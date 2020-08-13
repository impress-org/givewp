<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

/**
 * Class PaymentCaptureRefunded
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @sicne 2.8.0
 */
class PaymentCaptureRefunded extends PaymentEventListener {
	/**
	 * @inheritDoc
	 */
	public function processEvent( $event ) {
		$paymentId = $this->getPaymentFromRefund( $event->resource, 'up' );

		$donation = $this->paymentsRepository->getDonationByPayment( $paymentId );

		// If there's no matching donation then it's not tracked by GiveWP
		if ( ! $donation ) {
			return;
		}

		give_update_payment_status( $donation->ID, 'refunded' );
		give_insert_payment_note( $donation->ID, __( 'Charge refunded in PayPal', 'give' ) );

		/**
		 * Fires when a charge has been refunded via webhook
		 *
		 * @since 2.8.0
		 */
		do_action( 'give_paypal_commerce_webhook_charge_refunded', $event, $donation );
	}
}
