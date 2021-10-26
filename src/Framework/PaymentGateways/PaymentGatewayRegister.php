<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Contracts\PaymentGateway;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewaysIterator;
use Give\Framework\PaymentGateways\Exceptions\OverflowException;
use Give\PaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

/**
 * @unreleased
 */
class PaymentGatewayRegister extends PaymentGatewaysIterator {
	private $gateways = [];

	/**
	 * * Get Gateways
	 *
	 * @unreleased
	 *
	 * @return array
	 */
	public function getPaymentGateways() {
		return $this->gateways;
	}

	/**
	 * Get Gateway
	 *
	 * @unreleased
	 *
	 * @param  string  $id
	 *
	 * @return string
	 */
	public function getPaymentGateway( $id ) {
		if ( ! isset( $this->gateways[ $id ] ) ) {
			throw new InvalidArgumentException( "No migration exists with the ID {$id}" );
		}

		return $this->gateways[ $id ];
	}

	/**
	 * @unreleased
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	public function hasPaymentGateway( $id ) {
		return isset( $this->gateways[ $id ] );
	}

	/**
	 * Register Gateway
	 *
	 * @unreleased
	 *
	 * @param  string  $gatewayClass
	 *
	 * @throws OverflowException|InvalidArgumentException|Exception
	 */
	public function registerGateway( $gatewayClass ) {
		if ( ! is_subclass_of( $gatewayClass, PaymentGateway::class ) ) {
			throw new InvalidArgumentException( sprintf(
				'%1$s must extend %2$s',
				$gatewayClass,
				PaymentGateway::class
			) );
		}

		$gatewayId = $gatewayClass::id();

		if ( isset( $this->gateways[ $gatewayId ] ) ) {
			throw new OverflowException( "Cannot register a gateway with an id that already exists: $gatewayId" );
		}

		$this->gateways[ $gatewayId ] = $gatewayClass;

		$this->connectToLegacyPaymentGatewayAdapter( $gatewayClass );
	}

	/**
	 * Unregister Gateway
	 *
	 * @unreleased
	 *
	 * @param  string  $gatewayClass
	 *
	 * @throws InvalidArgumentException
	 */
	public function unregisterGateway( $gatewayClass ) {
		if ( ! is_subclass_of( $gatewayClass, PaymentGateway::class ) ) {
			throw new InvalidArgumentException( sprintf(
				'%1$s must extend %2$s',
				$gatewayClass,
				PaymentGateway::class
			) );
		}

		$gatewayId = $gatewayClass::id();

		unset( $this->gateways[ $gatewayId ] );
	}

	/**
	 * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
	 * that prepares data to be sent to each gateway
	 *
	 * @param  string  $gateway
	 */
	private function connectToLegacyPaymentGatewayAdapter( $gateway ) {
		/** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
		$legacyPaymentGatewayAdapter = give( LegacyPaymentGatewayAdapter::class );

		/** @var PaymentGatewayInterface $registeredGateway */
		$registeredGateway = give( $gateway );
		$registeredGatewayId = $registeredGateway->getId();

		add_action( "give_{$registeredGatewayId}_cc_form",
			static function ( $formId ) use ( $registeredGateway, $legacyPaymentGatewayAdapter ) {
				echo $legacyPaymentGatewayAdapter->getLegacyFormFieldMarkup( $formId, $registeredGateway );
			} );

		add_action( "give_gateway_{$registeredGatewayId}",
			static function ( $formId ) use ( $registeredGateway, $legacyPaymentGatewayAdapter ) {
				return $legacyPaymentGatewayAdapter->handleBeforeGateway( $formId, $registeredGateway );
			} );
	}
}
