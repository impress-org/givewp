<?php

namespace Give\ServiceProviders;

use Give\PaymentGateways\PaymentGateway;
use Give\PaymentGateways\PayPalCommerce\onBoardingRedirectHandler;
use Give\PaymentGateways\PayPalCommerce\PayPalCommerce;
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
		PayPalCommerce::class,
	];

	/**
	 * Array of SettingPage classes to be bootstrapped
	 *
	 * @var string[]
	 */
	private $gatewaySettingsPages = [
		PaypalSettingPage::class,
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
		add_filter( 'give_register_gateway', [ $this, 'registerGateways' ] );
		add_action( 'admin_init', [ $this, 'handleSellerOnBoardingRedirect' ] );
		add_action( 'give-settings_start', [ $this, 'registerPayPalSettingPage' ] );
	}

	/**
	 * Handle seller on boarding redirect.
	 *
	 * @since 2.8.0
	 */
	public function handleSellerOnBoardingRedirect() {
		give( onBoardingRedirectHandler::class )->boot();
	}

	/**
	 * Register all payment gateways setting pages with GiveWP.
	 *
	 * @since 2.8.0
	 */
	public function registerPayPalSettingPage() {
		foreach ( $this->gatewaySettingsPages  as $page ) {
			give()->make( $page )->boot();
		}
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

			$gateway->boot();
		}

		return $gateways;
	}
}


