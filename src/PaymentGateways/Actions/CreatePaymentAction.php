<?php

namespace Give\PaymentGateways\Actions;

use Give\Log\Log;
use Give\PaymentGateways\DataTransferObjects\FormData;

/**
 * Class CreatePaymentAction
 * @unreleased
 */
class CreatePaymentAction {
	/**
	 * @unreleased
	 *
	 * @param  FormData  $formData
	 *
	 * @return bool|int
	 */
	public function __invoke( FormData $formData ) {
		// Record the pending payment
		$payment = give_insert_payment( $formData->toPaymentArray() );

		// If errors are present, send the user back to the donation page, so they can be corrected
		if (! $payment ) {
			Log::error( esc_html__( 'Payment Error', 'give' ), $formData->toPaymentArray() );
			give_send_back_to_checkout( '?payment-mode=' . $formData->gateway );
		}

		return $payment;
	}
}
