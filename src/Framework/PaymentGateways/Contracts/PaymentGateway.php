<?php

namespace Give\Framework\PaymentGateways\Contracts;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Actions\HandleBeforeGatewayAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\Traits\ValidationHelpers;

/**
 * @unreleased
 */
abstract class PaymentGateway implements PaymentGatewayInterface {
	use ValidationHelpers;

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function id() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getId() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getName() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getPaymentMethodLabel() {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function getFormFields( $formId ) {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * @inheritDoc
	 * @throws Exception
	 */
	public function handleGatewayRequest( $donationId, $formData ) {
		throw new Exception( 'function must be overridden' );
	}

	/**
	 * First we create a payment, then move on to the gateway processing
	 *
	 * @unreleased
	 *
	 * @param  array  $request  Donation Data
	 *
	 * @return void
	 * @throws Exception
	 */
	public function handleFormRequest( $request ) {
		$formData = FormData::fromRequest( $request );

		$this->validateGatewayNonce( $formData->gatewayNonce );

		$donationId = $this->createPayment( $formData );

		return $this->handleGatewayRequest( $donationId, $formData );
	}


	/**
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
