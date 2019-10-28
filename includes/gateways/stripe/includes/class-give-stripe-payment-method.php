<?php
/**
 * Give - Stripe Core | Payment Method API
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check for class Give_Stripe_Payment_Method exists.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Payment_Method' ) ) {

	class Give_Stripe_Payment_Method {

		/**
		 * Create Payment Method.
		 *
		 * @param array $args Payment Method Arguments.
		 *
		 * @since 2.5.0
		 *
		 * @return \Stripe\PaymentMethod
		 */
		public function create( $args ) {

			try {

				give_stripe_set_app_info();

				$payment_method = \Stripe\PaymentMethod::create( $args, give_stripe_get_connected_account_options() );

			} catch( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Payment Method Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while creating the payment method. Details: %s', 'give' ),
						$e
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while creating the payment method. Please try again.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode']) );
				return false;
			}

			return $payment_method;
		}

		/**
		 * Retrieves the payment method.
		 *
		 * @param string $id Payment Intent ID.
		 *
		 * @return \Stripe\PaymentMethod
		 */
		public function retrieve( $id ) {

			try {

				give_stripe_set_app_info();

				$payment_method_details = \Stripe\PaymentMethod::retrieve( $id, give_stripe_get_connected_account_options() );

			} catch( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Payment Method Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while retrieving the payment method of the customer. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while retrieving the payment method of the customer. Please try again.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode']) );
				return false;
			}

			return $payment_method_details;
		}

		/**
		 * This function is used to update existing payment method.
		 *
		 * @param string $id   Payment Method ID of Stripe.
		 * @param array  $args List of arguments to update.
		 *
		 * @since 2.5.10
		 *
		 * @return bool|\Stripe\PaymentMethod
		 */
		public function update( $id, $args ) {

			give_stripe_set_app_info();

			$payment_method = false;

			try {
				$payment_method = \Stripe\PaymentMethod::update( $id, $args, give_stripe_get_connected_account_options() );
			} catch( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Payment Method Error', 'give' ),
					sprintf(
					/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while updating the payment method of the customer. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while retrieving the payment method of the customer. Please try again.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode']) );
			}

			return $payment_method;
		}

		/**
		 * Fetch all payment methods of the customer.
		 *
		 * @param string $customer_id Stripe Customer ID.
		 * @param string $type        Stripe Payment Type.
		 *
		 * @since 2.5.0
		 *
		 * @return \Stripe\PaymentMethod
		 */
		public function list_all( $customer_id, $type = 'card' ) {

			try {

				give_stripe_set_app_info();

				$all_payment_methods = \Stripe\PaymentMethod::all(
					array(
						'customer' => $customer_id,
						'type'     => $type,
						'limit'    => 100,
					),
					give_stripe_get_connected_account_options()
				);

			} catch( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Payment Method Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while fetching the list of payment methods of the customer. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while fetching the list of payment methods of the customer. Please try again.', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode']) );
				return false;
			}

			return $all_payment_methods;
		}
	}
}
