<?php
/**
 * Give - Stripe Core Gateway
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
 * Check for class Give_Stripe_Gateway exists.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Gateway' ) ) {

	class Give_Stripe_Gateway {

		/**
		 * Default Gateway ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Set Latest Stripe Version.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var string
		 */
		public $api_version = '2019-05-16';

		/**
		 * Secret API Key.
		 *
		 * @access private
		 *
		 * @var string
		 */
		private $secret_key = '';

		/**
		 * Payment Intent.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var \Stripe\PaymentIntent
		 */
		public $payment_intent;

		/**
		 * Stripe Payment Method Object.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var Give_Stripe_Payment_Method
		 */
		public $payment_method;

		/**
		 * Give_Stripe_Gateway constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return bool|void
		 */
		public function __construct() {

			// Set secret key received from Stripe.
			$this->secret_key = give_stripe_get_secret_key();

			// Set API Version.
			$this->set_api_version();

			// Call Payment Intent Class to utilize.
			$this->payment_intent = new Give_Stripe_Payment_Intent();
			$this->payment_method = new Give_Stripe_Payment_Method();

			add_action( "give_gateway_{$this->id}", array( $this, 'process_payment' ) );

		}

		/**
		 * This function will help to set the latest Stripe API version.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function set_api_version() {

			// Set Application Info.
			give_stripe_set_app_info();

			try {

				// Set API Version to latest.
				\Stripe\Stripe::setApiVersion( $this->api_version );

			} catch ( \Stripe\Error\Base $e ) {

				// Log Error.
				$this->log_error( $e );

			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'Unable to set Stripe API Version. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );

				// Send donor back to checkout page on error.
				$this->send_back_to_checkout();
			}
		}

		/**
		 * Send back to checkout based on the gateway id.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function send_back_to_checkout() {
			give_send_back_to_checkout( '?payment-mode=' . $this->id );
		}

		/**
		 * This function will be used to fetch token details from token id.
		 *
		 * @param string $id   Stripe Token ID.
		 * @param array  $args Additional arguments.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\Token
		 */
		public function get_token_details( $id, $args = array() ) {

			// Set Application Info.
			give_stripe_set_app_info();

			try {

				$args = wp_parse_args( $args, give_stripe_get_connected_account_options() );

				// Retrieve Token Object.
				return \Stripe\Token::retrieve( $id, $args );

			} catch ( \Stripe\Error\Base $e ) {
				$this->log_error( $e );
			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Token Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'Unable to retrieve token. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );

				// Send donor back to checkout page on error.
				$this->send_back_to_checkout();
			}
		}

		/**
		 * This function will be used to fetch source details from source id.
		 *
		 * @param string $id Stripe Source ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\Source
		 */
		public function get_source_details( $id ) {

			// Set Application Info.
			give_stripe_set_app_info();

			try {

				// Retrieve Source Object.
				return \Stripe\Source::retrieve( $id, give_stripe_get_connected_account_options() );

			} catch ( \Stripe\Error\Base $e ) {
				$this->log_error( $e );
			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Source Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'Unable to retrieve source. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );

				// Send donor back to checkout page on error.
				$this->send_back_to_checkout();
			}
		}

		/**
		 * This function will prepare source based on the parameters provided.
		 *
		 * @param array $args List of arguments \Stripe\Source::create() supports.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\Source
		 */
		public function prepare_source( $args ) {

			// Set Application Info.
			give_stripe_set_app_info();

			try {

				// Create Source Object.
				return \Stripe\Source::create( $args, give_stripe_get_connected_account_options() );

			} catch ( \Stripe\Error\Base $e ) {
				$this->log_error( $e );
			} catch ( Exception $e ) {

				// Something went wrong outside of Stripe.
				give_record_gateway_error(
					__( 'Stripe Error', 'give' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'Unable to create source. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );

				// Send donor back to checkout page on error.
				$this->send_back_to_checkout();
			}
		}

		/**
		 * Get Customer's card.
		 *
		 * @param \Stripe\Customer $stripe_customer Stripe Customer Object.
		 * @param string           $id              Source or Token ID.
		 *
		 * @since 2.5.0
		 *
		 * @return \Stripe\Source|bool
		 */
		public function get_customer_card( $stripe_customer, $id ) {

			$card_exists = false;
			$all_sources = $stripe_customer->sources->all();

			if ( give_stripe_is_checkout_enabled() && 'stripe' === $this->id ) {
				$card = $this->get_token_details( $id );
			} else {
				$card = $this->get_source_details( $id );
			}

			$source_list = wp_list_pluck( $all_sources->data, 'id' );

			// Check whether the source is already attached to customer or not.
			if ( in_array( $id, $source_list, true ) ) {
				$card_exists = true;
			}

			// Create the card if none found above.
			if ( ! $card_exists ) {
				try {

					// Attach Source to existing Customer.
					$card = $stripe_customer->sources->create( array(
						'source' => $id,
					) );

				} catch ( \Stripe\Error\Base $e ) {

					// Log Error.
					$this->log_error( $e );

				} catch ( Exception $e ) {

					give_record_gateway_error(
						__( 'Stripe Card Error', 'give' ),
						sprintf(
							/* translators: %s Exception Error Message */
							__( 'The Stripe Gateway returned an error while processing a donation. Details: %s', 'give' ),
							$e->getMessage()
						)
					);

					// Send donor back to checkout page on error.
					$this->send_back_to_checkout();
				}
			}

			// Return Card Details, if exists.
			if ( ! empty( $card->id ) ) {
				return $card;
			} else {

				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );
				give_record_gateway_error( __( 'Stripe Error', 'give' ), __( 'An error occurred retrieving or creating the ', 'give' ) );

				// Send donor back to checkout page on error.
				$this->send_back_to_checkout();

				return false;
			}
		}

		/**
		 * Save Stripe Customer ID.
		 *
		 * @param string $stripe_customer_id Customer ID.
		 * @param int    $payment_id         Payment ID.
		 *
		 * @since 2.5.0
		 */
		public function save_stripe_customer_id( $stripe_customer_id, $payment_id ) {

			// Update customer meta.
			if ( class_exists( 'Give_DB_Donor_Meta' ) ) {

				$donor_id = give_get_payment_donor_id( $payment_id );

				// Get the Give donor.
				$donor = new Give_Donor( $donor_id );

				// Update donor meta.
				$donor->update_meta( give_stripe_get_customer_key(), $stripe_customer_id );

			} elseif ( is_user_logged_in() ) {

				// Support saving to legacy method of user method.
				update_user_meta( get_current_user_id(), give_stripe_get_customer_key(), $stripe_customer_id );

			}

		}

		/**
		 * Log a Stripe Error.
		 *
		 * Logs in the Give db the error and also displays the error message to the donor.
		 *
		 * @param \Stripe\Error\Base|\Stripe\Error\Card $exception    Exception.
		 *
		 * @since 2.5.0
		 *
		 * @return bool
		 */
		public function log_error( $exception ) {

			$log_message = __( 'The Stripe payment gateway returned an error while processing the donation.', 'give' ) . '<br><br>';
			$exception_message = $exception->getMessage();

			// Bad Request of some sort.
			if ( ! empty( $exception_message ) ) {
				$log_message .= sprintf(
					/* translators: %s Exception Message */
					__( 'Message: %s', 'give' ),
					$exception_message
				) . '<br><br>';

				$trace_string = $exception->getTraceAsString();

				if ( ! empty( $trace_string ) ) {
					$log_message .= sprintf(
						/* translators: %s Trace String */
						__( 'Code: %s', 'give' ),
						$trace_string
					);
				}

				give_set_error( 'stripe_request_error', $exception_message );
			} else {
				give_set_error( 'stripe_request_error', __( 'The Stripe API request was invalid, please try again.', 'give' ) );
			}

			// Log it with DB.
			give_record_gateway_error( __( 'Stripe Error', 'give' ), $log_message );

			// Send donor back to checkout page on error.
			$this->send_back_to_checkout();

			return false;

		}

		/**
		 * Format currency for Stripe.
		 *
		 * @see https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
		 *
		 * @param float $amount Donation amount.
		 *
		 * @return mixed
		 */
		public function format_amount( $amount ) {

			// Get the donation amount.
			if ( give_stripe_is_zero_decimal_currency() ) {
				return $amount;
			} else {
				return $amount * 100;
			}
		}

		/**
		 * Verify Payment.
		 *
		 * @param int            $payment_id         Payment ID.
		 * @param string         $stripe_customer_id Customer ID.
		 * @param \Stripe\Charge $charge             Stripe Charge Object.
		 */
		public function verify_payment( $payment_id, $stripe_customer_id, $charge ) {

			// Sanity checks: verify all vars exist.
			if ( $payment_id && ( ! empty( $stripe_customer_id ) || ! empty( $charge ) ) ) {

				/**
				 * This action hook is used to perform some additional steps to verify the payment.
				 *
				 * @param int            $payment_id         Payment ID.
				 * @param string         $stripe_customer_id Customer ID.
				 * @param \Stripe\Charge $charge             Stripe Charge Object.
				 *
				 * @since 2.5.0
				 */
				do_action( 'give_stripe_verify_payment', $payment_id, $stripe_customer_id, $charge );

				// @TODO use Stripe's API here to retrieve the invoice then confirm it has been paid.
				// Regular payment, publish it.
				give_update_payment_status( $payment_id, 'publish' );

				// Save Stripe customer id.
				$this->save_stripe_customer_id( $stripe_customer_id, $payment_id );

				// Send them to success page.
				give_send_to_success_page();

			} else {

				give_set_error( 'payment_not_recorded', __( 'Your donation could not be recorded, please contact the site administrator.', 'give' ) );

				// If errors are present, send the user back to the purchase page so they can be corrected.
				$this->send_back_to_checkout();

			} // End if().
		}

		/**
		 * This function will prepare metadata to send to Stripe.
		 *
		 * @param int $donation_id Donation ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return array
		 */
		public function prepare_metadata( $donation_id = 0 ) {
			return give_stripe_prepare_metadata( $donation_id );
		}

		/**
		 * This function will help to charge with Stripe.
		 *
		 * @param int   $donation_id Donation ID with pending status.
		 * @param array $charge_args List of charge arguments.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\Charge
		 */
		public function create_charge( $donation_id, $charge_args ) {

			// Set App Info to Stripe.
			give_stripe_set_app_info();

			try {

				$charge_args = apply_filters( "give_{$this->id}_create_charge_args", $charge_args );

				// Charge application fee, only if the Stripe premium add-on is not active.
				if ( ! defined( 'GIVE_STRIPE_VERSION' ) ) {
					// Set Application Fee Amount.
					$charge_args['application_fee_amount'] = give_stripe_get_application_fee_amount( $charge_args['amount'] );
				}

				$charge = \Stripe\Charge::create(
					$charge_args,
					give_stripe_get_connected_account_options()
				);

				// Add note for the charge.
				// Save Stripe's charge ID to the transaction.
				if ( ! empty( $charge ) ) {
					give_insert_payment_note( $donation_id, 'Stripe Charge ID: ' . $charge->id );
					give_set_payment_transaction_id( $donation_id, $charge->id );
				}

				return $charge;

			} catch ( \Stripe\Error\Base $e ) {
				Give_Stripe_Logger::log_error( $e, $this->id );
			} catch ( Exception $e ) {

				give_record_gateway_error(
					__( 'Stripe Charge Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to create a successful charge. Details: %s', 'give' ),
						$e
					)
				);
				give_set_error( 'stripe_charge_error', __( 'Error processing donation with Stripe. Please try again.', 'give' ) );
				return false;
			} // End try().
		}

		/**
		 * Create Source for Stripe 3D Secure Payments.
		 *
		 * @param int $donation_id Donation ID.
		 * @param int $source_id   Source ID/Object.
		 *
		 * @since  1.6
		 * @access public
	 	 *
		 * @return bool|\Stripe\Source
		 */
		public function create_3d_secure_source( $donation_id, $source_id ) {
			$form_id         = give_get_payment_form_id( $donation_id );
			$customer_id     = give_get_payment_meta( $donation_id, '_give_stripe_customer_id', true );
			$donation_amount = give_donation_amount( $donation_id );

			// Prepare basic source args.
			$source_args = array(
				'amount'               => $this->format_amount( $donation_amount ),
				'currency'             => give_get_currency( $form_id ),
				'type'                 => 'three_d_secure',
				'three_d_secure'       => array(
					'card'     => $source_id,
				),
				'statement_descriptor' => give_stripe_get_statement_descriptor(),
				'redirect'             => array(
					'return_url' => add_query_arg(
						array(
							'give-listener' => 'stripe_three_d_secure',
							'donation_id'   => $donation_id,
						),
						give_get_success_page_uri()
					),
				),
			);

			$source = $this->prepare_source( $source_args );

			// Add donation note for 3D secure source ID.
			if ( ! empty( $source->id ) ) {
				give_insert_payment_note( $donation_id, 'Stripe 3D Secure Source ID: ' . $source->id );
			}

			// Save 3D secure source id to donation.
			give_update_payment_meta( $donation_id, '_give_stripe_3dsecure_source_id', $source->id );

			return $source;
		}

		/**
		 * Is 3D secure payment required?
		 *
		 * @param \Stripe\Source $source_object Stripe Source Object.
		 *
		 * @since  1.6
		 * @access public
		 *
	 	 * @return bool
		 */
		public function is_3d_secure_required( $source_object ) {

			$is_3d_secure_enabled = give_is_setting_enabled( give_get_option( 'stripe_enable_three_d_secure_payments', '' ) );

			if ( $is_3d_secure_enabled ) {
				return apply_filters(
					'give_stripe_3d_secure_required',
					(
						! empty( $source_object->type ) &&
						'card' === $source_object->type &&
						'required' === $source_object->card->three_d_secure
					),
					$source_object
				);
			}

			return false;
		}

		/**
		 * Process One Time Charge.
		 *
		 * @param array  $donation_data      List of donation data.
		 * @param string $stripe_customer_id Customer ID.
		 *
		 * @return bool|\Stripe\Charge
		 */
		public function process_charge( $donation_data, $stripe_customer_id ) {

			$form_id     = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
			$donation_id = ! empty( $donation_data['donation_id'] ) ? intval( $donation_data['donation_id'] ) : 0;
			$source_id   = ! empty( $donation_data['source_id'] ) ? $donation_data['source_id'] : 0;
			$description = ! empty( $donation_data['description'] ) ? $donation_data['description'] : false;

			// Process the charge.
			$amount = $this->format_amount( $donation_data['price'] );

			$charge_args = array(
				'amount'               => $amount,
				'currency'             => give_get_currency( $form_id ),
				'customer'             => $stripe_customer_id,
				'description'          => html_entity_decode( $description, ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => give_stripe_get_statement_descriptor( $donation_data ),
				'metadata'             => $this->prepare_metadata( $donation_id ),
				'source'               => $source_id,
			);

			// Create charge with general gateway fn.
			$charge = $this->create_charge( $donation_id, $charge_args );

			// Return charge if set.
			if ( isset( $charge ) ) {
				return $charge;
			} else {
				return false;
			}
		}
	}
}
