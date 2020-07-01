<?php

namespace Give\ServiceProviders;

use Give\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCheckout\PayPalCheckout;

class PaymentGateways implements ServiceProvider {
	public $gateways = [
		PayPalCheckout::class,
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
