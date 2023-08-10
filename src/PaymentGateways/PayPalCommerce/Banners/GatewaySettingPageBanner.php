<?php

namespace Give\PaymentGateways\PayPalCommerce\Banners;

/**
 * Class GatewaySettingPageBanner
 *
 * This class is used to render banner on gateway settings page.
 *
 * @unreleased
 */
class GatewaySettingPageBanner
{
    /**
     * Setup hook.
     * @unreleased
     * @return void
     */
    public function setupHook()
    {
        // Set highest priority to render banner at the end.
        add_action('give-settings_settings_gateways_page', [$this, 'render'], 999);
    }

    /**
     * Render banner.
     * @unreleased
     * @return void
     */
    public function render()
    {
        // Bailout if not on the gateway settings page.
        if ('gateways-settings' !== give_get_current_setting_section()) {
            return;
        }

        printf(
            '<div class="give-paypal-migration-banner gateway-settiing-page">
                <p class="message">
                    <i class="label">%1$s</i>%2$s <a href="%3$s">%4$s</a>
                <p>
            </div>',
            esc_html__('Important', 'give'),
            esc_html__('PayPal Standard is no longer supported by PayPal. It is recommended to migrate to PayPal Donations.', 'give'),
            '#',
            esc_html__('How to migrate safely', 'give')
        );
    }
}
