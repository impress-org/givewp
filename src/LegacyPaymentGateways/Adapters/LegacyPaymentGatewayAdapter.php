<?php

namespace Give\LegacyPaymentGateways\Adapters;

use Give\Framework\PaymentGateways\Contracts\PaymentGatewayInterface;
use Give\PaymentGateways\Actions\CreatePaymentAction;
use Give\PaymentGateways\Actions\CreateSubscriptionAction;
use Give\PaymentGateways\DataTransferObjects\FormData;
use Give\PaymentGateways\DataTransferObjects\GatewayPaymentData;
use Give\PaymentGateways\DataTransferObjects\GatewaySubscriptionData;
use Give\PaymentGateways\DataTransferObjects\GiveInsertPaymentData;
use Give\PaymentGateways\DataTransferObjects\SubscriptionData;

/**
 * Class LegacyPaymentGatewayAdapter
 * @unreleased
 */
class LegacyPaymentGatewayAdapter {

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

		$donationId = $this->createPayment( $formData->toPaymentData() );

		$gatewayPaymentData = GatewayPaymentData::fromArray( [
			'amount' => $formData->amount,
			'currency' => $formData->currency,
			'date' => $formData->date,
			'price' => $formData->price,
			'priceId' => $formData->priceId,
			'gatewayId' => $formData->paymentGateway,
			'paymentId' => $donationId,
			'purchaseKey' => $formData->purchaseKey,
			'donorInfo' => $formData->donorInfo,
			'cardInfo' => $formData->cardInfo,
			'billingAddress' => $formData->billingAddress,
		] );

		if ( function_exists( 'Give_Recurring' ) && Give_Recurring()->is_recurring( $formData->formId ) ) {
			$subscriptionData = SubscriptionData::fromRequest( $request );
			$subscriptionId = $this->createSubscription( $donationId, $formData, $subscriptionData );

			$gatewaySubscriptionData = GatewaySubscriptionData::fromArray( [
				'period' => $subscriptionData->period,
				'times' => $subscriptionData->times,
				'frequency' => $subscriptionData->frequency,
				'subscriptionId' => $subscriptionId,
			] );

			$registeredGateway->createSubscription( $gatewayPaymentData, $gatewaySubscriptionData );
		}

		$registeredGateway->createPayment( $gatewayPaymentData );
	}

	/**
	 * Create the payment
	 *
	 * @unreleased
	 *
	 * @param  GiveInsertPaymentData  $giveInsertPaymentData
	 *
	 * @return int
	 */
	private function createPayment( GiveInsertPaymentData $giveInsertPaymentData ) {
		/** @var CreatePaymentAction $createPaymentAction */
		$createPaymentAction = give( CreatePaymentAction::class );

		return $createPaymentAction( $giveInsertPaymentData );
	}

	/**
	 * Create the payment
	 *
	 * @unreleased
	 *
	 * @param  int  $donationId
	 * @param  FormData  $formData
	 * @param  SubscriptionData  $subscriptionData
	 *
	 * @return int
	 */
	private function createSubscription( $donationId, FormData $formData, SubscriptionData $subscriptionData ) {
		/** @var CreateSubscriptionAction $createSubscriptionAction */
		$createSubscriptionAction = give( CreateSubscriptionAction::class );

		return $createSubscriptionAction( $donationId, $formData, $subscriptionData );
	}

	/**
	 * Validate Gateway Nonce
	 *
	 * @unreleased
	 *
	 * @param  string  $gatewayNonce
	 */
	private function validateGatewayNonce( $gatewayNonce ) {
		if ( ! wp_verify_nonce( $gatewayNonce, 'give-gateway' ) ) {
			wp_die( esc_html__( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.',
				'give' ), esc_html__( 'Error', 'give' ), [ 'response' => 403 ] );
		}
	}
}