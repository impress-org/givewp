<?php

namespace Give\PaymentGateways;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use Give\PaymentGateways\Actions\RegisterPaymentGateways;
use Give\PaymentGateways\Actions\RegisterPaymentGatewaySettingsList;
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
	 * @inheritDoc
	 */
	public function register() {
		give()->singleton( PaymentGatewayRegister::class );
	}

	/**
	 * @inheritDoc
	 */
	public function boot() {
		Hooks::addFilter('give_register_gateway', RegisterPaymentGateways::class);
		Hooks::addFilter('give_payment_gateways', RegisterPaymentGatewaySettingsList::class);
	}
}
