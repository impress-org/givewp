<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Contracts\PaymentGateway;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewaysIterator;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\Framework\PaymentGateways\PaymentGatewayType\OffSitePaymentGateway;
use Give\Framework\PaymentGateways\PaymentGatewayType\OnsitePaymentGateway;

/**
 * @unreleased
 */
class PaymentGatewayRegister extends PaymentGatewaysIterator {
	private $gateways = [];

	/**
	 * @unreleased
	 *
	 * @param string $gatewayClass
	 *
	 * @throws OverflowException|InvalidArgumentException|Exception
	 */
	public function registerGateway( $gatewayClass ) {
		if ( ! is_subclass_of( $gatewayClass, PaymentGateway::class ) ) {
			throw new InvalidArgumentException( sprintf(
				'%1$s must extend %2$s',
				$gatewayClass,
				PaymentGateway::class
			));
		}

		if (
			! is_subclass_of( $gatewayClass, OffSitePaymentGateway::class ) &&
			! is_subclass_of( $gatewayClass, OnsitePaymentGateway::class )
		) {
			throw new InvalidArgumentException( "$gatewayClass must extend either the Offsite or Onsite Payment Gateway interface" );
		}

		$gatewayId = $gatewayClass::id();

		if ( isset( $this->gateways[ $gatewayId ] ) ) {
			throw new OverflowException( "Cannot register a gateway with an id that already exists: $gatewayId" );
		}

		$this->gateways[ $gatewayId ] = $gatewayClass;
	}
}
