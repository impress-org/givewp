<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Admin;

use Give\PaymentGateways\Stripe\Webhooks\Controllers\EnsureWebhookEndpointsController;
use Give\PaymentGateways\Stripe\Webhooks\WebhookHealthMonitor;

/**
 * Adds the setup button and the health email option to the Webhooks group of the Stripe settings.
 *
 * @since TBD
 */
final class WebhookSettingsFields
{
    public const SETUP_FIELD_TYPE = 'stripe_webhook_setup';

    /**
     * @since TBD
     *
     * @param array $settings Settings of the Payment Gateways tab, keyed by group.
     *
     * @return array
     */
    public function addFields($settings)
    {
        if ('stripe-settings' !== give_get_current_setting_section() || empty($settings['webhooks'])) {
            return $settings;
        }

        $sectionEnd = array_pop($settings['webhooks']);

        $settings['webhooks'][] = [
            'name' => esc_html__('Webhook Setup', 'give'),
            'id' => 'stripe_webhook_setup',
            'type' => self::SETUP_FIELD_TYPE,
        ];

        $settings['webhooks'][] = [
            'name' => esc_html__('Email Site Administrator About Webhook Health', 'give'),
            'desc' => esc_html__(
                'GiveWP checks every hour that each Stripe account sends events to this site. When one does not, GiveWP emails the address that WordPress sends fatal error emails to.',
                'give'
            ),
            'id' => WebhookHealthMonitor::EMAIL_SETTING,
            'type' => 'radio_inline',
            'default' => 'enabled',
            'options' => [
                'enabled' => esc_html__('Enabled', 'give'),
                'disabled' => esc_html__('Disabled', 'give'),
            ],
        ];

        $settings['webhooks'][] = $sectionEnd;

        return $settings;
    }

    /**
     * @since TBD
     *
     * @param array $field
     */
    public function renderSetupField($field): void
    {
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label><?php echo esc_html($field['name']); ?></label>
            </th>
            <td class="give-forminp">
                <button
                    type="button"
                    class="button give-stripe-webhook-setup-button"
                    data-nonce="<?php echo esc_attr(wp_create_nonce(EnsureWebhookEndpointsController::ACTION)); ?>"
                ><?php esc_html_e('Set up webhooks', 'give'); ?></button>
                <p class="give-field-description">
                    <?php
                    esc_html_e(
                        'For each Stripe account, GiveWP looks for a webhook endpoint that points at this site. It creates the endpoint when there is none, and enables it or adds missing events when it exists.',
                        'give'
                    );
                    ?>
                </p>
            </td>
        </tr>
        <?php
    }
}
