<?php

namespace Give\Framework\PaymentGateways;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Contracts\PaymentGateway;

/**
 *
 * @unreleased
 */
class AddNewPaymentGatewaysToOldList {
	/**
	 * @param array $gatewaysData
	 *
	 * @return array
	 * @throws Exception
	 */
	public function __invoke( $gatewaysData ) {
		$newPaymentGateways = give( PaymentGatewayRegister::class)->getPaymentGateways();

		if( ! $newPaymentGateways ) {
			return $gatewaysData;
		}

		foreach ( $newPaymentGateways as $paymentGatewayId => $className ){
			/* @var PaymentGateway $paymentGatewayObj */
			$paymentGatewayObj = give( $className );

			$gatewaysData[$paymentGatewayId] = [
				'admin_label' => $paymentGatewayObj->getName(),
				'checkout_label' => $paymentGatewayObj->getPaymentMethodLabel(),
			];
		}

		return $gatewaysData;
	}
}
