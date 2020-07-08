<?php

namespace Give\ServiceProviders;

use Give\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;

/**
 * Class PaymentGateways
 *
 * The Service Provider for loading the Payment Gateways
 *
 * @since 2.8.0
 */
class PaymentGateways implements ServiceProvider {
	/**
	 * Array of PaymentGateway classes to be bootstrapped
	 *
	 * @var string[]
	 */
	public $gateways = [
		PayPalCommerce::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
		// Not used, but needed for interface
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'give_payment_gateways', [ $this, 'registerGateways' ] );
	}

	/**
	 * Registers all of the payment gateways with GiveWP
	 *
	 * @since 2.8.0
	 *
	 * @param array $gateways
	 *
	 * @return array
	 */
	public function registerGateways( array $gateways ) {
		foreach ( $this->gateways as $gateway ) {
			/** @var PaymentGateway $gateway */
			$gateway = new $gateway();

			$gateways[ $gateway->getId() ] = [
				'admin_label'    => $gateway->getName(),
				'checkout_label' => $gateway->getPaymentMethodLabel(),
			];
		}

		return $gateways;
	}
}
