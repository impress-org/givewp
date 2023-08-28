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
        $scriptAsset = require GIVE_PLUGIN_DIR . '/build/stripePaymentElementFormBuilder.asset.php';

        wp_enqueue_script(
            'givewp-stripe-payment-element-form-builder',
            GIVE_PLUGIN_URL . 'build/stripePaymentElementFormBuilder.js',
            $scriptAsset['dependencies'],
            false,
            true
        );

        wp_enqueue_style(
            'givewp-stripe-payment-element-form-builder',
            GIVE_PLUGIN_URL . 'build/stripePaymentElementFormBuilder.css'
        );
    }
}
