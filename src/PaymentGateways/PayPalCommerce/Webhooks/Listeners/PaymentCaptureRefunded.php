<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners;

use Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder;
use Give\Repositories\PaymentsRepository;

class PaymentCaptureRefunded implements EventListener {
	/**
	 * @since 2.8.0
	 *
	 * @var PaymentsRepository
	 */
	private $paymentsRepository;

	/**
	 * PaymentCaptureRefunded constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param PaymentsRepository $paymentsRepository
	 */
	public function __construct( PaymentsRepository $paymentsRepository ) {
		$this->paymentsRepository = $paymentsRepository;
	}

	/**
	 * @inheritDoc
	 */
	public function processEvent( $event ) {
		$donation = $this->paymentsRepository->getDonationByPayment( $event->resource->id );

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
