<?php

namespace Give\PaymentGateways\Gateways\PayPalStandard;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Http\Response\Types\RedirectResponse;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\Framework\Support\Scripts\Concerns\HasScriptAssetFile;
use Give\Helpers\Language;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\CreatePayPalStandardPaymentURL;
use Give\PaymentGateways\Gateways\PayPalStandard\Controllers\PayPalStandardWebhook;
use Give\PaymentGateways\Gateways\PayPalStandard\Views\PayPalStandardBillingFields;

/**
 * @since 3.0.0 added support for Give 3.0 forms
 * @since 2.19.0
 */
class PayPalStandard extends PaymentGateway
{
    use HandleHttpResponses;
    use HasScriptAssetFile;

    public $routeMethods = [
        'handleIpnNotification',
    ];

    public $secureRouteMethods = [
        'handleSuccessPaymentReturn',
        'handleCancelledPaymentReturn',
    ];

    /**
     * @since 2.19.0
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        return (new PayPalStandardBillingFields())($formId);
    }

    /**
     * @since 2.19.0
     */
    public static function id(): string
    {
        return 'paypal';
    }

    /**
     * @since 2.19.0
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @since 2.19.0
     */
    public function getName(): string
    {
        return __('PayPal Standard', 'give');
    }

    /**
     * @since 2.19.0
     */
    public function getPaymentMethodLabel(): string
    {
        return esc_html__('PayPal', 'give');
    }

    /**
     * @since 3.0.0
     */
    public function formSettings(int $formId): array
    {
        return [
            'fields' => [
                'heading' => __('Make your donation quickly and securely with PayPal', 'give'),
                'subheading' => __('How it works', 'give'),
                'body' => __(
                    'You will be redirected to PayPal to complete your donation with your debit card, credit card, or with your PayPal account. Once complete, you will be redirected back to this site to view your receipt.',
                    'give'
                ),
            ]
        ];
    }

    /**
     * @since 3.1.0 set translations for script
     * @since 3.0.0
     */
    public function enqueueScript(int $formId)
    {
        $assets = $this->getScriptAsset(GIVE_PLUGIN_DIR . 'build/payPalStandardGateway.asset.php');
        $handle = $this::id();

        wp_enqueue_script(
            $handle,
            GIVE_PLUGIN_URL . 'build/payPalStandardGateway.js',
            $assets['dependencies'],
            $assets['version'],
            true
        );

        Language::setScriptTranslations($handle);
    }

    /**
     * @since 3.0.0 update to add `givewp-return-url` to the query params
     * @since 2.19.0
     */
    public function createPayment(Donation $donation, $gatewayData = []): RedirectOffsite
    {
        return new RedirectOffsite(
            (new CreatePayPalStandardPaymentURL())(
                $donation,
                $this->generateSecureGatewayRouteUrl(
                    'handleSuccessPaymentReturn',
                    $donation->id,
                    [
                        'donation-id' => $donation->id,
                        'givewp-return-url' => $gatewayData['successUrl']
                    ]
                ),
                $this->generateSecureGatewayRouteUrl(
                    'handleCancelledPaymentReturn',
                    $donation->id,
                    [
                        'donation-id' => $donation->id,
                        'givewp-return-url' => $gatewayData['cancelUrl']
                    ]
                ),
                $this->generateGatewayRouteUrl(
                    'handleIpnNotification'
                )
            )
        );
    }

    /**
     * Handle payment redirect after successful payment on PayPal standard.
     *
     * @since 3.0.0 update to use the $gatewayParams return url
     * @since 2.19.0
     * @since 2.19.4 Only pending PayPal Standard donation set to processing.
     * @since 2.19.6 1. Do not set donation to "processing" 2. Add "payment-confirmation" param to receipt page url
     */
    protected function handleSuccessPaymentReturn(array $queryParams): RedirectResponse
    {
        return new RedirectResponse(esc_url_raw($queryParams['givewp-return-url']));
    }

