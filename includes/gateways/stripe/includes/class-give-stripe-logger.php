<?php
/**
 * Give - Stripe Core - Logger Class
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Stripe_Logger
 *
 * @since 2.5.0
 */
class Give_Stripe_Logger {

	/**
	 * Give_Stripe_Logger constructor.
	 *
	 * @since 2.5.0
	 */
	public function __construct() {
	}

	/**
	 * Log a Stripe Error.
	 *
	 * Logs in the Give db the error and also displays the error message to the donor.
	 *
	 * @param \Stripe\Error\Base|\Stripe\Error\Card $exception    Stripe Exception Object.
	 * @param string                                $payment_mode Payment Mode.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public static function log_error( $exception, $payment_mode ) {

		$body       = $exception->getJsonBody();
		$error      = $body['error'];
		$error_code = ! empty( $error['code'] ) ? $error['code'] : '';

		// Update the error message based on custom error message using error code.
		$translated_error_message = self::get_card_error_message( $error_code );
		$error['message']         = ! empty( $translated_error_message ) ? $translated_error_message : $error['message'];

		$message = __( 'The payment gateway returned an error while processing the donation.', 'give' ) . '<br><br>';

		// Bad Request of some sort.
		if ( isset( $error['message'] ) ) {
			$message .= sprintf(
				/* translators: 1. Error Message */
				__( 'Message: %s', 'give' ),
				$error['message']
			);
			$message .= '<br><br>';
			$message .= sprintf(
				/* translators: 1. Error Code */
				__( 'Code: %s', 'give' ),
				$error_code
			);

			give_set_error( 'stripe_request_error', $error['message'] );
		} else {
			give_set_error( 'stripe_request_error', __( 'The Stripe API request was invalid, please try again.', 'give' ) );
		}

		// Log it with DB.
		give_record_gateway_error( __( 'Stripe Error', 'give' ), $message );
		give_send_back_to_checkout( '?payment-mode=' . $payment_mode );

		return false;

	}

	/**
	 * This function is used to fetch the custom card error messages.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param string $error_code Error Code.
	 *
	 * @return string
	 */
	public static function get_card_error_message( $error_code ) {

		$message = '';

		switch ( $error_code ) {
			case 'incorrect_number':
				$message = __( 'The card number is incorrect.', 'give' );
				break;
			case 'invalid_number':
				$message = __( 'The card number is not a valid credit card number.', 'give' );
				break;
			case 'invalid_expiry_month':
				$message = __( 'The card\'s expiration month is invalid.', 'give' );
				break;
			case 'invalid_expiry_year':
				$message = __( 'The card\'s expiration year is invalid.', 'give' );
				break;
			case 'invalid_cvc':
				$message = __( 'The card\'s security code is invalid.', 'give' );
				break;
			case 'expired_card':
				$message = __( 'The card has expired.', 'give' );
				break;
			case 'incorrect_cvc':
				$message = __( 'The card\'s security code is incorrect.', 'give' );
				break;
			case 'incorrect_zip':
				$message = __( 'The card\'s zip code failed validation.', 'give' );
				break;
			case 'card_declined':
				$message = __( 'The card was declined.', 'give' );
				break;
			case 'missing':
				$message = __( 'There is no card on a customer that is being charged.', 'give' );
				break;
			case 'processing_error':
				$message = __( 'An error occurred while processing the card.', 'give' );
				break;
			case 'rate_limit':
				$message = __( 'An error occurred due to requests hitting the API too quickly. Please let us know if you\'re consistently running into this error.', 'give' );
				break;
		} // End switch().

		return $message;
	}
}

new Give_Stripe_Logger();
