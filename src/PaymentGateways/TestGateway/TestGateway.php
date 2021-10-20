<?php

namespace Give\PaymentGateways\TestGateway;

use Give\Framework\PaymentGateways\Contracts\PaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayTypes\OnSitePaymentGateway;

/**
 * Class TestGateway
 * @unreleased
 */
class TestGateway extends PaymentGateway implements OnSitePaymentGateway {
	/**
	 * @inheritDoc
	 */
	public static function id() {
		return 'test-gateway';
	}

	/**
	 * @inheritDoc
	 */
	public function getId() {
		return self::id();
	}

	/**
	 * @inheritDoc
	 */
	public function getName() {
		return 'Test Gateway';
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentMethodLabel() {
		return 'Test Gateway';
	}
}