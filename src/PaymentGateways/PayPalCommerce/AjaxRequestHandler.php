<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\Framework\Http\ConnectServer\Client\ConnectClient;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;

/**
 * Class AjaxRequestHandler
 * @package Give\PaymentGateways\PaypalCommerce
 *
 * @sicne 2.9.0
 */
class AjaxRequestHandler
{
    /**
     * @since 2.9.0
     *
     * @var Webhooks
     */
    private $webhooksRepository;

    /**
     * @since 2.9.0
     *
     * @var MerchantDetail
     */
    private $merchantDetails;

    /**
     * @since 2.9.0
     *
     * @var PayPalAuth
     */
    private $payPalAuth;

    /**
     * @since 2.9.0
     *
     * @var MerchantDetails
     */
    private $merchantRepository;

    /**
     * @since 2.9.0
     *
     * @var ConnectClient
     */
    private $refreshToken;

    /**
     * @since 2.9.0
     *
     * @var Settings
     */
    private $settings;

    /**
     * AjaxRequestHandler constructor.
     *
     * @since 2.9.0
     *
     * @param Webhooks $webhooksRepository
     * @param MerchantDetail $merchantDetails
     * @param MerchantDetails $merchantRepository
     * @param RefreshToken $refreshToken
     * @param Settings $settings
     * @param PayPalAuth $payPalAuth
     */
    public function __construct(
        Webhooks $webhooksRepository,
        MerchantDetail $merchantDetails,
        MerchantDetails $merchantRepository,
        RefreshToken $refreshToken,
        Settings $settings,
        PayPalAuth $payPalAuth
    ) {
        $this->webhooksRepository = $webhooksRepository;
        $this->merchantDetails = $merchantDetails;
        $this->merchantRepository = $merchantRepository;
        $this->refreshToken = $refreshToken;
        $this->settings = $settings;
        $this->payPalAuth = $payPalAuth;
    }

    /**
     * give_paypal_commerce_user_onboarded ajax action handler
     *
     * @since 2.32.0 Return error response on exception when fetch access token from authorization code.
     * @since 2.9.0
     */
    public function onBoardedUserAjaxRequestHandler()
    {
        $this->validateAdminRequest();

        if (empty($_GET['mode']) || ! in_array($_GET['mode'], ['sandbox', 'live'])) {
            wp_send_json_error('Must include valid mode');
        }

        $mode = sanitize_text_field(wp_unslash($_GET['mode']));

        // Set PayPal client mode.
        give(PayPalClient::class)->setMode($mode);

        $partnerLinkInfo = $this->settings->getPartnerLinkDetails();

        try{
            $payPalResponse = $this->payPalAuth->getTokenFromAuthorizationCode(
                give_clean($_GET['authCode']),
                give_clean($_GET['sharedId']),
                $partnerLinkInfo['nonce']
            );
        } catch ( \Exception $exception ) {
            wp_send_json_error();
        }

        $this->settings->updateAccessToken($payPalResponse);

        // Set cron job to refresh token.
        $refreshToken = give(RefreshToken::class);
        $refreshToken->setMode($mode);
        $refreshToken->registerCronJobToRefreshToken($payPalResponse['expiresIn']);

        wp_send_json_success();
    }

    /**
     * This function handle ajax request with give_paypal_commerce_get_partner_url action.
     *
     * @since 2.30.0 Add support for mode param.
     * @since 2.9.0
     */
    public function onGetPartnerUrlAjaxRequestHandler()
    {
        $this->validateAdminRequest();

        if (empty($country = $_GET['countryCode']) || ! isset(give_get_country_list()[$country])) {
            wp_send_json_error('Must include valid 2-character country code');
        }

        if (empty($_GET['mode']) || ! in_array($_GET['mode'], ['sandbox', 'live'])) {
            wp_send_json_error('Must include valid mode');
        }

        $country = sanitize_text_field(wp_unslash($_GET['countryCode']));
        $mode = sanitize_text_field(wp_unslash($_GET['mode']));
        $redirectUrl = add_query_arg(
            [
                'tab' => 'gateways',
                'section' => 'paypal',
                'group' => 'paypal-commerce',
                'mode' => $mode
            ],
            admin_url('edit.php?post_type=give_forms&page=give-settings')
        );

        // Set PayPal client mode.
        give(PayPalClient::class)->setMode($mode);

        $data = $this->payPalAuth->getSellerPartnerLink($redirectUrl, $country);

        if (! $data) {
            wp_send_json_error();
        }

        $this->settings->updateAccountCountry($country);
        $this->settings->updatePartnerLinkDetails($data);

        wp_send_json_success($data);
    }

