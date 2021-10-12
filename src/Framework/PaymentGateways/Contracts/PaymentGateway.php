<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface {

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function id() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getId() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getName() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getPaymentMethodLabel() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getOptions() {
		throw new Exception( 'function must be overridden' );
	}
}