    /**
     * This method is called when the user cancels the payment on PayPal.
     *
     * @since 3.0.0
     */
    protected function handleCancelledPaymentReturn(array $queryParams): RedirectResponse
    {
        $donationId = (int)$queryParams['donation-id'];

        /** @var Donation $donation */
        $donation = Donation::find($donationId);
        $donation->status = DonationStatus::CANCELLED();
        $donation->save();

        return new RedirectResponse(esc_url_raw($queryParams['givewp-return-url']));
    }

    /**
     * Handle PayPal IPN notification.
     *
     * @since 2.19.0
     */
    public function handleIpnNotification()
    {
        give(PayPalStandardWebhook::class)->handle();
    }

    /**
     * This function returns payment gateway settings.
     *
     * @since 2.19.0
     */
    public function getOptions(): array
    {
        $setting = [
            // Section 2: PayPal Standard.
            [
                'type' => 'title',
                'id' => 'give_title_gateway_settings_2',
            ],
            [
                'name' => esc_html__('PayPal Email', 'give'),
                'desc' => esc_html__(
                    'Enter the email address associated with your PayPal account to connect with the gateway.',
                    'give'
                ),
                'id' => 'paypal_email',
                'type' => 'email',
            ],
            [
                'name' => esc_html__('PayPal Page Style', 'give'),
                'desc' => esc_html__(
                    'Enter the name of the PayPal page style to use, or leave blank to use the default.',
                    'give'
                ),
                'id' => 'paypal_page_style',
                'type' => 'text',
            ],
            [
                'name' => esc_html__('PayPal Transaction Type', 'give'),
                'desc' => esc_html__(
                    'Nonprofits must verify their status to withdraw donations they receive via PayPal. PayPal users that are not verified nonprofits must demonstrate how their donations will be used, once they raise more than $10,000. By default, GiveWP transactions are sent to PayPal as donations. You may change the transaction type using this option if you feel you may not meet PayPal\'s donation requirements.',
                    'give'
                ),
                'id' => 'paypal_button_type',
                'type' => 'radio_inline',
                'options' => [
                    'donation' => esc_html__('Donation', 'give'),
                    'standard' => esc_html__('Standard Transaction', 'give'),
                ],
                'default' => 'donation',
            ],
            [
                'name' => esc_html__('Billing Details', 'give'),
                'desc' => esc_html__(
                    'If enabled, required billing address fields are added to PayPal Standard forms. These fields are not required by PayPal to process the transaction, but you may have a need to collect the data. Billing address details are added to both the donation and donor record in GiveWP.',
                    'give'
                ),
                'id' => 'paypal_standard_billing_details',
                'type' => 'radio_inline',
                'default' => 'disabled',
                'options' => [
                    'enabled' => esc_html__('Enabled', 'give'),
                    'disabled' => esc_html__('Disabled', 'give'),
                ],
            ],
            [
                'id' => 'paypal_invoice_prefix',
                'name' => esc_html__('Invoice ID Prefix', 'give'),
                'desc' => esc_html__(
                    'Enter a prefix for your invoice numbers. If you use your PayPal account for multiple fundraising platforms or ecommerce stores, ensure this prefix is unique. PayPal will not allow orders or donations with the same invoice number.',
                    'give'
                ),
                'type' => 'text',
                'default' => 'GIVE-',
            ],
            [
                'name' => esc_html__('PayPal Standard Gateway Settings Docs Link', 'give'),
                'id' => 'paypal_standard_gateway_settings_docs_link',
                'url' => esc_url('http://docs.givewp.com/settings-gateway-paypal-standard'),
                'title' => esc_html__('PayPal Standard Gateway Settings', 'give'),
                'type' => 'give_docs_link',
            ],
            [
                'type' => 'sectionend',
                'id' => 'give_title_gateway_settings_2',
            ],
        ];

        /**
         * filter the settings.
         *
         * @since 2.9.6
         */
        return apply_filters('give_get_settings_paypal_standard', $setting);
    }

    /**
     * @since 2.20.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }
}
