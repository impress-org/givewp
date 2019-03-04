<?php
/**
 * Give - Stripe Core Customer
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

if ( ! class_exists( 'Give_Stripe_Customer' ) ) {
	/**
	 * Class Give_Stripe_Customer.
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Customer {

		/**
		 * Stripe Customer ID.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var string
		 */
		private $id = '';

		/**
		 * Stripe Source ID.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var string
		 */
		private $source_id = '';

		/**
		 * Donor Email.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var string
		 */
		private $donor_email = '';

		/**
		 * Stripe Customer Data.
		 *
		 * @since  2.5.0
		 * @access private
		 *
		 * @var \Stripe\Customer
		 */
		public $customer_data = array();

		/**
		 * Stripe Gateway Object.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var array|Give_Stripe_Gateway
		 */
		public $stripe_gateway = array();

		/**
		 * Attached Stripe Source to Customer.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var array|\Stripe\Source
		 */
		public $attached_source = array();

		/**
		 * Give_Stripe_Customer constructor.
		 *
		 * @param string $email     Donor Email.
		 * @param string $source_id Stripe Source ID.
		 *
		 * @since  2.1
		 * @access public
		 */
		public function __construct( $email, $source_id = '' ) {
			$this->donor_email    = $email;
			$this->source_id      = $source_id;
			$this->stripe_gateway = new Give_Stripe_Gateway();
			$this->set_id( give_stripe_get_customer_id( $email ) );
			$this->get_or_create_customer();
		}

		/**
		 * Get Stripe customer ID.
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @return string
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Set Stripe customer ID.
		 *
		 * @param string $id Stripe Customer ID.
		 *
		 * @since  2.1
		 * @access public
		 */
		public function set_id( $id ) {
			$this->id = give_clean( $id );
		}

		/**
		 * Store data from the Stripe API about this customer
		 *
		 * @param /Stripe/Customer $data Stripe Customer Object.
		 *
		 * @since  2.1
		 * @access public
		 */
		public function set_customer_data( $data ) {
			$this->customer_data = $data;
		}

		/**
		 * Get the Stripe customer object. If not found, create the customer with Stripe's API.
		 * Save the customer ID appropriately in the database.
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @return bool|\Stripe\Customer
		 */
		public function get_or_create_customer() {

			$customer = false;

			// No customer ID found, look up based on the email.
			$stripe_customer_id = give_stripe_get_customer_id( $this->donor_email );

			// There is a customer ID. Check if it is active still in Stripe.
			if ( ! empty( $stripe_customer_id ) ) {

				try {

					// Retrieve the customer to ensure the customer has not been deleted.
					$customer = \Stripe\Customer::retrieve( $stripe_customer_id, give_stripe_get_connected_account_options() );

					if ( isset( $customer->deleted ) && $customer->deleted ) {

						// This customer was deleted.
						$customer = false;
					}
				} catch ( \Stripe\Error\InvalidRequest $e ) {

					$error_object = $e->getJsonBody();

					if ( $this->is_no_such_customer_error( $error_object['error'] ) ) {
						$customer = $this->create_customer();
					} else {

						// Record Log.
						give_stripe_record_log(
							__( 'Stripe - Customer Creation Error', 'give-stripe' ),
							$e->getMessage()
						);
					}
				} catch ( Exception $e ) {
					$customer = false;
				}
			}

			// Create the Stripe customer if not present.
			if ( empty( $customer ) ) {
				$customer = $this->create_customer();
			}

			$this->set_id( $customer->id );
			$this->set_customer_data( $customer );

			// Attach source to customer.
			$this->attach_source();

			return $customer;

		}

		/**
		 * Create a Customer in Stripe.
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @return bool|\Stripe\Customer
		 */
		public function create_customer() {

			$customer     = false;
			$post_data    = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
			$payment_mode = ! empty( $post_data['give-gateway'] ) ? $post_data['give-gateway'] : '';

			try {

				$metadata = array(
					'first_name' => $post_data['give_first'],
					'last_name'  => $post_data['give_last'],
					'created_by' => $post_data['give-form-title'],
				);

				// Add address to customer metadata if present.
				if ( ! empty( $post_data['billing_country'] ) ) {
					$metadata['address_line1']   = isset( $post_data['card_address'] ) ? $post_data['card_address'] : '';
					$metadata['address_line2']   = isset( $post_data['card_address_2'] ) ? $post_data['card_address_2'] : '';
					$metadata['address_city']    = isset( $post_data['card_city'] ) ? $post_data['card_city'] : '';
					$metadata['address_state']   = isset( $post_data['card_state'] ) ? $post_data['card_state'] : '';
					$metadata['address_country'] = isset( $post_data['billing_country'] ) ? $post_data['billing_country'] : '';
					$metadata['address_zip']     = isset( $post_data['card_zip'] ) ? $post_data['card_zip'] : '';
				}

				/**
				 * This filter will be used to modify customer arguments based on the need.
				 *
				 * @param array $args List of customer arguments from Stripe.
				 *
				 * @since 2.1.2
				 */
				$args = apply_filters( 'give_stripe_customer_args', array(
					'description' => sprintf(
						/* translators: %s Site URL */
						__( 'Stripe Customer generated by GiveWP via %s', 'give-stripe' ),
						get_bloginfo( 'url' )
					),
					'email'       => $this->donor_email,
					'metadata'    => apply_filters( 'give_stripe_customer_metadata', $metadata, $post_data ),
				) );

				// Create a customer first so we can retrieve them later for future payments.
				$customer = \Stripe\Customer::create( $args, give_stripe_get_connected_account_options() );

			} catch ( \Stripe\Error\Base $e ) {
				// Record Log.
				give_stripe_record_log(
					__( 'Stripe - Customer Creation Error', 'give-stripe' ),
					$e->getMessage()
				);

			} catch ( Exception $e ) {
				give_record_gateway_error(
					__( 'Stripe Error', 'give-stripe' ),
					sprintf(
						/* translators: %s Exception Message Body */
						__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'give-stripe' ),
						$e->getMessage()
					)
				);
				give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give-stripe' ) );
				give_send_back_to_checkout( "?payment-mode={$payment_mode}&form_id={$post_data['post_data']['give-form-id']}" );
			} // End try().

			if ( ! empty( $customer->id ) ) {
				// Return obj.
				return $customer;
			} else {
				return false;
			}

		}

		/**
		 * This function is used to attach source to the customer, if not exists.
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @return void
		 */
		public function attach_source() {

			if ( ! empty( $this->source_id ) && ! empty( $this->customer_data ) ) {

				$card        = '';
				$card_exists = false;
				$all_sources = $this->customer_data->sources->all();

				// Fetch the new card or source object to match with customer attached card fingerprint.
				if ( give_stripe_is_checkout_enabled() ) {
					$token_details = $this->stripe_gateway->get_token_details( $this->source_id );
					$new_card = $token_details->card;
				} elseif( 'stripe_ach' === give_clean( $_POST['give-gateway'] ) ) {
					$token_details = $this->stripe_gateway->get_token_details( $this->source_id );
					$new_card = $token_details->bank_account;
				} else {
					$source_details = $this->stripe_gateway->get_source_details( $this->source_id );
					$new_card = $source_details->card;
				}

				// Check to ensure that new card is already attached with customer or not.
				if ( count( $all_sources->data ) > 0 ) {
					foreach ( $all_sources->data as $source_item ) {

						if (
							( $this->is_card( $source_item->id ) && $source_item->fingerprint === $new_card->fingerprint ) ||
							(
								$source_item->card->fingerprint === $new_card->fingerprint &&
								( $this->is_source( $source_item->id ) || $this->is_bank_account( $source_item->id ))
							)
						) {

							// Set the existing card as default source.
							$this->customer_data->default_source = $source_item->id;
							$this->customer_data->save();
							$card        = $source_item;
							$card_exists = true;
							break;
						}
					}
				}

				// Create the card, if none found above.
				if ( ! $card_exists ) {
					try {

						$card = $this->customer_data->sources->create( array(
							'source' => $this->source_id,
						) );

						$this->customer_data->default_source = $card->id;
						$this->customer_data->save();

					} catch ( \Stripe\Error\Base $e ) {

						Give_Stripe_Logger::log_error( $e, 'stripe' );

					} catch ( Exception $e ) {
						give_record_gateway_error(
							__( 'Stripe Error', 'give-stripe' ),
							sprintf(
								/* translators: %s Exception Message Body */
								__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'give-stripe' ),
								$e->getMessage()
							)
						);
						give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give-stripe' ) );
						give_send_back_to_checkout( '?payment-mode=stripe' );
					}
				}

				// Return Card Details, if exists.
				if ( ! empty( $card->id ) ) {
					$this->attached_source = $card;
				} else {

					give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give-stripe' ) );
					give_record_gateway_error( __( 'Stripe Error', 'give-stripe' ), __( 'An error occurred retrieving or creating the ', 'give-stripe' ) );
					give_send_back_to_checkout( '?payment-mode=stripe' );

					$this->attached_source = false;
				}
			} // End if().
		}

		/**
		 * This function will check whether the error says no such customer.
		 *
		 * @param \Stripe\Error\InvalidRequest $error Invalid Request Error.
		 *
		 * @since  2.1
		 * @access public
		 *
		 * @return bool
		 */
		public function is_no_such_customer_error( $error ) {
			return (
				$error &&
				'invalid_request_error' === $error['type'] &&
				preg_match( '/No such customer/i', $error['message'] )
			);
		}

		/**
		 * This function will check whether the ID provided is Card ID?
		 *
		 * @param string $id Card ID.
		 *
		 * @since  2.1.2
		 * @access public
		 *
		 * @return bool
		 */
		public function is_card( $id ) {
			return (
				$id &&
				preg_match( '/card_/i', $id )
			);
		}

		/**
		 * This function will check whether the ID provided is Source ID?
		 *
		 * @param string $id Source ID.
		 *
		 * @since  2.1.2
		 * @access public
		 *
		 * @return bool
		 */
		public function is_source( $id ) {
			return (
				$id &&
				preg_match( '/src_/i', $id )
			);
		}

		/**
		 * This function will check whether the ID provided is Bank Account ID?
		 *
		 * @param string $id Source ID.
		 *
		 * @since  2.1.2
		 * @access public
		 *
		 * @return bool
		 */
		public function is_bank_account( $id ) {
			return (
				$id &&
				preg_match( '/ba_/i', $id )
			);
		}
	}
}
