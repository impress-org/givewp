<?php

namespace Give\PaymentGateways\Gateways\Offline\Actions;

use Give\PaymentGateways\Gateways\Offline\OfflineGateway;

class EnqueueOfflineFormBuilderScripts
{
    /**
     * Enqueues the Stripe scripts and styles for the Form Builder.
     *
     * @since 3.16.2 On the "offlineEnabled" option check if the offline gateway is enabled  for v3 forms instead of v2 forms
     *
     * @return void
     */
    public function __invoke()
    {
        $scriptAsset = require trailingslashit(GIVE_PLUGIN_DIR) . 'build/offlineGatewayFormBuilder.asset.php';

        wp_enqueue_style(
            'givewp-offline-gateway-form-builder',
            GIVE_PLUGIN_URL . 'build/offlineGatewayFormBuilder.css',
            [],
            $scriptAsset['version']
        );

        wp_enqueue_script(
            'givewp-offline-gateway-form-builder',
            GIVE_PLUGIN_URL . 'build/offlineGatewayFormBuilder.js',
            $scriptAsset['dependencies'],
            $scriptAsset['version'],
            true
        );

        wp_add_inline_script(
            'givewp-offline-gateway-form-builder',
            'window.giveOfflineGatewaySettings = ' . wp_json_encode(
                [
                    'offlineEnabled' => give_is_gateway_active(OfflineGateway::id(), 3),
                    'offlineSettingsUrl' => admin_url(
                        'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=offline-donations'
                    ),
                    'defaultInstructions' => give_get_default_offline_donation_content(),
                ]
            ),
            'before'
        );
    }
}
