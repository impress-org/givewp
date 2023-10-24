<?php

namespace Give\PaymentGateways\PayPalCommerce\Banners;

/**
 * Class GatewaySettingPageBanner
 *
 * @since 2.33.0
 */
class PayPalDonationsSettingPageBanner
{
    /**
     * @since 2.33.0
     */
    public function render(): string
    {
        return sprintf(
            '<div class="give-paypal-migration-banner paypal-donations-setting-page">
                <p class="message">
                    <span class="icon"></span>%1$s <a href="%2$s">%3$s</a>
                <p>
            </div>',
            esc_html__(
                'Make sure you enable PayPal Donation in the gateway settings to receive payment on your form.',
                'give'
            ),
            esc_url(admin_url('edit.php?post_type=give_forms&page=give-settings&tab=gateways')),
            esc_html__('Go to gateway settings', 'give')
        );
    }
}
