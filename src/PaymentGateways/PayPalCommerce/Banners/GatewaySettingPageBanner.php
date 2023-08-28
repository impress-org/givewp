<?php

namespace Give\PaymentGateways\PayPalCommerce\Banners;

/**
 * Class GatewaySettingPageBanner
 *
 * This class is used to render banner on gateway settings page.
 *
 * @since 2.33.0
 */
class GatewaySettingPageBanner
{
    /**
     * Setup hook.
     * @since 2.33.0
     * @return void
     */
    public function setupHook()
    {
        // Set highest priority to render banner at the end.
        add_action('give-settings_settings_gateways_page', [$this, 'render'], 999);
    }

    /**
     * Render banner.
     * @since 2.33.0
     * @return void
     */
    public function render()
    {
        // Bailout if:
        // - not on the gateway settings page, or
        // - PayPal Standard is not active.
        if (
            'gateways-settings' !== give_get_current_setting_section() ||
            ! give_is_gateway_active('paypal')
        ) {
            return;
        }

        printf(
            '<div class="give-paypal-migration-banner gateway-settiing-page">
                <p class="message">
                    <span class="label">%1$s</span>%2$s <a href="https://docs.givewp.com/paypal-migrate" target="_blank">%3$s</a>
                <p>
            </div>',
            esc_html__('Important', 'give'),
            esc_html__(
                'PayPal Standard is no longer supported by PayPal. It is recommended to migrate to PayPal Donations.',
                'give'
            ),
            esc_html__('How to migrate safely', 'give')
        );
    }
}
