<?php

namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give_Payment;

class RefundPaymentHandler {
	/**
	 * @var PayPalOrder
	 */
	private $ordersRepository;

	public function __construct( PayPalOrder $ordersRepository ) {
		$this->ordersRepository = $ordersRepository;
	}

	public function refundPayment( Give_Payment $donation ) {
		$payPalPaymentId = give_get_payment_transaction_id( $donation->ID );

		$this->ordersRepository->refundPayment( $payPalPaymentId );
	}
}
