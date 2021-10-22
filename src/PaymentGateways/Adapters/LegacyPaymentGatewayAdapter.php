<?php

namespace Give\PaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\PaymentGateways\Actions\HandleBeforeGatewayAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\Traits\ValidationHelpers;

/**
 * Class LegacyPaymentGatewayAdapter
 * @unreleased
 */
class LegacyPaymentGatewayAdapter {
	use ValidationHelpers;

	/**
	 * @var PaymentGatewayRegister
	 */
	private $paymentGatewayRegister;

	public function __construct( PaymentGatewayRegister $paymentGatewayRegister ) {
		$this->paymentGatewayRegister = $paymentGatewayRegister;
	}

	/**
	 * Adds new payment gateways to legacy list for settings
	 *
	 * @unreleased
	 *
	 * @param  array  $gatewaysData
	 *
	 * @return array
	 */
	public function addNewPaymentGatewaysToLegacyList( $gatewaysData ) {
		$newPaymentGateways = $this->paymentGatewayRegister->getPaymentGateways();

		if ( ! $newPaymentGateways ) {
			return $gatewaysData;
		}

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

	/**
	 * Get legacy form field markup to display gateway specific payment fields
	 *
	 * @unreleased
	 *
	 * @param  int  $formId
	 * @param  PaymentGatewayInterface  $registeredGateway
	 *
	 * @return string|bool
	 */
	public function getLegacyFormFieldMarkup( $formId, $registeredGateway ) {
		return $registeredGateway->getLegacyFormFieldMarkup( $formId );
	}

	/**
	 * First we create a payment, then move on to the gateway processing
	 *
	 * @unreleased
	 *
	 * @param  array  $request  Donation Data
	 * @param  PaymentGatewayInterface  $registeredGateway
	 *
	 */
	public function handleBeforeGateway( $request, $registeredGateway ) {
		$formData = FormData::fromRequest( $request );

		$this->validateGatewayNonce( $formData->gatewayNonce );

		$donationId = $this->createPayment( $formData );

		return $registeredGateway->handleGatewayRequest( $donationId, $formData );
	}

	/**
	 * Create the payment
	 *
	 * @param  FormData  $formData
	 *
	 * @return int
	 */
	private function createPayment( FormData $formData ) {
		/** @var HandleBeforeGatewayAction $handleBeforeGatewayAction */
		$handleBeforeGatewayAction = give( HandleBeforeGatewayAction::class );

		return $handleBeforeGatewayAction( $formData );
	}
}