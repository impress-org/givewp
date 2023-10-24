<?php

namespace Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions;

/**
 * Class EnqueueStripeFormBuilderScripts
 *
 * @since 3.0.0
 */
class EnqueueStripeFormBuilderScripts
{

    /**
     * Enqueues the Stripe scripts and styles for the Form Builder.
     *
     * @return void
     */
    public function __invoke()
    {
        $enabledGateways = array_keys(give_get_option('gateways_v3', []));
        $stripeEnabled = in_array('stripe_payment_element', $enabledGateways, true);

        if (!$stripeEnabled) {
            return;
        }

        $scriptAsset = require trailingslashit(GIVE_PLUGIN_DIR) . 'build/stripePaymentElementFormBuilder.asset.php';

        wp_enqueue_script(
            'givewp-stripe-payment-element-form-builder',
            GIVE_PLUGIN_URL . 'build/stripePaymentElementFormBuilder.js',
            $scriptAsset['dependencies'],
            false,
            true
        );

        wp_localize_script(
            'givewp-stripe-payment-element-form-builder',
            'stripePaymentElementGatewaySettings',
            [
                'defaultAccount' => (bool)give_stripe_get_default_account(),
                'allAccounts' => give_stripe_get_all_accounts(),
                'stripeSettingsUrl' => admin_url(
                    'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&group=accounts'
                ),
            ]
        );

        wp_enqueue_style(
            'givewp-stripe-payment-element-form-builder',
            GIVE_PLUGIN_URL . 'build/stripePaymentElementFormBuilder.css'
        );
    }
}
