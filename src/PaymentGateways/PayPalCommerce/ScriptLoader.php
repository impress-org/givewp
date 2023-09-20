<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\Gateways\PayPalCommerce\PayPalCommerceGateway;
use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\Helpers\Form\Template\Utils\Frontend as FrontendFormTemplateUtils;
use Give_Admin_Settings;

/**
 * Class ScriptLoader
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 3.0.0 Implement on-demand PayPal connection. Admin can select Advance or standard card processing.
 * @since 2.9.0
 */
class ScriptLoader
{
    /**
     * List of connection account type.
     *
     * @since 3.0.0
     * @var string[]
     */
    public static $accountTypes = [
        'PPCP',
        'EXPRESS_CHECKOUT'
    ];

    /**
     * List of countries that support custom payment accounts
     *
     * @since 3.0.0
     * @var string[]
     */
    private $countriesAvailableForAdvanceConnection = [
        'AU',
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DK',
        'EE',
        'FI',
        'FR',
        'GR',
        'HU',
        'IT',
        'LV',
        'LI',
        'LT',
        'LU',
        'MT',
        'NL',
        'NO',
        'PL',
        'PT',
        'RO',
        'SK',
        'SI',
        'ES',
        'SE',
        'GB',
        'US',
    ];

    /**
     * @since 2.9.0
     *
     * @var PayPalCommerceGateway
     */
    private $payPalCommerceGateway;

    /**
     * ScriptLoader constructor.
     *
     * @since 3.0.0 Remove $merchantRepository parameter and add $payPalCommerceGateway parameter.
     * @since 2.9.0
     *
     * @param PayPalCommerceGateway $payPalCommerceGateway
     */
    public function __construct(PayPalCommerceGateway $payPalCommerceGateway)
    {
        $this->payPalCommerceGateway = $payPalCommerceGateway;
    }

    /**
     * Load admin scripts
     *
     * @since 2.9.0
     */
    public function loadAdminScripts()
    {
        if (!Give_Admin_Settings::is_setting_page('gateway', 'paypal')) {
            return;
        }

        wp_enqueue_script(
            'give-paypal-partner-js',
            $this->getPartnerJsUrl(),
            [],
            null,
            true
        );

        wp_enqueue_style(
            'give-admin-paypal-commerce-css',
            GIVE_PLUGIN_URL . 'assets/dist/css/admin-paypal-commerce.css',
            [],
            GIVE_VERSION
        );

        wp_localize_script(
            'give-paypal-partner-js',
            'givePayPalCommerce',
            [
                'countriesAvailableForAdvanceConnection' => $this->countriesAvailableForAdvanceConnection,
                'accountTypes' => self::$accountTypes,
                'translations' => [
                    'confirmPaypalAccountDisconnection' => esc_html__('Disconnect PayPal Account', 'give'),
                    'disconnectPayPalAccount' => esc_html__(
                        'Are you sure you want to disconnect your PayPal account?',
                        'give'
                    ),
                    'connectSuccessTitle' => esc_html__('You’re connected to PayPal! Here’s what’s next...', 'give'),
                    'pciWarning' => sprintf(
                        __(
                            'PayPal allows you to accept credit or debit cards directly on your website. Because of
                            this, your site needs to maintain <a href="%1$s" target="_blank">PCI-DDS compliance</a>.
                            GiveWP never stores sensitive information like card details to your server and works
                            seamlessly with SSL certificates. Compliance is comprised of, but not limited to:',
                            'give'
                        ),
                        'https://givewp.com/documentation/resources/pci-compliance/'
                    ),
                    'pciComplianceInstructions' => [
                        esc_html__(
                            'Using a trusted, secure hosting provider – preferably one which claims and actively promotes PCI compliance.',
                            'give'
                        ),
                        esc_html__(
                            'Maintain security best practices when setting passwords and limit access to your server.',
                            'give'
                        ),
                        esc_html__('Implement an SSL certificate to keep your donations secure.', 'give'),
                        esc_html__('Keep plugins up to date to ensure latest security fixes are present.', 'give'),
                    ],
                ],
            ]
        );

        $script = <<<EOT
                function giveSandboxPayPalOnBoardedCallback(authCode, sharedId){
                    givePayPalOnBoardedCallback('sandbox', authCode, sharedId);
                }

                function giveLivePayPalOnBoardedCallback(authCode, sharedId){
                    givePayPalOnBoardedCallback('live', authCode, sharedId);
                }

                function givePayPalOnBoardedCallback(mode, authCode, sharedId) {
                    const query = '&mode=' + mode + '&authCode=' + authCode + '&sharedId=' + sharedId;

                    fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded' + query )
                        .then(function(res){ return res.json() })
                        .then(function(res) {
                            if ( true !== res.success ) {
                                console.log(res);
                                alert('Something went wrong!');
                                return;
                            }

                            // Remove PayPal quick help container.
                            const paypalErrorQuickHelp = document.getElementById('give-paypal-onboarding-trouble-notice');
                            paypalErrorQuickHelp && paypalErrorQuickHelp.remove();
                        });
                }
EOT;

        wp_add_inline_script(
            'give-paypal-partner-js',
            $script
        );
    }

