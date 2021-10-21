<?php

namespace Give\ServiceProviders;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\AddNewPaymentGatewaysToOldList;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use Give\PaymentGateways\TestGateway\TestGateway;

/**
 * Class PaymentGateways2
 *
 * The Service Provider for loading the Payment Gateways for Payment Flow 2.0
 *
 * @unreleased
 */
class PaymentGateways2 implements ServiceProvider {
	/**
	 * Array of PaymentGateway classes to be bootstrapped
	 *
	 * @var string[]
	 */
	public $gateways = [
		TestGateway::class
	];
	/**
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( PaymentGatewayRegister::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'give_register_gateway', [ $this, 'registerGateways' ] );
		Hooks::addFilter( 'give_payment_gateways', AddNewPaymentGatewaysToOldList::class );
	}

	/**
	 * Registers all the payment gateways with GiveWP
	 *
	 * @unreleased
	 *
	 * @param array $gateways
	 *
	 * @return array
	 *
	 * @throws InvalidArgumentException|Exception
	 *
	 */
	public function registerGateways( array $gateways ) {
		/** @var PaymentGatewayRegister $paymentGatewayRegister */
		$paymentGatewayRegister = give( PaymentGatewayRegister::class );

		foreach ( $this->gateways as $gateway ) {
			$paymentGatewayRegister->registerGateway( $gateway );

			/** @var PaymentGatewayInterface $registeredGateway */
			$registeredGateway = give( $gateway );
			$registeredGatewayId = $registeredGateway->getId();

			Hooks::addAction( "give_{$registeredGatewayId}_cc_form", $gateway, 'getFormFields' );
			Hooks::addAction( "give_gateway_{$registeredGatewayId}", $gateway, 'handleFormRequest' );
		}

		return $gateways;
	}
}
