<?php

namespace Give\ServiceProviders;

use Give\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCheckout\PayPalCheckout;
use Give\PaymentGateways\PayPalStandard\PayPalStandard;
use Give\PaymentGateways\PaypalSettingPage;

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
		PayPalStandard::class,
		PayPalCheckout::class,
	];

	/**
	 * @inheritDoc
	 */
	public function register() {
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		add_filter( 'give_payment_gateways', [ $this, 'registerGateways' ] );
		add_action( 'give-settings_start', [ $this, 'registerPayPalSettingPage' ] );
	}

	/**
	 * Register paypal setting section.
	 *
	 * @since 2.8.0
	 */
	public function registerPayPalSettingPage() {
		$paypalSettingPage = new PaypalSettingPage();
		$paypalSettingPage->register()->boot();
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


