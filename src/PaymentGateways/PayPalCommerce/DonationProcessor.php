<?php
namespace Give\PaymentGateways\PayPalCommerce;

use Give\PaymentGateways\PayPalCommerce\Models\PayPalOrder;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

/**
 * Class DonationProcessor
 * @package Give\PaymentGateways\PayPalCommerce
 *
 * @since 2.8.0
 */
class DonationProcessor {
	/**
	 * Handle donation form submission.
	 *
	 * @param array $donationFormData
	 *
	 * @since 2.8.0
	 */
	public function handle( $donationFormData ) {
		$formId = absint( $donationFormData['post_data']['give-form-id'] );

		$donationData = [
			'price'           => $donationFormData['price'],
			'give_form_title' => $donationFormData['post_data']['give-form-title'],
			'give_form_id'    => $formId,
			'give_price_id'   => isset( $donationFormData['post_data']['give-price-id'] ) ? $donationFormData['post_data']['give-price-id'] : '',
			'date'            => $donationFormData['date'],
			'user_email'      => $donationFormData['user_email'],
			'purchase_key'    => $donationFormData['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $donationFormData['user_info'],
			'status'          => 'pending',
			'gateway'         => $donationFormData['gateway'],
		];

		$donationId = give_insert_payment( $donationData );

		if ( ! $donationId ) {
			$this->redirectBackToDonationForm( $donationFormData );
		}

		$this->redirectDonorToSuccessPage( $donationFormData, $donationId, $formId );

		exit();
	}

	/**
	 * Return back to donation form page after logging error.
	 *
	 * @since 2.8.0
	 * @param array $donationFormData
	 */
	private function redirectBackToDonationForm( $donationFormData ) {
		// Record the error.
		give_record_gateway_error(
			esc_html__( 'Payment Error', 'give' ),
			/* translators: %s: payment data */
			sprintf(
				esc_html__( 'The payment creation failed before processing the PayPalCommerce gateway request. Payment data: %s', 'give' ),
				print_r( $donationFormData, true )
			)
		);

		give_set_error( 'give', esc_html__( 'An error occurred while processing your payment. Please try again.', 'give' ) );

		// Problems? Send back.
		give_send_back_to_checkout();
	}

	/**
	 * Redirect donor to success page.
	 *
	 * @param array $donationFormData
	 * @param  int  $donationId
	 * @param  int  $formId
	 *
	 * @since 2.8.0
	 */
	private function redirectDonorToSuccessPage( $donationFormData, $donationId, $formId ) {

		$orderDetailRequest = new OrdersGetRequest( $donationFormData['post_data']['payPalOrderId'] );
		$orderDetails       = (array) give( PayPalClient::class )->getHttpClient()->execute( $orderDetailRequest )->result;

		$order = PayPalOrder::fromArray( $orderDetails );

		give_insert_payment_note(
			$donationId,
			sprintf(
				__( 'Transaction Successful. PayPal Transaction ID: %1$s    PayPal Order ID: %2$s', 'give' ),
				$order->payment->id,
				$order->id
			)
		);
		give_set_payment_transaction_id( $donationId, $order->payment->id );
		give( 'payment_meta' )->update_meta( $donationId, '_give_order_id', $order->id );

		// Do not need to set donation to complete if already completed by PayPal webhook.
		if ( 'COMPLETED' === $order->payment->status ) {
			give_update_payment_status( $donationId );
		}

		wp_safe_redirect(
			add_query_arg(
				[ 'payment-confirmation' => 'paypal-commerce' ],
				give_get_success_page_url()
			)
		);

		exit();
	}
}