    /**
     * Load public assets.
     *
     * @since 2.32.0 Handle exception if client token is not generated.
     * @since 2.9.0
     */
    public function loadPublicAssets()
    {
        $formId = FrontendFormTemplateUtils::getFormId();

        if (
            ! $formId
            || \Give\Helpers\Form\Utils::isV3Form($formId)
            || !Utils::gatewayIsActive()
            || !Utils::isAccountReadyToAcceptPayment()
        ) {
            return;
        }

        try {
            $formSettings = $this->payPalCommerceGateway->formSettings($formId);
            $paypalSDKOptions = $formSettings['sdkOptions'];

            // Remove v3 donation form related param.
            unset($paypalSDKOptions['data-namespace']);
        } catch (\Exception $e) {
            give_set_error(
                'give-paypal-commerce-client-token-error',
                sprintf(
                    esc_html__(
                        'Unable to load PayPal Commerce client token. Please try again later. Error: %1$s',
                        'give'
                    ),
                    $e->getMessage()
                )
            );
            return;
        }

        /* @var MerchantDetail $merchant */
        $merchant = give(MerchantDetail::class);

        $scriptId = 'give-paypal-commerce-js';

        wp_enqueue_script(
            $scriptId,
            GIVE_PLUGIN_URL . 'assets/dist/js/paypal-commerce.js',
            [],
            GIVE_VERSION,
            true
        );

        wp_localize_script(
            $scriptId,
            'givePayPalCommerce',
            [
                'paypalCardInfoErrorPrefixes' => [
                    'expirationDateField' => esc_html__('Card Expiration Date:', 'give'),
                    'cardNumberField' => esc_html__('Card Number:', 'give'),
                    'cardCvcField' => esc_html__('Card CVC:', 'give'),
                ],
                'cardFieldPlaceholders' => [
                    'cardNumber' => esc_html__('Card Number', 'give'),
                    'cardCvc' => esc_html__('CVC', 'give'),
                    'expirationDate' => esc_html__('MM/YY', 'give'),
                ],
                'threeDsCardAuthenticationFailedNotice' => esc_html__(
                    'There was a problem authenticating your payment method. Please try again. If the problem persists, please try another payment method.',
                    'give'
                ),
                'errorCodeLabel' => esc_html__('Error Code', 'give'),
                'genericDonorErrorMessage' => __(
                    'There was an error processing your donation. Please contact the administrator.',
                    'give'
                ),
                // List of style properties support by PayPal for advanced card fields: https://developer.paypal.com/docs/business/checkout/reference/style-guide/#style-the-card-payments-fields
                'hostedCardFieldStyles' => apply_filters('give_paypal_commerce_hosted_field_style', []),
                'supportsCustomPayments' => $merchant->supportsCustomPayments ? 1 : '',
                'separatorLabel' => esc_html__('Or pay with card', 'give'),
                'payPalSdkQueryParameters' => $paypalSDKOptions,
                'textForOverlayScreen' => sprintf(
                    '<h3>%1$s</h3><p>%2$s</p><p>%3$s</p>',
                    esc_html__('Donation Processing...', 'give'),
                    esc_html__('Checking donation status with PayPal.', 'give'),
                    esc_html__('This will only take a second!', 'give')
                )
            ]
        );
    }

    /**
     * Get PayPal partner js url.
     *
     * @since 2.30.0 sandbox PayPal partner js loads slow. So we are using live url for now.
     * @since 2.9.0
     *
     * @return string
     */
    private function getPartnerJsUrl()
    {
        return 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
    }
}
