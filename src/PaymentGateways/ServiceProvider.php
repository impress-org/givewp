<?php

namespace Give\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\PaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\TestGateway\TestGateway;
use Give\ServiceProviders\ServiceProvider as ServiceProviderInterface;

/**
 * Class ServiceProvider - PaymentGateways
 *
 * The Service Provider for loading the Payment Gateways for Payment Flow 2.0
 *
 * @unreleased
 */
class ServiceProvider implements ServiceProviderInterface {
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
		add_filter( 'give_payment_gateways', [ $this, 'registerGatewaySettingsList' ] );
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
		}

		$this->register3rdPartyPaymentGateways( $paymentGatewayRegister );
		$this->unregister3rdPartyPaymentGateways( $paymentGatewayRegister );

		return $gateways;
	}

	/**
	 * Add gateways to settings list
	 *
	 * @param  array  $gatewayData
	 */
	public function registerGatewaySettingsList( $gatewayData ) {
		/** @var LegacyPaymentGatewayRegisterAdapter $legacyPaymentGatewayRegisterAdapter */
		$legacyPaymentGatewayRegisterAdapter = give( LegacyPaymentGatewayRegisterAdapter::class );

		/** @var PaymentGatewayRegister $paymentGatewayRegister */
		$paymentGatewayRegister = give( PaymentGatewayRegister::class );

		$newPaymentGateways = $paymentGatewayRegister->getPaymentGateways();

		if ( ! $newPaymentGateways ) {
			return $gatewayData;
		}

		return $legacyPaymentGatewayRegisterAdapter->addNewPaymentGatewaysToLegacyListSettings(
			$gatewayData,
			$paymentGatewayRegister->getPaymentGateways()
		);
	}

	/**
	 * Register 3rd party payment gateways
	 *
	 * @param  PaymentGatewayRegister  $paymentGatewayRegister
	 */
	private function register3rdPartyPaymentGateways( PaymentGatewayRegister $paymentGatewayRegister ) {
		do_action( 'give_register_payment_gateway', $paymentGatewayRegister );
	}

	/**
	 * Unregister 3rd party payment gateways
	 *
	 * @param  PaymentGatewayRegister  $paymentGatewayRegister
	 */
	private function unregister3rdPartyPaymentGateways( PaymentGatewayRegister $paymentGatewayRegister ) {
		do_action( 'give_unregister_payment_gateway', $paymentGatewayRegister );
	}
}
