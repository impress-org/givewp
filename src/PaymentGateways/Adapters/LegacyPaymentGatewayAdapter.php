<?php

namespace Give\PaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\PaymentGateways\Actions\CreatePaymentAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\Traits\ValidationHelpers;

/**
 * Class LegacyPaymentGatewayAdapter
 * @unreleased
 */
class LegacyPaymentGatewayAdapter {
	use ValidationHelpers;

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
		/** @var CreatePaymentAction $createPaymentAction */
		$createPaymentAction = give( CreatePaymentAction::class );

		return $createPaymentAction( $formData );
	}
}