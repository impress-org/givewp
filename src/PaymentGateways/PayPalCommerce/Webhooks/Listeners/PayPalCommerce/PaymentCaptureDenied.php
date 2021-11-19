<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

/**
 * Class PaymentCaptureDenied
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @since 2.9.0
 */
class PaymentCaptureDenied extends PaymentEventListener
{
    /**
     * @inheritDoc
     */
    public function processEvent($event)
    {
        $donation = $this->paymentsRepository->getDonationByPayment($event->resource->id);

        // If there's no matching donation then it's not tracked by GiveWP
        if ( ! $donation) {
            return;
        }

        // Exit if donation status already set to failed.
        if ( ! give_update_payment_status($donation->ID, 'failed')) {
            return;
        }

        give_insert_payment_note($donation->ID, __('Charge Denied in PayPal', 'give'));

        /**
         * Fires when a charge has been denied via webhook
         *
         * @since 2.9.0
         */
        do_action('give_paypal_commerce_webhook_charge_denied', $event, $donation);
    }
}
