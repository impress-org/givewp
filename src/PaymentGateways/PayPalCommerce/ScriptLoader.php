<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\MerchantDetail;
use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give_Admin_Settings;

/**
 * Class ScriptLoader
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.9.0
 */
class ScriptLoader
{
    /**
     * @since 2.9.0
     *
     * @var MerchantDetails
     */
    private $merchantRepository;

    /**
     * ScriptLoader constructor.
     *
     * @since 2.9.0
     *
     * @param MerchantDetails $merchantRepository
     */
    public function __construct(MerchantDetails $merchantRepository)
    {
        $this->merchantRepository = $merchantRepository;
    }

    /**
     * Load admin scripts
     *
     * @since 2.9.0
     */
    public function loadAdminScripts()
    {
        if ( ! Give_Admin_Settings::is_setting_page('gateway', 'paypal')) {
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
                    'liveWarning' => give_is_test_mode() ? esc_html__(
                        'You have connected your account for test mode. You will need to connect again once you
						are in live mode.',
                        'give'
                    ) : '',
                ],
            ]
        );

        $script = <<<EOT
				function givePayPalOnBoardedCallback(authCode, sharedId) {
					const query = '&authCode=' + authCode + '&sharedId=' + sharedId;
					fetch( ajaxurl + '?action=give_paypal_commerce_user_on_boarded' + query )
						.then(function(res){ return res.json() })
						.then(function(res) {
							if ( true !== res.success ) {
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
     * @since 2.9.0
     */
    public function loadPublicAssets()
    {
        if ( ! Utils::gatewayIsActive() || ! Utils::isAccountReadyToAcceptPayment()) {
            return;
        }

        /* @var MerchantDetail $merchant */
        $merchant = give(MerchantDetail::class);
        $scriptId = 'give-paypal-commerce-js';

        /**
         * List of PayPal query parameters: https://developer.paypal.com/docs/checkout/reference/customize-sdk/#query-parameters
         */
        $payPalSdkQueryParameters = [
            'client-id' => $merchant->clientId,
            'merchant-id' => $merchant->merchantIdInPayPal,
            'components' => 'hosted-fields,buttons',
            'locale' => get_locale(),
            'disable-funding' => 'credit',
            'vault' => true,
            'data-partner-attribution-id' => give('PAYPAL_COMMERCE_ATTRIBUTION_ID'),
            'data-client-token' => $this->merchantRepository->getClientToken(),
        ];

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
                'accountCountry' => $merchant->accountCountry,
                'separatorLabel' => esc_html__('Or pay with card', 'give'),
                'payPalSdkQueryParameters' => $payPalSdkQueryParameters,
                'textForOverlayScreen' => sprintf(
                    '<h3>%1$s</h3><p>%2$s</p><p>%3$s</p>',
                    esc_html__('Donation Processing...', 'give'),
                    esc_html__('Checking donation status with PayPal.', 'give'),
                    esc_html__('This will only take a second!', 'give')
                ),
            ]
        );
    }

    /**
     * Get PayPal partner js url.
     *
     * @since 2.9.0
     *
     * @return string
     */
    private function getPartnerJsUrl()
    {
        return sprintf(
            '%1$swebapps/merchantboarding/js/lib/lightbox/partner.js',
            give(PayPalClient::class)->getHomePageUrl()
        );
    }
}
