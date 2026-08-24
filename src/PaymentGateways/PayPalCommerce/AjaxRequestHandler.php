<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\DonationForms\Actions\ValidateDonationFormRequest;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\PayPalCheckoutSdk\ProcessorResponseError;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give\Helpers\Form\Utils as FormUtils;

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
     * @var RefreshToken
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

        try {
            $payPalResponse = $this->payPalAuth->getTokenFromAuthorizationCode(
                give_clean($_GET['authCode']),
                give_clean($_GET['sharedId']),
                $partnerLinkInfo['nonce']
            );
        } catch (\Exception $exception) {
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
     * @since 3.0.0 Add support for accountType. This param is required to get partner link.
     * @since 2.30.0 Add support for mode param.
     * @since 2.9.0
     */
    public function onGetPartnerUrlAjaxRequestHandler()
    {
        $this->validateAdminRequest();

        if (empty($accountType = $_GET['accountType']) || ! in_array($accountType, ScriptLoader::$accountTypes, true)) {
            wp_send_json_error('Must include valid account type');
        }

        if (empty($country = $_GET['countryCode']) || ! isset(give_get_country_list()[$country])) {
            wp_send_json_error('Must include valid 2-character country code');
        }

        if (empty($_GET['mode']) || ! in_array($_GET['mode'], ['sandbox', 'live'])) {
            wp_send_json_error('Must include valid mode');
        }

        $country = sanitize_text_field(wp_unslash($_GET['countryCode']));
        $accountType = sanitize_text_field(wp_unslash($_GET['accountType']));
        $mode = sanitize_text_field(wp_unslash($_GET['mode']));

        // Generate a unique state token for CSRF protection on PayPal callback.
        $stateToken = wp_generate_password(32, false);
        set_transient('give_paypal_onboarding_state_' . $mode, $stateToken, HOUR_IN_SECONDS);

        $redirectUrl = add_query_arg(
            [
                'tab' => 'gateways',
                'section' => 'paypal',
                'group' => 'paypal-commerce',
                'mode' => $mode,
                'give_paypal_state' => $stateToken,
            ],
            admin_url('edit.php?post_type=give_forms&page=give-settings')
        );

        // Set PayPal client mode.
        give(PayPalClient::class)->setMode($mode);

        $data = $this->payPalAuth->getSellerPartnerLink($redirectUrl, $accountType);

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
     * @since 3.16.0 added security nonce check
     * @since 3.13.0 Add new $keepWebhooks option
     * @since 2.30.0 Add support for mode param.
     * @since 2.25.0 Remove merchant seller token.
     * @since 2.9.0
     */
    public function removePayPalAccount()
    {
        check_ajax_referer( 'give_paypal_commerce_disconnect_account');

        if (! current_user_can('manage_give_settings')) {
            wp_send_json_error(['error' => esc_html__('You are not allowed to perform this action.', 'give')]);
        }

        try {
            $mode = give_clean($_POST['mode']);
            $keepWebhooks = rest_sanitize_boolean($_POST['keep-webhooks']);
            $this->webhooksRepository->setMode($mode);
            $this->merchantRepository->setMode($mode);
            $this->refreshToken->setMode($mode);
            $this->settings->setMode($mode);

            $this->validateAdminRequest();

            // Remove the webhook from PayPal if there is one
            if ( ! $keepWebhooks && $webhookConfig = $this->webhooksRepository->getWebhookConfig()) {
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
     * @since 3.1.0 Remove unused variable from createOrder argument.
     * @since 2.9.0
     */
    public function createOrder()
    {
        $this->validateFrontendRequest();
        $data = $this->getOrderData();

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
     * @since 4.16.7.1 Validate the request through the form layer before building order data. v3 forms must
     *            also send a total at least as large as the amount the form validated; v2 forms are
     *            checked on the final, post-filter amount.
     * @since 4.14.4 Validate donation amount before creating or updating an order.
     * @since 4.2.1 Only filter amount for v2 forms.
     * @since 3.4.2
     */
    private function getOrderData(): array
    {
        $postData = give_clean($_POST);
        $formId = absint($postData['give-form-id']);
        $donorAddress = $this->getDonorAddressFromPostedDataForPaypalOrder($postData);
        $isV3Form = FormUtils::isV3Form($formId);

        if (!$isV3Form) {
            $this->skipLegacyCardFieldRequirements();
        }

        $this->validateDonationFormRequest($formId, $postData);

        if ($isV3Form) {
            /*
             * v3 forms send the form's own amount field as "amount" and the total, with fee recovery
             * already included, as "give-amount". The total is what the donor approves in the PayPal
             * popup; PayPalCommerce::createPayment() reconciles the order to the validated donation
             * before capturing, so all this has to guarantee is that the total never drops below the
             * amount the form just validated.
             */
            $validatedAmount = isset($postData['amount']) ? (float)$postData['amount'] : 0.0;
            $amount = isset($postData['give-amount']) ? give_clean($postData['give-amount']) : '0.00';

            if ($validatedAmount <= 0 || (float)$amount < $validatedAmount) {
                wp_send_json_error(['error' => __('Invalid donation amount.', 'give')]);
            }
        } else {
            $amount = isset($postData['give-amount']) ?
                (float)apply_filters(
                    'give_donation_total',
                    give_maybe_sanitize_amount(
                        $postData['give-amount'],
                        ['currency' => give_get_currency($formId)]
                    )
                ) :
                '0.00';

            $this->validateDonationAmount($amount, $formId);
        }

        return [
            'formId' => $formId,
            'formTitle' => give_payment_gateway_item_title(['post_data' => $postData], 127),
            'donationAmount' => $amount,
            'payer' => [
                'firstName' => $postData['give_first'],
                'lastName' => $postData['give_last'],
                'email' => $postData['give_email'],
                'address' => $donorAddress,
            ],
        ];
    }

    /**
     * Approve order.
     *
     * @todo: handle payment capture error on frontend.
     *
     * @since 4.16.7.1 Refuse v3 forms; their capture happens in PayPalCommerce::createPayment(). Validate
     *            the posted form before every capture, not only when the amount changed.
     * @since 4.14.4 Validate donation amount before approving an order.
     * @since 3.2.0 Discover error by checking capture status.
     * @since 2.9.0
     */
    public function approveOrder()
    {
        $this->validateFrontendRequest();
        $this->rejectV3FormRequest();

        $orderId = give_clean($_GET['order']);
        $updateAmount = filter_var(give_clean($_GET['update_amount']), FILTER_VALIDATE_BOOLEAN);

        try {
            $orderData = $this->getOrderData();

            if ($updateAmount) {
                $this->validateOrderAmountNotDecreased($orderId, $orderData['donationAmount']);
                give(PayPalOrder::class)->updateOrderAmount($orderId, $orderData);
            }

            $result = give(PayPalOrder::class)->approveOrder($orderId);
            // PayPal does not return error in case of invalid cvv. So we need to check capture status and return error.
            // ref - https://feedback.givewp.com/bug-reports/p/paypal-credit-card-donations-can-generate-a-fatal-error
            $this->returnErrorOnFailedApproveOrderResponse($result);
            wp_send_json_success(['order' => $result,]);
        } catch (\Exception $ex) {
            wp_send_json_error(['error' => json_decode($ex->getMessage(), true),]);
        }
    }

    /**
     * @since 4.16.7.1 Refuse v3 forms; PayPalCommerce::createPayment() reconciles their order amount.
     * @since 4.14.4 Validate donation amount before updating an order amount.
     * @since 3.4.2
     */
    public function updateOrderAmount()
    {
        $this->validateFrontendRequest();
        $this->rejectV3FormRequest();

        $orderId = give_clean($_GET['order']);

        try {
            $orderData = $this->getOrderData();
            $this->validateOrderAmountNotDecreased($orderId, $orderData['donationAmount']);
            give(PayPalOrder::class)->updateOrderAmount($orderId, $orderData);

            wp_send_json_success(['order' => $orderId,]);
        } catch (\Exception $ex) {
            wp_send_json_error(['error' => json_decode($ex->getMessage(), true),]);
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
     * Hold the request to the form's own rules before anything reaches PayPal. The form layer owns
     * the rules: amount limits, required fields, and whatever else it validates for this form
     * version; this handler only acts on the verdict.
     *
     * @since 4.16.7.1
     */
    private function validateDonationFormRequest(int $formId, array $request): void
    {
        try {
            give(ValidateDonationFormRequest::class)($formId, $request);
        } catch (DonationFormFieldErrorsException $exception) {
            wp_send_json_error(['error' => implode(' ', $exception->getError()->get_error_messages())]);
        } catch (DonationFormForbidden $exception) {
            wp_send_json_error(['error' => $exception->getMessage()], 403);
        } catch (\Exception $exception) {
            /*
             * Anything else the form layer throws (a spam detection, for one) still means "do not
             * create this order". Same handling as the validate route, log entry included.
             */
            Log::error('PayPal Commerce order request rejected', [
                'formId' => $formId,
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
            ]);

            wp_send_json_error(['error' => $exception->getMessage()]);
        }
    }

    /**
     * The v2 form posts its card inputs along with everything else, but for this gateway those inputs
     * are PayPal-hosted fields (SmartButtons.js strips them before its own validation call for the
     * same reason), so the legacy validator must not require them here.
     *
     * @since 4.16.7.1
     */
    private function skipLegacyCardFieldRequirements(): void
    {
        add_filter('give_donation_form_required_fields', static function ($requiredFields) {
            return array_diff_key(
                (array)$requiredFields,
                array_flip(['card_name', 'card_number', 'card_cvc', 'card_expiry'])
            );
        });
    }

    /**
     * The legacy validator checks the raw posted amount. The amount that actually reaches PayPal for
     * a v2 form has been through the give_donation_total filter (fee recovery), so it is checked
     * again here: positive and within the form's maximum. v3 amounts are validated by the form layer.
     *
     * @since 4.16.7.1 Applies to v2 forms only.
     * @since 4.14.4
     *
     * @param float|string $amount
     */
    private function validateDonationAmount($amount, int $formId): void
    {
        $amount = (float)$amount;

        if ($amount <= 0) {
            wp_send_json_error(['error' => __('Invalid donation amount.', 'give')]);
        }

        $maxAmount = (float)give_get_form_maximum_price($formId);
        if ($maxAmount > 0 && $amount > $maxAmount) {
            wp_send_json_error([
                'error' => sprintf(
                    /* translators: %s: maximum donation amount */
                    __('Donation amount must not exceed %s.', 'give'),
                    give_currency_filter(give_format_amount($maxAmount, ['sanitize' => false]))
                ),
            ]);
        }
    }

    /**
     * Visual Form Builder (v3) forms never call the approve and update-amount endpoints: their order
     * is reconciled and captured in PayPalCommerce::createPayment(), after the donation exists.
     * Refusing them here keeps these endpoints from capturing outside donation processing.
     *
     * @since 4.16.7.1
     */
    private function rejectV3FormRequest(): void
    {
        if (FormUtils::isV3Form(absint($_POST['give-form-id']))) {
            wp_send_json_error(
                ['error' => __('This request is not supported for this donation form.', 'give')],
                403
            );
        }
    }

    /**
     * Validate that the new donation amount is not less than the original PayPal order amount.
     *
     * @since 4.14.4
     *
     * @param string $orderId
     * @param float|string $newAmount
     */
    private function validateOrderAmountNotDecreased(string $orderId, $newAmount): void
    {
        $newAmount = (float)$newAmount;

        $currentOrder = give(PayPalOrder::class)->getApprovedOrder($orderId);
        $currentAmount = (float)$currentOrder->purchase_units[0]->amount->value;

        if ($newAmount < $currentAmount) {
            wp_send_json_error([
                'error' => __('Donation amount cannot be decreased.', 'give'),
            ]);
        }
    }

    /**
     * This function should return address array in PayPal rest api accepted format.
     *
     * @since 3.1.0 Return address only if setting enabled and has valida country in PayPal accepted formatted.
     * @since 2.11.1
     */
    private function getDonorAddressFromPostedDataForPaypalOrder(array $postedData): array
    {
        if (empty($postedData['billing_country'])) {
            return [];
        }

        $address['address_line_1'] = ! empty($postedData['card_address']) ? $postedData['card_address'] : '';
        $address['address_line_2'] = ! empty($postedData['card_address_2']) ? $postedData['card_address_2'] : '';
        $address['admin_area_2'] = ! empty($postedData['card_city']) ? $postedData['card_city'] : '';
        $address['admin_area_1'] = ! empty($postedData['card_state']) ? $postedData['card_state'] : '';
        $address['postal_code'] = ! empty($postedData['card_zip']) ? $postedData['card_zip'] : '';
        $address['country_code'] = ! empty($postedData['billing_country']) ? $postedData['billing_country'] : '';

        return $address;
    }

    /**
     * This function should validate PayPal ApproveOrder response and respond to ajax request on error.
     *
     * @since 3.2.0
     */
    private function returnErrorOnFailedApproveOrderResponse(\stdClass $response)
    {
        // Get capture.
        // ref - https://developer.paypal.com/docs/api/orders/v2/#orders_capture
        $capture = $response->purchase_units[0]->payments->captures[0];

        // Check if capture status is failed or declined.
        if (
            in_array($capture->status, ['FAILED', 'DECLINED'])
            && property_exists($capture, 'processor_response')
        ) {
            $error = ProcessorResponseError::getError($capture->processor_response);

            if ($error) {
                wp_send_json_error(['error' => $error]);
            }
        }
    }
}
