<?php
/**
 * Give - Stripe Card Payments
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
 * Check for Give_Stripe_Card existence.
 *
 * @since 2.5.0
 */
if ( ! class_exists( 'Give_Stripe_Card' ) ) {

	/**
	 * Class Give_Stripe_Card.
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Card extends Give_Stripe_Gateway {

		/**
		 * Override Payment Method ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var string
		 */
		public $id = 'stripe';

		/**
		 * Give_Stripe_Card constructor.
		 *
		 * @since  2.5.0
		 * @access public
		 */
		public function __construct() {

			parent::__construct();

			add_action( 'init', array( $this, 'stripe_event_listener' ) );
		}

		/**
		 * Check for the Stripe Source.
		 *
		 * @param array $donation_data List of Donation Data.
		 *
		 * @since 2.0.6
		 *
		 * @return string
		 */
		public function check_for_source( $donation_data ) {

			$source_id          = $donation_data['post_data']['give_stripe_source'];
			$stripe_js_fallback = give_get_option( 'stripe_js_fallback' );

			if ( ! isset( $source_id ) ) {

				// check for fallback mode.
				if ( ! empty( $stripe_js_fallback ) ) {

					$card_data = $this->prepare_card_data( $donation_data );

					try {

						$source = \Stripe\Source::create( array(
							'card' => $card_data,
						) );
						$source_id = $source->id;

					} catch ( \Stripe\Error\Base $e ) {
						$this->log_error( $e );

					} catch ( Exception $e ) {

						give_record_gateway_error(
							__( 'Stripe Error', 'give-stripe' ),
							sprintf(
								/* translators: %s Exception Message Body */
								__( 'The Stripe Gateway returned an error while creating the customer payment source. Details: %s', 'give-stripe' ),
								$e->getMessage()
							)
						);
						give_set_error( 'stripe_error', __( 'An occurred while processing the donation with the gateway. Please try your donation again.', 'give-stripe' ) );
						give_send_back_to_checkout( "?payment-mode={$this->id}&form_id={$donation_data['post_data']['give-form-id']}" );
					}
				} elseif ( ! $this->is_stripe_popup_enabled() ) {

					// No Stripe source and fallback mode is disabled.
					give_set_error( 'no_token', __( 'Missing Stripe Source. Please contact support.', 'give-stripe' ) );
					give_record_gateway_error( __( 'Missing Stripe Source', 'give-stripe' ), __( 'A Stripe token failed to be generated. Please check Stripe logs for more information.', 'give-stripe' ) );

				}
			} // End if().

			return $source_id;

		}

		/**
		 * Process the POST Data for the Credit Card Form, if a source was not supplied.
		 *
		 * @since 2.5.0
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @return array The credit card data from the $_POST
		 */
		public function prepare_card_data( $donation_data ) {

			$card_data = array(
				'number'          => $donation_data['card_info']['card_number'],
				'name'            => $donation_data['card_info']['card_name'],
				'exp_month'       => $donation_data['card_info']['card_exp_month'],
				'exp_year'        => $donation_data['card_info']['card_exp_year'],
				'cvc'             => $donation_data['card_info']['card_cvc'],
				'address_line1'   => $donation_data['card_info']['card_address'],
				'address_line2'   => $donation_data['card_info']['card_address_2'],
				'address_city'    => $donation_data['card_info']['card_city'],
				'address_zip'     => $donation_data['card_info']['card_zip'],
				'address_state'   => $donation_data['card_info']['card_state'],
				'address_country' => $donation_data['card_info']['card_country'],
			);

			return $card_data;
		}

		/**
		 * This function will be used for donation processing.
		 *
		 * @param array $donation_data List of donation data.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment( $donation_data ) {

			// Bailout, if the current gateway and the posted gateway mismatched.
			if ( $this->id !== $donation_data['post_data']['give-gateway'] ) {
				return;
			}

			// Make sure we don't have any left over errors present.
			give_clear_errors();

			$source_id = ! empty( $donation_data['post_data']['give_stripe_source'] )
				? $donation_data['post_data']['give_stripe_source']
				: $this->check_for_source( $donation_data );

			// Any errors?
			$errors = give_get_errors();

			// No errors, proceed.
			if ( ! $errors ) {

				$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
				$price_id         = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
				$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;
				$intent_id        = ! empty( $donation_data['post_data']['give_stripe_intent_id'] ) ? $donation_data['post_data']['give_stripe_intent_id'] : 0;
				$donation_summary = give_payment_gateway_donation_summary( $donation_data, false );

				// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
				$give_stripe_customer = new Give_Stripe_Customer( $donor_email, $source_id );
				$stripe_customer      = $give_stripe_customer->customer_data;
				$stripe_customer_id   = $give_stripe_customer->get_id();

				// We have a Stripe customer, charge them.
				if ( $stripe_customer_id ) {

					// Proceed to get stripe source details on if stripe checkout is not enabled.
					$source    = $give_stripe_customer->attached_source;
					$source_id = $source->id;

					// Setup the payment details.
					$payment_data = array(
						'price'           => $donation_data['price'],
						'give_form_title' => $donation_data['post_data']['give-form-title'],
						'give_form_id'    => $form_id,
						'give_price_id'   => $price_id,
						'date'            => $donation_data['date'],
						'user_email'      => $donation_data['user_email'],
						'purchase_key'    => $donation_data['purchase_key'],
						'currency'        => give_get_currency( $form_id ),
						'user_info'       => $donation_data['user_info'],
						'status'          => 'pending',
						'gateway'         => $this->id,
					);

					// Record the pending payment in Give.
					$donation_id = give_insert_payment( $payment_data );

					// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
					give_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
					$this->save_stripe_customer_id( $stripe_customer_id, $donation_id );
					give_update_meta( $donation_id, '_give_stripe_customer_id', $stripe_customer_id );

					// Save Source ID to donation note and DB.
					give_insert_payment_note( $donation_id, 'Stripe Source ID: ' . $source_id );
					give_update_meta( $donation_id, '_give_stripe_source_id', $source_id );

					// Save donation summary to donation.
					give_update_meta( $donation_id, '_give_stripe_donation_summary', $donation_summary );

					$intent_args = array(
						'amount'               => $this->format_amount( $donation_data['price'] ),
						'currency'             => give_get_currency( $form_id ),
						'payment_method_types' => [ 'card' ],
						'statement_descriptor' => give_stripe_get_statement_descriptor(),
						'receipt_email'        => $donation_data['user_email'],
						'description'          => give_payment_gateway_donation_summary( $donation_data ),
						'metadata'             => $this->prepare_metadata( $donation_id ),
						'customer'             => $stripe_customer_id,
						'source'               => $source_id,
						'save_payment_method'  => true,
						'confirm'              => true,
						'return_url'           => give_get_success_page_uri(),
					);
					$intent      = $this->payment_intent->create( $intent_args );

					// Save Payment Intent ID to donation note and DB.
					give_insert_payment_note( $donation_id, 'Stripe Payment Intent ID: ' . $intent->id );
					give_update_meta( $donation_id, '_give_stripe_payment_intent_id', $intent->id );

					// Save Payment Intent Client Secret to donation note and DB.
					give_insert_payment_note( $donation_id, 'Stripe Payment Intent Client Secret: ' . $intent->client_secret );
					give_update_meta( $donation_id, '_give_stripe_payment_intent_client_secret', $intent->client_secret );

					// Additional steps required when payment intent status is set to `requires_action`.
					if ( 'requires_action' === $intent->status ) {

						$action_url = $intent->next_action->redirect_to_url->url;

						// Save Payment Intent requires action related information to donation note and DB.
						give_insert_payment_note( $donation_id, 'Stripe requires additional action to be fulfilled.' );
						give_update_meta( $donation_id, '_give_stripe_payment_intent_require_action_url', $action_url );

						wp_redirect( $action_url );
						exit;
					}

					// Send them to success page.
					give_send_to_success_page();

				} else {

					// No customer, failed.
					give_record_gateway_error(
						__( 'Stripe Customer Creation Failed', 'give' ),
						sprintf(
							/* translators: %s Donation Data */
							__( 'Customer creation failed while processing the donation. Details: %s', 'give' ),
							wp_json_encode( $donation_data )
						)
					);
					give_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while processing the donation.', 'give' ) );
					give_send_back_to_checkout( "?payment-mode={$this->id}" );

				} // End if().
			} else {
				give_send_back_to_checkout( "?payment-mode={$this->id}" );
			} // End if().
		}

		/**
		 * Process One Time Charge.
		 *
		 * @param array  $donation_data      List of donation data.
		 * @param string $stripe_customer_id Customer ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return bool|\Stripe\Charge
		 */
		public function process_charge( $donation_data, $stripe_customer_id ) {

			$form_id     = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
			$donation_id = ! empty( $donation_data['donation_id'] ) ? intval( $donation_data['donation_id'] ) : 0;

			// Process the charge.
			$amount = $this->format_amount( $donation_data['price'] );

			$charge_args = array(
				'amount'               => $amount,
				'currency'             => give_get_currency( $form_id ),
				'customer'             => $stripe_customer_id,
				'description'          => html_entity_decode( $donation_data['description'], ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => give_stripe_get_statement_descriptor( $donation_data ),
				'metadata'             => $this->prepare_metadata( $donation_id ),
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

		/**
		 * Listen for Stripe events.
		 *
		 * @access public
		 * @since  2.5.0
		 *
		 * @return void
		 */
		public function stripe_event_listener() {

			// Must be a stripe listener to proceed.
			if ( ! isset( $_GET['give-listener'] ) || $this->id !== $_GET['give-listener'] ) {
				return;
			}

			// Get the Stripe SDK autoloader.
			require_once GIVE_PLUGIN_DIR . 'vendor/autoload.php';

			$this->set_api_key();
			$this->set_api_version();

			// Retrieve the request's body and parse it as JSON.
			$body       = @file_get_contents( 'php://input' );
			$event_json = json_decode( $body );

			$this->process_webhooks( $event_json );

		}

		/**
		 * Process Stripe Webhooks.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @param object $event_json Stripe Webhook JSON.
		 */
		public function process_webhooks( $event_json ) {

			// Next, proceed with additional webhooks.
			if ( isset( $event_json->id ) ) {

				status_header( 200 );

				try {

					$event = \Stripe\Event::retrieve( $event_json->id );

				} catch ( \Stripe\Error\Authentication $e ) {

					if ( strpos( $e->getMessage(), 'Platform access may have been revoked' ) !== false ) {
						give_stripe_connect_delete_options();
					}
				} catch ( Exception $e ) {

					die( 'Invalid event ID' );

				}

				switch ( $event->type ) {

					case 'payment_intent.succeeded':
						$intent = $event->data->object;

						if ( 'succeeded' === $intent->status ) {
							$donation_id = give_stripe_get_donation_id_by( $intent->id, 'intent_id' );

							// Update payment status to donation.
							give_update_payment_status( $donation_id, 'publish' );

							// Insert donation note to inform admin that charge succeeded.
							give_insert_payment_note( $donation_id, __( 'Charge succeeded in Stripe.', 'give' ) );
						}

						break;

					case 'payment_intent.payment_failed':
							$intent      = $event->data->object;
							$donation_id = give_stripe_get_donation_id_by( $intent->id, 'intent_id' );

							// Update payment status to donation.
							give_update_payment_status( $donation_id, 'failed' );

							// Insert donation note to inform admin that charge succeeded.
							give_insert_payment_note( $donation_id, __( 'Charge failed in Stripe.', 'give' ) );

						break;

					case 'charge.refunded':
						global $wpdb;

						$charge = $event->data->object;

						if ( $charge->refunded ) {

							$payment_id = $wpdb->get_var( $wpdb->prepare( "SELECT donation_id FROM {$wpdb->donationmeta} WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $charge->id ) );

							if ( $payment_id ) {

								give_update_payment_status( $payment_id, 'refunded' );
								give_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe.', 'give' ) );

							}
						}

						break;
				}

				do_action( 'give_stripe_event_' . $event->type, $event );

				die( '1' ); // Completed successfully.

			} else {
				status_header( 500 );
				// Something went wrong outside of Stripe.
				give_record_gateway_error( __( 'Stripe Error', 'give' ), sprintf( __( 'An error occurred while processing a webhook.', 'give' ) ) );
				die( '-1' ); // Failed.
			} // End if().
		}
	}
}
return new Give_Stripe_Card();
