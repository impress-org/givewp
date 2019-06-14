<?php
/**
 * Give - Stripe Customer
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * Stripe Payment Method ID.
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @var string
	 */
	private $payment_method_id = '';

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
	 * Attached Payment Method to Customer.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var array|\Stripe\PaymentMethod
	 */
	public $attached_payment_method = array();

	/**
	 * Check for card existence for customer.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var bool
	 */
	public $is_card_exists = false;

	/**
	 * Give_Stripe_Customer constructor.
	 *
	 * @param string $email             Donor Email.
	 * @param string $payment_method_id Stripe Payment Method ID.
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public function __construct( $email, $payment_method_id = '' ) {

		$this->donor_email       = $email;
		$this->payment_method_id = $payment_method_id;
		$this->stripe_gateway    = new Give_Stripe_Gateway();

		$this->set_id( give_stripe_get_customer_id( $email ) );
		$this->get_or_create_customer();
	}

	/**
	 * Get Stripe customer ID.
	 *
	 * @since  2.5.0
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
	 * @since  2.5.0
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
	 * @since  2.5.0
	 * @access public
	 */
	public function set_customer_data( $data ) {
		$this->customer_data = $data;
	}

	/**
	 * Get the Stripe customer object. If not found, create the customer with Stripe's API.
	 * Save the customer ID appropriately in the database.
	 *
	 * @since  2.5.0
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

			// Set Application Info.
			give_stripe_set_app_info();

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
						__( 'Stripe - Customer Creation Error', 'give' ),
						$e->getMessage()
					);
				}
			} catch ( Exception $e ) {
				$customer = false;
			}
		}

		// Create the Stripe customer if not present.
		if ( ! $customer ) {
			$customer = $this->create_customer();
		}

		$this->set_id( $customer->id );
		$this->set_customer_data( $customer );

		// Attach source/payment method to customer.
		if ( give_stripe_is_checkout_enabled() || give_stripe_is_source_type( $this->payment_method_id, 'btok' ) ) {
			$this->attach_source();
		} else {
			$this->attach_payment_method();
		}

		return $customer;

	}

	/**
	 * Create a Customer in Stripe.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool|\Stripe\Customer
	 */
	public function create_customer() {

		$customer     = false;
		$post_data    = give_clean( $_POST );
		$payment_mode = ! empty( $post_data['give-gateway'] ) ? $post_data['give-gateway'] : '';
		$form_id      = ! empty( $post_data['give-form-id'] ) ? $post_data['give-form-id'] : false;
		$first_name   = ! empty( $post_data['give_first'] ) ? $post_data['give_first'] : '';
		$last_name    = ! empty( $post_data['give_last'] ) ? $post_data['give_last'] : '';
		$full_name    = ! empty( $last_name ) ? "{$first_name} {$last_name}" : $first_name;

		// Set Application Info.
		give_stripe_set_app_info();

		try {

			$metadata = array(
				'first_name' => $first_name,
				'last_name'  => $last_name,
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

			// Add company name to customer metadata.
			if ( give_is_company_field_enabled( $form_id ) ) {
				$metadata['company_name'] = ! empty( $post_data['give_company_name'] ) ? $post_data['give_company_name'] : '';
			}

			/**
			 * This filter will be used to modify customer arguments based on the need.
			 *
			 * @param array $args List of customer arguments from Stripe.
			 *
			 * @since 2.5.0
			 */
			$args = apply_filters(
				'give_stripe_customer_args',
				array(
					'description'    => sprintf(
						/* translators: %s Site URL */
						__( 'Stripe Customer generated by GiveWP via %s', 'give' ),
						get_bloginfo( 'url' )
					),
					'name'           => $full_name,
					'email'          => $this->donor_email,
					'metadata'       => apply_filters( 'give_stripe_customer_metadata', $metadata, $post_data ),
				)
			);

			// Add these parameters when payment method/source id exists.
			if ( ! empty( $this->payment_method_id ) ) {
				if ( give_stripe_is_checkout_enabled() || give_stripe_is_source_type( $this->payment_method_id, 'btok' ) ) {
					$args['source'] = $this->payment_method_id;
				} else {
					$args['payment_method'] = $this->payment_method_id;
				}
			}

			// Create a customer first so we can retrieve them later for future payments.
			$customer = \Stripe\Customer::create( $args, give_stripe_get_connected_account_options() );

		} catch ( \Stripe\Error\Base $e ) {
			// Record Log.
			give_stripe_record_log(
				__( 'Stripe - Customer Creation Error', 'give' ),
				$e->getMessage()
			);

		} catch ( Exception $e ) {
			give_record_gateway_error(
				__( 'Stripe Error', 'give' ),
				sprintf(
					/* translators: %s Exception Message Body */
					__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'give' ),
					$e->getMessage()
				)
			);
			give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give' ) );
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

		if ( ! empty( $this->payment_method_id ) && ! empty( $this->customer_data ) ) {

			$card        = '';
			$card_exists = false;
			$new_card    = '';
			$all_sources = $this->customer_data->sources->all();

			// Fetch the new card or source object to match with customer attached card fingerprint.
			if ( give_stripe_is_checkout_enabled() ) {
				$token_details = $this->stripe_gateway->get_token_details( $this->payment_method_id );
				$new_card = $token_details->card;
			} elseif ( give_stripe_is_source_type( $this->payment_method_id, 'src' ) ) {
				$source_details = $this->stripe_gateway->get_source_details( $this->payment_method_id );
				$new_card = $source_details->card;
			}

			/**
			 * This filter hook is used to get new card details.
			 *
			 * @since 2.5.0
			 */
			$new_card = apply_filters( 'give_stripe_get_new_card_details', $new_card, $this->payment_method_id, $this->stripe_gateway );

			// Check to ensure that new card is already attached with customer or not.
			if ( count( $all_sources->data ) > 0 ) {
				foreach ( $all_sources->data as $source_item ) {

					if (
						(
							isset( $source_item->card->fingerprint ) &&
							$source_item->card->fingerprint === $new_card->fingerprint
						) ||
						(
							isset( $source_item->fingerprint ) &&
							$source_item->fingerprint === $new_card->fingerprint
						)
					) {

						// Set the existing card as default source.
						$this->customer_data->default_source = $source_item->id;
						$this->customer_data->save();
						$card                 = $source_item;
						$card_exists          = true;
						$this->is_card_exists = true;
						break;
					}
				}
			}

			// Create the card, if none found above.
			if ( ! $card_exists ) {
				try {

					$card = $this->customer_data->sources->create( array(
						'source' => $this->payment_method_id,
					) );

					$this->customer_data->default_source = $card->id;
					$this->customer_data->save();

				} catch ( \Stripe\Error\Base $e ) {
					Give_Stripe_Logger::log_error( $e, 'stripe' );
				} catch ( Exception $e ) {
					give_record_gateway_error(
						__( 'Stripe Error', 'give' ),
						sprintf(
							/* translators: %s Exception Message Body */
							__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'give' ),
							$e->getMessage()
						)
					);
					give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give' ) );
					give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
					return false;
				}
			}

			// Return Card Details, if exists.
			if ( ! empty( $card->id ) ) {
				$this->attached_payment_method = $card;
			} else {
				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );
				give_record_gateway_error( __( 'Stripe Error', 'give' ), __( 'An error occurred retrieving or creating the ', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );
				$this->attached_payment_method = false;
			}
		} // End if().
	}

	/**
	 * This function is used to attach source to the customer, if not exists.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return void
	 */
	public function attach_payment_method() {

		if ( ! empty( $this->payment_method_id ) && ! empty( $this->customer_data ) ) {

			$card        = '';
			$card_exists = false;
			$all_sources = $this->customer_data->sources->all();

			// Fetch the new card or source object to match with customer attached card fingerprint.
			if ( give_stripe_is_source_type( $this->payment_method_id, 'tok' ) ) {
				$token_details = $this->stripe_gateway->get_token_details( $this->payment_method_id );
				$new_card      = $token_details->card;
			} elseif ( give_stripe_is_source_type( $this->payment_method_id, 'src' ) ) {
				$source_details = $this->stripe_gateway->get_source_details( $this->payment_method_id );
				$new_card       = $source_details->card;
			} elseif ( give_stripe_is_source_type( $this->payment_method_id, 'pm' ) ) {
				$payment_method_details = $this->stripe_gateway->payment_method->retrieve( $this->payment_method_id );
				$new_card               = $payment_method_details->card;
			}

			/**
			 * This filter hook is used to get new card details.
			 *
			 * @since 2.5.0
			 */
			$new_card = apply_filters( 'give_stripe_get_new_card_details', $new_card, $this->payment_method_id, $this->stripe_gateway );

			// Check to ensure that new card is already attached with customer or not.
			if ( count( $all_sources->data ) > 0 ) {
				foreach ( $all_sources->data as $source_item ) {

					$source_fingerprint = isset( $source_item->card ) ? $source_item->card->fingerprint : $source_item->fingerprint;

					if ( $source_fingerprint === $new_card->fingerprint ) {

						// Set the existing card as default source.
						$this->customer_data->default_source = $source_item->id;
						$this->customer_data->save();
						$card                 = $source_item;
						$card_exists          = true;
						$this->is_card_exists = true;
						break;
					}
				}
			}

			// Create the card, if none found above.
			if ( ! $card_exists ) {
				try {
					$customer_args = array();

					if ( give_stripe_is_source_type( $this->payment_method_id, 'src' ) || give_stripe_is_source_type( $this->payment_method_id, 'tok' ) ) {
						$customer_args['source'] = $this->payment_method_id;
						$card = $this->customer_data->sources->create( $customer_args );
						$this->customer_data->default_source = $card->id;
						$this->customer_data->save();
					} else {

						$card = $this->stripe_gateway->payment_method->retrieve( $this->payment_method_id );

						// If payment method is not attached to customer then attach it now.
						if ( empty( $card->customer ) ) {
							$card->attach(
								array(
									'customer' => $this->id,
								)
							);
						}
					}

				} catch ( \Stripe\Error\Base $e ) {

					Give_Stripe_Logger::log_error( $e, 'stripe' );

				} catch ( Exception $e ) {
					give_record_gateway_error(
						__( 'Stripe Error', 'give' ),
						sprintf(
							/* translators: %s Exception Message Body */
							__( 'The Stripe Gateway returned an error while creating the customer. Details: %s', 'give' ),
							$e->getMessage()
						)
					);
					give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give' ) );
					give_send_back_to_checkout( '?payment-mode=stripe' );
				}
			}

			// Return Card Details, if exists.
			if ( ! empty( $card->id ) ) {
				$this->attached_payment_method = $card;
			} else {

				give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );
				give_record_gateway_error( __( 'Stripe Error', 'give' ), __( 'An error occurred retrieving or creating the ', 'give' ) );
				give_send_back_to_checkout( '?payment-mode=stripe' );

				$this->attached_payment_method = false;
			}
		} // End if().
	}

	/**
	 * This function will check whether the error says no such customer.
	 *
	 * @param \Stripe\Error\InvalidRequest $error Invalid Request Error.
	 *
	 * @since  2.5.0
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
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_card( $id ) {
		return give_stripe_is_source_type( $id, 'card' );
	}

	/**
	 * This function will check whether the ID provided is Source ID?
	 *
	 * @param string $id Source ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_source( $id ) {
		return give_stripe_is_source_type( $id, 'src' );
	}

	/**
	 * This function will check whether the ID provided is Bank Account ID?
	 *
	 * @param string $id Source ID.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return bool
	 */
	public function is_bank_account( $id ) {
		return give_stripe_is_source_type( $id, 'ba' );
	}
}
