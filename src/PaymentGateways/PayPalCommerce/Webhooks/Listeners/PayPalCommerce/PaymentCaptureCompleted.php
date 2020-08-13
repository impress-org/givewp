<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

/**
 * Class PaymentCaptureCompleted
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @since 2.8.0
 */
class PaymentCaptureCompleted extends PaymentEventListener {
	/**
	 * @inheritDoc
	 */
	public function processEvent( $event ) {
		$paymentId = $this->getPaymentFromRefund( $event->resource, 'self' );

		$donation = $this->paymentsRepository->getDonationByPayment( $paymentId );

		// If there's no matching donation then it's not tracked by GiveWP
		if ( ! $donation || 'publish' === $donation->status ) {
			return;
		}

		give_update_payment_status( $donation->ID, 'publish' );
		give_insert_payment_note( $donation->ID, __( 'Charge Completed in PayPal', 'give' ) );

		/**
		 * Fires when a charge has been completed via webhook
		 *
		 * @since 2.8.0
		 */
		do_action( 'give_paypal_commerce_webhook_charge_completed', $event, $donation );
	}
}
