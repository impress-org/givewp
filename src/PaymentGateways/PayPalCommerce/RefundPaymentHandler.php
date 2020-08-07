<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Exception;
use Give_Payment;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;

/**
 * Class RefundPaymentHandler
 *
 * @since 2.8.0
 */
class RefundPaymentHandler {
	/**
	 * @since 2.8.0
	 *
	 * @var PayPalOrder
	 */
	private $ordersRepository;

	/**
	 * RefundPaymentHandler constructor.
	 *
	 * @since 2.8.0
	 *
	 * @param PayPalOrder $ordersRepository
	 */
	public function __construct( PayPalOrder $ordersRepository ) {
		$this->ordersRepository = $ordersRepository;
	}

	/**
	 * Refunds the payment when the donation is marked as refunded
	 *
	 * @since 2.8.0
	 *
	 * @param Give_Payment $donation
	 *
	 * @throws Exception
	 */
	public function refundPayment( Give_Payment $donation ) {
		$payPalPaymentId = give_get_payment_transaction_id( $donation->ID );

		$this->ordersRepository->refundPayment( $payPalPaymentId );
	}
}
