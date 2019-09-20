<?php
/**
 * Give - Stripe Core Payment Intent
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
 * Check for class Give_Stripe_Payment_Intent exists.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Payment_Intent' ) ) {

	class Give_Stripe_Payment_Intent {

		public function __construct() {

		}

		/**
		 * This function is used to create payment intent in Stripe.
		 *
		 * @param array $args List of parameters required to create payment intent.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\PaymentIntent
		 */
		public function create( $args ) {

			// Add application fee, if the Stripe premium add-on is not active.
			if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
				$args['application_fee_amount'] = give_stripe_get_application_fee_amount( $args['amount'] );
			}

			// Set Stripe Application Info.
			give_stripe_set_app_info();

			try {
				return \Stripe\PaymentIntent::create(
					$args,
					give_stripe_get_connected_account_options()
				);
			} catch ( Exception $e ) {

				give_record_gateway_error(
					__( 'Stripe Payment Intent Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to create a payment intent. Details: %s', 'give' ),
						$e->getMessage()
					)
				);

				give_set_error( 'stripe_payment_intent_error', __( 'Error creating payment intent with Stripe. Please try again.', 'give' ) );
			} // End try().
		}

		/**
		 * This function is used to retrieve payment intent in Stripe.
		 *
		 * @param string $client_secret Client Secret represents unique string for the payment intent.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\PaymentIntent
		 */
		public function retrieve( $client_secret ) {

			// Set Application Info.
			give_stripe_set_app_info();

			try {
				return \Stripe\PaymentIntent::retrieve(
					$client_secret,
					give_stripe_get_connected_account_options()
				);
			} catch ( Exception $e ) {

				give_record_gateway_error(
					__( 'Stripe Payment Intent Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to retrieve a payment intent. Details: %s', 'give' ),
						$e
					)
				);

				give_set_error( 'stripe_payment_intent_error', __( 'Error retrieving payment intent with Stripe. Please try again.', 'give' ) );
			} // End try().
		}

		/**
		 * This function is used to update existing payment intent in Stripe.
		 *
		 * @param string $client_secret Client Secret represents unique string for the payment intent.
		 * @param array  $args          List of parameters required to create payment intent.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\PaymentIntent
		 */
		public function update( $client_secret, $args ) {

			// Add application fee, if the Stripe premium add-on is not active.
			if ( ! defined( GIVE_STRIPE_VERSION ) ) {
				$args['application_fee_amount'] = give_stripe_format_amount( give_stripe_get_application_fee_amount( $args['amount'] ) );
			}

			// Set Stripe Application Info.
			give_stripe_set_app_info();

			try {
				return \Stripe\PaymentIntent::update(
					$client_secret,
					$args,
					give_stripe_get_connected_account_options()
				);
			} catch ( Exception $e ) {

				give_record_gateway_error(
					__( 'Stripe Payment Intent Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to update a payment intent. Details: %s', 'give' ),
						$e->getMessage()
					)
				);

				give_set_error( 'stripe_payment_intent_error', __( 'Error updating payment intent with Stripe. Please try again.', 'give' ) );
			} // End try().
		}
	}
}