    /**
     * give_paypal_commerce_disconnect_account ajax request handler.
     *
     * @since 2.30.0 Add support for mode param.
     * @since 2.25.0 Remove merchant seller token.
     * @since 2.9.0
     */
    public function removePayPalAccount()
    {
        if (! current_user_can('manage_give_settings')) {
            wp_send_json_error(['error' => esc_html__('You are not allowed to perform this action.', 'give')]);
        }

        try {
            $mode = give_clean($_POST['mode']);
            $this->webhooksRepository->setMode($mode);
            $this->merchantRepository->setMode($mode);
            $this->refreshToken->setMode($mode);
            $this->settings->setMode($mode);

            $this->validateAdminRequest();

            // Remove the webhook from PayPal if there is one
            if ($webhookConfig = $this->webhooksRepository->getWebhookConfig()) {
                $this->webhooksRepository->deleteWebhook($this->merchantDetails->accessToken, $webhookConfig->id);
                $this->webhooksRepository->deleteWebhookConfig();
            }

            $this->merchantRepository->delete();
            $this->merchantRepository->deleteAccountErrors();
            $this->merchantRepository->deleteClientToken();
            $this->settings->deleteSellerAccessToken();
            $this->refreshToken->deleteRefreshTokenCronJob();

            wp_send_json_success();
        } catch (\Exception $exception) {
            wp_send_json_error(['error' => $exception->getMessage()]);
        }
    }

    /**
     * Create order.
     *
     * @todo: handle payment create error on frontend.
     *
     * @since 2.9.0
     */
    public function createOrder()
    {
        $this->validateFrontendRequest();

        $postData = give_clean($_POST);
        $formId = absint($postData['give-form-id']);

        $data = [
            'formId' => $formId,
            'formTitle' => give_payment_gateway_item_title(['post_data' => $postData], 127),
            'donationAmount' => isset($postData['give-amount']) ?
                (float)apply_filters(
                    'give_donation_total',
                    give_maybe_sanitize_amount(
                        $postData['give-amount'],
                        ['currency' => give_get_currency($formId)]
                    )
                ) :
                '0.00',
            'payer' => [
                'firstName' => $postData['give_first'],
                'lastName' => $postData['give_last'],
                'email' => $postData['give_email'],
                'address' => $this->getDonorAddressFromPostedDataForPaypalOrder($postData),
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
            ],
        ];

        try {
            $result = give(PayPalOrder::class)->createOrder($data);

            wp_send_json_success(
                [
                    'id' => $result,
                ]
            );
        } catch (\Exception $ex) {
            wp_send_json_error(
                [
                    'error' => json_decode($ex->getMessage(), true),
                ]
            );
        }
    }

    /**
     * Approve order.
     *
     * @todo: handle payment capture error on frontend.
     *
     * @since 2.9.0
     */
    public function approveOrder()
    {
        $this->validateFrontendRequest();

        $orderId = give_clean($_GET['order']);

        try {
            $result = give(PayPalOrder::class)->approveOrder($orderId);
            wp_send_json_success(
                [
                    'order' => $result,
                ]
            );
        } catch (\Exception $ex) {
            wp_send_json_error(
                [
                    'error' => json_decode($ex->getMessage(), true),
                ]
            );
        }
    }

    /**
     * Return on boarding trouble notice.
     *
     * @since 2.9.6
     */
    public function onBoardingTroubleNotice()
    {
        if (! current_user_can('manage_give_settings')) {
            wp_die();
        }

        /* @var AdminSettingFields $adminSettingFields */
        $adminSettingFields = give(AdminSettingFields::class);

        $actionList = sprintf(
            '<ol><li>%1$s</li><li>%2$s</li><li>%3$s</li></ol>',
            esc_html__(
                'Make sure to complete the entire PayPal process. Do not close the window until you have finished the process.',
                'give'
            ),
            esc_html__(
                'The last screen of the PayPal connect process includes a button to be sent back to your site. It is important you click this and do not close the window yourself.',
                'give'
            ),
            esc_html__(
                'If you’re still having problems connecting: ',
                'give'
            ) . $adminSettingFields->getAdminGuidanceNotice(false)
        );

        $standardError = sprintf(
            '<div id="give-paypal-onboarding-trouble-notice" class="give-hidden"><p class="error-message">%1$s</p><p>%2$s</p></div>',
            esc_html__('Having trouble connecting to PayPal?', 'give'),
            $actionList
        );

        wp_send_json_success($standardError);
    }

    /**
     * Validate admin ajax request.
     *
     * @since 2.9.0
     */
    private function validateAdminRequest()
    {
        if (! current_user_can('manage_give_settings')) {
            wp_die();
        }
    }

    /**
     * Validate frontend ajax request.
     *
     * @since 2.9.0
     */
    private function validateFrontendRequest()
    {
        $formId = absint($_POST['give-form-id']);

        if (! $formId || ! give_verify_donation_form_nonce(give_clean($_POST['give-form-hash']), $formId)) {
            wp_die();
        }
    }

    /**
     * @since 2.11.1
     *
     * @param array $postedData
     *
     * @return array
     */
    private function getDonorAddressFromPostedDataForPaypalOrder($postedData)
    {
        if (! $this->settings->canCollectBillingInformation()) {
            return [];
        }

        $address['address_line1'] = ! empty($postedData['card_address']) ? $postedData['card_address'] : '';
        $address['address_line_2'] = ! empty($postedData['card_address_2']) ? $postedData['card_address_2'] : '';
        $address['admin_line_1'] = ! empty($postedData['card_city']) ? $postedData['card_city'] : '';
        $address['admin_line_2'] = ! empty($postedData['card_state']) ? $postedData['card_state'] : '';
        $address['postal_code'] = ! empty($postedData['card_zip']) ? $postedData['card_zip'] : '';
        $address['country_code'] = ! empty($postedData['billing_country']) ? $postedData['billing_country'] : '';

        return $address;
    }
}
