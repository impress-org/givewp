<?php

namespace Give\PaymentGateways\TestGateway\Actions;

use Give\PaymentGateways\TestGateway\DataTransferObjects\TestGatewayFormData;

/**
 * Class TestGatewayHandleFormRequestAction
 * @unreleased
 */
class TestGatewayHandleFormRequestAction {
	/**
	 * @unreleased
	 *
	 * @return void
	 */
	public function __invoke(TestGatewayFormData $formData) {
		// Record the pending payment
		$payment = give_insert_payment( $formData->toPaymentArray() );

		if (! $payment ) {
			give_record_gateway_error(
				esc_html__( 'Payment Error', 'give' ),
				sprintf(
				/* translators: %s: payment data */
					esc_html__( 'The payment creation failed while processing a manual (free or test) donation. Payment data: %s',
						'give' ),
					json_encode( $formData->toPaymentArray() )
				),
				$payment
			);
			// If errors are present, send the user back to the donation page so they can be corrected
			give_send_back_to_checkout( '?payment-mode=' . $formData->gateway );
		}

		give_update_payment_status( $payment, 'publish' );
		give_send_to_success_page();
	}
}
