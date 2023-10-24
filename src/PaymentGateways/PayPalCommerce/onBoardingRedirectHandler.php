<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Exception;
use Give\Framework\Exceptions\Primitives\Exception as GiveException;
use Give\Log\Log;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalAuth;
use Give\PaymentGateways\PayPalCommerce\Repositories\Settings;
use Give\PaymentGateways\PayPalCommerce\Repositories\Webhooks;
use Give_Admin_Settings;

/**
 * Class PayPalOnBoardingRedirectHandler
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class onBoardingRedirectHandler
{
    /**
     * @since 2.9.0
     *
     * @var PayPalAuth
     */
    private $payPalAuth;

    /**
     * @since 2.9.0
     *
     * @var Webhooks
     */
    private $webhooksRepository;

    /**
     * @since 2.9.0
     *
     * @var MerchantDetails
     */
    private $merchantRepository;

    /**
     * @since 2.9.0
     *
     * @var Settings
     */
    private $settings;

    /**
     * onBoardingRedirectHandler constructor.
     *
     * @since 2.9.0
     *
     * @param Webhooks $webhooks
     * @param MerchantDetails $merchantRepository
     * @param Settings $settings
     * @param PayPalAuth $payPalAuth
     */
    public function __construct(
        Webhooks $webhooks,
        MerchantDetails $merchantRepository,
        Settings $settings,
        PayPalAuth $payPalAuth
    ) {
        $this->webhooksRepository = $webhooks;
        $this->merchantRepository = $merchantRepository;
        $this->settings = $settings;
        $this->payPalAuth = $payPalAuth;

        $this->setModeFromRequest();
    }

    /**
     * This function set mode from request.
     *
     * @since 2.30.0
     * @return void
     */
    private function setModeFromRequest()
    {
        if (isset($_GET['mode']) && in_array($_GET['mode'], ['live', 'sandbox'], true)) {
            $mode = $_GET['mode'];

            $this->setModeForServices($mode);
        }
    }

    /**
     * Set mode for services.
     *
     * Use this function to manually set connection mode. Values can be 'live' or 'sandbox'.
     * Services are classes which depends upon connection mode and used to interact with PayPal API or core logic.
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function setModeForServices(string $mode)
    {
        $this->webhooksRepository->setMode($mode);
        $this->merchantRepository->setMode($mode);
        give(PayPalClient::class)->setMode($mode);
    }

    /**
     * Bootstrap class
     *
     * @since 2.9.0
     */
    public function boot()
    {
        if ($this->isPayPalUserRedirected()) {
            $details = $this->savePayPalMerchantDetails();
            $this->setUpWebhook($details);
            $this->redirectAccountConnected();
        }

        if ($this->isPayPalAccountDetailsSaved()) {
            $this->registerPayPalSSLNotice();
            $this->registerPayPalAccountConnectedNotice();
        }

        if ($this->isStatusRefresh()) {
            $this->refreshAccountStatus();
        }
    }

    /**
     * Save PayPal merchant details
     *
     * @since 2.32.0 Remove second argument from updateSellerAccessToken function.
     * @since 2.25.0 Handle exception.
     * @since 2.9.0
     *
     * @return MerchantDetail
     */
    private function savePayPalMerchantDetails()
    {
        $paypalGetData = wp_parse_args($_SERVER['QUERY_STRING']);
        $partnerLinkInfo = $this->settings->getPartnerLinkDetails();
        $tokenInfo = $this->settings->getAccessToken();

        $allowedPayPalData = [
            'merchantId',
            'merchantIdInPayPal',
        ];

        $payPalAccount = array_intersect_key($paypalGetData, array_flip($allowedPayPalData));

        if (! array_key_exists('merchantIdInPayPal', $payPalAccount) || empty($payPalAccount['merchantIdInPayPal'])) {
            $errors[] = [
                'type' => 'url',
                'message' => esc_html__(
                                 'The Merchant ID for PayPal was not found. Try connecting to PayPal again. The PayPal return URL is:',
                                 'give'
                             ) . "\n",
                'value' => urlencode($_SERVER['QUERY_STRING']),
            ];

            $this->merchantRepository->saveAccountErrors($errors);
            $this->redirectWhenOnBoardingFail();
        }

        $restApiCredentials = (array)$this->payPalAuth->getSellerRestAPICredentials(
            $tokenInfo ? $tokenInfo['accessToken'] : ''
        );
        $this->didWeGetValidSellerRestApiCredentials($restApiCredentials);

        try {
            $tokenInfo = $this->payPalAuth->getTokenFromClientCredentials(
                $restApiCredentials['client_id'],
                $restApiCredentials['client_secret']
            );
        } catch (GiveException $e) {
            give(Log::class)->warning(
                'PayPal Commerce: Error retrieving access token on boarding redirect.',
                [
                    'category' => 'Payment Gateway',
                    'source' => 'Paypal Commerce',
                    'exception' => $e,
                ]
            );

            $errors[] = esc_html__(
                'There was a problem with retrieving PayPal access token. Please try again or contact support.',
                'give'
            );

            $this->merchantRepository->saveAccountErrors($errors);
            $this->redirectWhenOnBoardingFail();
        }

        $payPalAccount['clientId'] = $restApiCredentials['client_id'];
        $payPalAccount['clientSecret'] = $restApiCredentials['client_secret'];
        $payPalAccount['token'] = $tokenInfo;
        $payPalAccount['supportsCustomPayments'] = 'PPCP' === $partnerLinkInfo['product'];
        $payPalAccount['accountIsReady'] = true;
        $payPalAccount['accountCountry'] = $this->settings->getAccountCountry();

        $merchantDetails = MerchantDetail::fromArray($payPalAccount);
        $this->merchantRepository->save($merchantDetails);

        // Preserve the seller access token.
        // This is required to get the merchant rest api credentials.
        $this->settings->updateSellerAccessToken($this->settings->getAccessToken());

        $this->deleteTempOptions();

        return $merchantDetails;
    }

    /**
     * Redirects the user to the account connected url
     *
     * @since 2.9.0
     */
    private function redirectAccountConnected()
    {
        $this->refreshAccountStatus();

        wp_redirect(
            add_query_arg(
                [
                    'post_type' => 'give_forms',
                    'page' => 'give-settings',
                    'tab' => 'gateways',
                    'section' => 'paypal',
                    'group' => 'paypal-commerce',
                    'paypal-commerce-account-connected' => '1'
                ],
                admin_url('edit.php')
            )
        );

        exit();
    }

    /**
     * Sets up the webhook for the connected account
     *
     * @since 2.32.0 Remove second argument from createWebhook function.
     * @since 2.9.0
     *
     * @param MerchantDetail $merchant_details
     */
    private function setUpWebhook(MerchantDetail $merchant_details)
    {
        if (! is_ssl()) {
            return;
        }

        try {
            $webhookConfig = $this->webhooksRepository->createWebhook();
            $this->webhooksRepository->saveWebhookConfig($webhookConfig);
        } catch (Exception $ex) {
            $errors[] = esc_html__(
                'There was a problem with creating webhook on PayPal. A gateway error log also added to get details information about PayPal response.',
                'give'
            );

            $this->merchantRepository->saveAccountErrors($errors);
            $this->redirectWhenOnBoardingFail();
        }
    }

    /**
     * Delete temp data
     *
     * @since 2.9.0
     * @return void
     */
    private function deleteTempOptions()
    {
        $this->settings->deletePartnerLinkDetails();
        $this->settings->deleteAccessToken();
    }

    /**
     * Register notice if account connect success fully.
     *
     * @since 2.9.0
     */
    private function registerPayPalAccountConnectedNotice()
    {
        Give_Admin_Settings::add_message(
            'paypal-commerce-account-connected',
            esc_html__('PayPal account connected successfully.', 'give')
        );
    }

    /**
     * Returns whether or not the current request is for refreshing the account status
     *
     * @since 2.9.0
     *
     * @return bool
     */
    private function isStatusRefresh()
    {
        return isset($_GET['paypalStatusCheck']) && Give_Admin_Settings::is_setting_page('gateways', 'paypal');
    }

    /**
     * Return whether or not PayPal user redirect to GiveWP setting page after successful onboarding.
     *
     * @since 2.9.0
     *
     * @return bool
     */
    private function isPayPalUserRedirected()
    {
        return isset($_GET['merchantIdInPayPal']) && Give_Admin_Settings::is_setting_page('gateways', 'paypal');
    }

    /**
     * Return whether or not PayPal account details saved.
     *
     * @since 2.9.0
     *
     * @return bool
     */
    private function isPayPalAccountDetailsSaved()
    {
        return isset($_GET['paypal-commerce-account-connected']) && Give_Admin_Settings::is_setting_page(
                'gateways',
                'paypal'
            );
    }

    /**
     * validate rest api credential.
     *
     * @since 2.9.0
     *
     * @param array $array
     *
     */
    private function didWeGetValidSellerRestApiCredentials($array)
    {
        $required = ['client_id', 'client_secret'];
        $array = array_filter($array); // Remove empty values.

        if (array_diff($required, array_keys($array))) {
            $errors[] = [
                'type' => 'json',
                'message' => esc_html__('PayPal client access token API request response is:', 'give'),
                'value' => wp_json_encode($this->settings->getAccessToken()),
            ];

            $errors[] = [
                'type' => 'json',
                'message' => esc_html__('PayPal client rest api credentials API request response is:', 'give'),
                'value' => wp_json_encode($array),
            ];

            $errors[] = esc_html__(
                'There was a problem with PayPal client rest API request and we could not find valid client id and secret.',
                'give'
            );

            $this->merchantRepository->saveAccountErrors($errors);
            $this->redirectWhenOnBoardingFail();
        }
    }

    /**
     * Handles the request for refreshing the account status
     *
     * @since 3.0.0 Make function publicly accessible.
     * @since 2.9.0
     */
    public function refreshAccountStatus()
    {
        $merchantDetails = $this->merchantRepository->getDetails();

        $statusErrors = $this->isAdminSuccessfullyOnBoarded(
            $merchantDetails->merchantIdInPayPal,
            $merchantDetails->accessToken,
            $merchantDetails->supportsCustomPayments
        );
        if ($statusErrors !== true) {
            $merchantDetails->accountIsReady = false;
            $this->merchantRepository->saveAccountErrors($statusErrors);
        } else {
            $merchantDetails->accountIsReady = true;
            $this->merchantRepository->deleteAccountErrors();
        }

        $this->merchantRepository->save($merchantDetails);
    }

    /**
     * Validate seller on Boarding status
     *
     * @since 2.29.0 Validate only primary capabilities during PayPal donations on-boarding.
     * @since 2.9.0
     *
     * @param string $merchantId
     * @param string $accessToken
     * @param bool $usesCustomPayments
     *
     * @return true|string[]
     */
    private function isAdminSuccessfullyOnBoarded($merchantId, $accessToken, $usesCustomPayments)
    {
        $onBoardedData = (array)$this->payPalAuth->getSellerOnBoardingDetailsFromPayPal($merchantId, $accessToken);
        $onBoardedData = array_filter($onBoardedData); // Remove empty values.
        $errorMessages[] = [
            'type' => 'json',
            'message' => esc_html__('PayPal merchant status check API request response is:', 'give'),
            'value' => wp_json_encode($onBoardedData),
        ];

        if (! is_ssl()) {
            $errorMessages[] = esc_html__(
                'A valid SSL certificate is required to accept donations and set up your PayPal account. Once a
                certificate is installed and the site is using https, please disconnect and reconnect your account.',
                'give'
            );
        }

        if (array_diff(['payments_receivable', 'primary_email_confirmed'], array_keys($onBoardedData))) {
            $errorMessages[] = __(
                'Your account is not fully set up and ready to receive payments. Please log into your PayPal account at <a href="https://paypal.com">paypal.com</a> and address the following issues. Reach out to PayPal support if you need help setting up your account.',
                'give'
            );

            // Return here since the rest of the validations will definitely fail
            return $errorMessages;
        }

        if (! $onBoardedData['payments_receivable']) {
            $errorMessages[] = esc_html__('An banking account needs to be connected to your PayPal account and verified', 'give');
        }

        if (! $onBoardedData['primary_email_confirmed']) {
            $errorMessages[] = esc_html__('Your primary email address needs to be confirmed', 'give');
        }

        // This error message is only for the case when the user is using custom payments.
        // Host card fields are supported only for specific countries and PayPal seller account of PPCP type.
        if ($usesCustomPayments) {
            $sellerCapabilities = array_key_exists('capabilities', $onBoardedData)
                ? wp_list_pluck($onBoardedData['capabilities'], 'name')
                : [];
            $requiredCapability = 'CUSTOM_CARD_PROCESSING';
            $customCardProcessingCapabilityIndex = array_search($requiredCapability, $sellerCapabilities, true);
            $hasCustomCardProcessingCapability = false !== $customCardProcessingCapabilityIndex;

            // If the capability is found then check if it is active.
            if (false !== $customCardProcessingCapabilityIndex) {
                $customCardProcessingCapability = $onBoardedData['capabilities'][$customCardProcessingCapabilityIndex];
                $hasCustomCardProcessingCapability = 'ACTIVE' === $customCardProcessingCapability['status'];
            }

            if (! $hasCustomCardProcessingCapability) {
                $errorMessages[] = __(
                    'Advance card processing is not active on your PayPal account. That capability is required in order to display card fields directly on your website. To accept donations with card fields directly on your site, called <a href="https://developer.paypal.com/docs/checkout/advanced/#enable-your-account" title="Link to PayPal Docs">hosted fields</a>, you\'ll need to enable custom card processing. This is something PayPal support can help with, and depends on factors outside of GiveWP\'s control. You can still accept donations with <a href="https://developer.paypal.com/docs/checkout/" title="Link to PayPal Docs">PayPal smart buttons</a>, which allow donors to log into PayPal and complete the donation in a modal window, in the meantime.',
                    'give'
                );
            }
        }

        // If there were errors then redirect the user with notices
        return count($errorMessages) > 1 ? $errorMessages : true;
    }

    /**
     * Redirect admin to setting section with error.
     *
     * @since 2.9.0
     */
    private function redirectWhenOnBoardingFail()
    {
        wp_redirect(
            add_query_arg(
                [
                    'post_type' => 'give_forms',
                    'page' => 'give-settings',
                    'tab' => 'gateways',
                    'section' => 'paypal',
                    'group' => 'paypal-commerce',
                    'paypal-error' => '1',
                ],
                admin_url('edit.php')
            )
        );

        exit();
    }

    /**
     * Displays a notice of the site is not using SSL
     *
     * @since 2.9.0
     */
    private function registerPayPalSSLNotice()
    {
        if (is_ssl() && empty($this->webhooksRepository->getWebhookConfig())) {
            $logLink = sprintf(
                '<a href="%1$s">%2$s</a>',
                admin_url('/edit.php?post_type=give_forms&page=give-tools&tab=logs'),
                esc_html__('logs data', 'give')
            );

            Give_Admin_Settings::add_error(
                'paypal-webhook-error',
                sprintf(
                    esc_html__(
                        'There was a problem setting up the webhooks for your PayPal account. Please try disconnecting and reconnecting your PayPal account. If the problem persists, please contact support and provide them with the latest %1$s',
                        'give'
                    ),
                    $logLink
                )
            );
        }
    }
}
