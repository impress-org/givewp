<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\Repositories\PaymentsRepository;

class PaymentCaptureRefunded implements EventListener {
	/**
	 * @since 2.8.0
	 *
	 * @var PaymentsRepository
	 */
	private $paymentsRepository;

	/**
	 * @var MerchantDetails
	 */
	private $merchantDetails;

	/**
	 * PaymentCaptureRefunded constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param PaymentsRepository $paymentsRepository
	 * @param MerchantDetails    $merchantDetails
	 */
	public function __construct( PaymentsRepository $paymentsRepository, MerchantDetails $merchantDetails ) {
		$this->paymentsRepository = $paymentsRepository;
		$this->merchantDetails    = $merchantDetails;
	}

	/**
	 * @inheritDoc
	 */
	public function processEvent( $event ) {
		$paymentId = $this->getPaymentFromRefund( $event->resource );

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

	/**
	 * This uses the links property of the refund to retrieve the refunded Payment from PayPal
	 *
	 * @since 2.8.0
	 *
	 * @param object $refund
	 *
	 * @return string
	 */
	private function getPaymentFromRefund( $refund ) {
		$link = current(
			array_filter(
				$refund->links,
				function ( $link ) {
					return $link->rel === 'up';
				}
			)
		);

		$accountDetails = $this->merchantDetails->getDetails();

		$response = wp_remote_request(
			$link->href,
			[
				'method'  => $link->method,
				'headers' => [
					'Content-Type'  => 'application/json',
					'Authorization' => "Bearer $accountDetails->accessToken",
				],
			]
		);

		$response = json_decode( $response['body'], false );

		return $response->id;
	}
}
