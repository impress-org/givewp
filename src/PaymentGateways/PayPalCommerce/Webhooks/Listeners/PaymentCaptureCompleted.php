<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\Repositories\PaymentsRepository;

class PaymentCaptureCompleted implements EventListener {
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

		give_update_payment_status( $donation->ID, 'publish' );
		give_insert_payment_note( $donation->ID, __( 'Charge Completed in PayPal', 'give' ) );

		/**
		 * Fires when a charge has been completed via webhook
		 *
		 * @since 2.8.0
		 */
		do_action( 'give_paypal_commerce_webhook_charge_completed', $event, $donation );
	}

	/**
	 * This uses the links property to get payment from PayPal
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
				static function ( $link ) {
					return $link->rel === 'self';
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
