<?php

namespace Give\Framework\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayAdapter;

class LegacyPaymentGatewayRegisterAdapter {
	/**
	 * Run the necessary legacy hooks on our LegacyPaymentGatewayAdapter
	 * that prepares data to be sent to each gateway
	 *
	 * @since 2.18.0
	 *
	 * @param  string  $gatewayClass
	 */
	public function connectGatewayToLegacyPaymentGatewayAdapter( $gatewayClass ) {
		/** @var LegacyPaymentGatewayAdapter $legacyPaymentGatewayAdapter */
		$legacyPaymentGatewayAdapter = give( LegacyPaymentGatewayAdapter::class );

		/** @var PaymentGatewayInterface $registeredGateway */
		$registeredGateway = give( $gatewayClass );
		$registeredGatewayId = $registeredGateway->getId();

		add_action( "give_{$registeredGatewayId}_cc_form",
			static function ( $formId ) use ( $registeredGateway, $legacyPaymentGatewayAdapter ) {
				echo $legacyPaymentGatewayAdapter->getLegacyFormFieldMarkup( $formId, $registeredGateway );
			} );

		add_action( "give_gateway_{$registeredGatewayId}",
			static function ( $legacyDonationData ) use ( $registeredGateway, $legacyPaymentGatewayAdapter ) {
				$legacyPaymentGatewayAdapter->handleBeforeGateway($legacyDonationData, $registeredGateway);
			} );
	}

	/**
	 * Adds new payment gateways to legacy list for settings
	 *
	 * @since 2.18.0
	 *
	 * @param  array  $gatewaysData
	 * @param  array  $newPaymentGateways
	 *
	 * @return array
	 */
	public function addNewPaymentGatewaysToLegacyListSettings( $gatewaysData, $newPaymentGateways ) {
		foreach ( $newPaymentGateways as $gatewayClassName ) {
			/* @var PaymentGatewayInterface $paymentGateway */
			$paymentGateway = give( $gatewayClassName );

			$gatewaysData[ $paymentGateway->getId() ] = [
				'admin_label' => $paymentGateway->getName(),
				'checkout_label' => $paymentGateway->getPaymentMethodLabel(),
			];
		}

		return $gatewaysData;
	}
}
