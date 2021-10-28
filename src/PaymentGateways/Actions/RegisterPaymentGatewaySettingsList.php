<?php

namespace Give\PaymentGateways;

use Give\Framework\PaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;

class RegisterPaymentGatewaySettingsList {
	/**
	 * Add gateways to settings list
	 *
	 * @unreleased 
	 *
	 * @param  array  $gatewayData
	 */
	public function __invoke( $gatewayData ) {
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
}
