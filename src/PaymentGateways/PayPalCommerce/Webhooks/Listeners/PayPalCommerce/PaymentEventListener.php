<?php

namespace Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\MerchantDetails;
use Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\EventListener;
use Give\Repositories\PaymentsRepository;


/**
 * Class PaymentEventListener
 * @package Give\PaymentGateways\PayPalCommerce\Webhooks\Listeners\PayPalCommerce
 *
 * @since 2.8.0
 */
abstract class PaymentEventListener implements EventListener {
	/**
	 * @since 2.8.0
	 *
	 * @var PaymentsRepository
	 */
	protected $paymentsRepository;

	/**
	 * @var MerchantDetails
	 */
	protected $merchantDetails;

	/**
	 * PaymentEventListener constructor.
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
	 * This uses the links property to get payment id from PayPal
	 *
	 * @since 2.8.0
	 *
	 * @param object $refund
	 * @param string $relType
	 *
	 * @return string
	 */
	protected function getPaymentFromRefund( $refund, $relType ) {
		$link = current(
			array_filter(
				$refund->links,
				static function ( $link ) use ( $relType ) {
					return $link->rel === $relType;
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
