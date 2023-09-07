<?php

namespace Give\PaymentGateways\Gateways\Offline\Actions;

use Give\PaymentGateways\Gateways\Offline\OfflineGateway;

class EnqueueOfflineFormBuilderScripts
{
    /**
     * Enqueues the Stripe scripts and styles for the Form Builder.
     *
     * @return void
     */
    public function __invoke()
    {
        $scriptAsset = require trailingslashit(GIVE_PLUGIN_DIR) . 'build/offlineGatewayFormBuilder.asset.php';

        wp_enqueue_script(
            'givewp-offline-gateway-form-builder',
            GIVE_PLUGIN_URL . 'build/offlineGatewayFormBuilder.js',
            $scriptAsset['dependencies'],
            false,
            true
        );

        wp_add_inline_script(
            'givewp-offline-gateway-form-builder',
            'window.giveOfflineGatewaySettings = ' . wp_json_encode(
                [
                    'offlineEnabled' => give_is_gateway_active(OfflineGateway::id()),
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
