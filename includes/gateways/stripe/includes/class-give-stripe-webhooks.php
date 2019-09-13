<?php
/**
 * Give - Stripe Core | Process Webhooks
 *
 * @since 2.5.0
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

if ( ! class_exists( 'Give_Stripe_Webhooks' ) ) {

	/**
	 * Class Give_Stripe_Webhooks
	 *
	 * @since 2.5.0
	 */
	class Give_Stripe_Webhooks {

		/**
		 * Stripe Gateway
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @var $stripe_gateway
		 */
		public $stripe_gateway;

		/**
		 * Give_Stripe_Webhooks constructor.
		 *
		 * @since 2.5.0
		 */
		public function __construct() {

			$this->stripe_gateway = new Give_Stripe_Gateway();

			add_action( 'init', array( $this, 'listen' ) );
		}

		/**
		 * Listen for Stripe events.
		 *
		 * @access public
		 * @since  2.5.0
		 *
		 * @return void
		 */
		public function listen() {

			$give_listener = give_clean( filter_input( INPUT_GET, 'give-listener' ) );

			// Must be a stripe listener to proceed.
			if ( ! isset( $give_listener ) || 'stripe' !== $give_listener ) {
				return;
			}

			// Load Stripe SDK.
			give_stripe_load_stripe_sdk();

			// Set App Info, API Key, and API Version.
			give_stripe_set_app_info();
			$this->stripe_gateway->set_api_version();

			// Retrieve the request's body and parse it as JSON.
			$body  = @file_get_contents( 'php://input' );
			$event = json_decode( $body );

			$processed_event = $this->process( $event );

			if ( false === $processed_event ) {
				$message = __( 'Something went wrong with processing the payment gateway event.', 'give' );
			} else {
				$message = sprintf(
					/* translators: 1. Processing result. */
					__( 'Processed event: %s', 'give' ),
					$processed_event
				);

				give_stripe_record_log(
					__( 'Stripe - Webhook Received', 'give' ),
					sprintf(
						/* translators: 1. Event ID 2. Event Type 3. Message */
						__( 'Webhook received with ID %1$s and TYPE %2$s which processed and returned a message %3$s.', 'give' ),
						$event->id,
						$event->type,
						$message
					)
				);
			}

			status_header( 200 );
			exit( $message );
		}

		/**
		 * Process Stripe Webhooks.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @param \Stripe\Event $event_json Stripe Event.
		 *
		 * @return bool|string
		 */
		public function process( $event_json ) {

			// Next, proceed with additional webhooks.
			if ( isset( $event_json->id ) ) {

				status_header( 200 );

				try {

					$event = \Stripe\Event::retrieve( $event_json->id );

					// Update time of webhook received whenever the event is retrieved.
					give_update_option( 'give_stripe_last_webhook_received_timestamp', current_time( 'timestamp', 1 ) );

				} catch ( \Stripe\Error\Authentication $e ) {

					if ( strpos( $e->getMessage(), 'Platform access may have been revoked' ) !== false ) {
						give_stripe_connect_delete_options();
					}
				} catch ( Exception $e ) {
					die( 'Invalid event ID' );
				}

				// Bailout, if event type doesn't exists.
				if ( empty( $event->type ) ) {
					return false;
				}

				switch ( $event->type ) {

					case 'checkout.session.completed':
						$this->process_checkout_session_completed( $event );
						break;

					case 'payment_intent.succeeded':
						$this->process_payment_intent_succeeded( $event );
						break;

					case 'payment_intent.payment_failed':
						$this->process_payment_intent_failed( $event );
						break;

					case 'charge.refunded':
						$this->process_charge_refunded( $event );
						break;
				}

				do_action( 'give_stripe_event_' . $event->type, $event );

				return $event->type;

			} else {
				status_header( 500 );
				// Something went wrong outside of Stripe.
				give_record_gateway_error( __( 'Stripe Error', 'give' ), sprintf( __( 'An error occurred while processing a webhook.', 'give' ) ) );
				die( '-1' ); // Failed.
			} // End if().
		}

		/**
		 * This function will process `checkout.session.completed` webhook event.
		 *
		 * @param \Stripe\Event $event Stripe Event.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_checkout_session_completed( $event ) {

			// Get Payment Intent data from Event.
			$checkout_session = $event->data->object;

			// Process when Payment Intent status is succeeded.
			$donation_id = give_get_purchase_id_by_transaction_id( $checkout_session->id );

			// Update payment status to donation.
			give_update_payment_status( $donation_id, 'publish' );

			// Insert donation note to inform admin that charge succeeded.
			give_insert_payment_note( $donation_id, __( 'Charge succeeded in Stripe.', 'give' ) );

			/**
			 * This action hook will be used to extend processing the payment intent succeeded event.
			 *
			 * @since 2.5.5
			 */
			do_action( 'give_stripe_process_checkout_session_completed', $donation_id, $event );
		}

		/**
		 * This function will process `payment_intent.succeeded` webhook event.
		 *
		 * @param \Stripe\Event $event Stripe Event.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment_intent_succeeded( $event ) {

			// Get Payment Intent data from Event.
			$intent = $event->data->object;

			// Process when Payment Intent status is succeeded.
			if ( 'succeeded' === $intent->status ) {
				$donation_id = give_get_purchase_id_by_transaction_id( $intent->id );

				// Update payment status to donation.
				give_update_payment_status( $donation_id, 'publish' );

				// Insert donation note to inform admin that charge succeeded.
				give_insert_payment_note( $donation_id, __( 'Charge succeeded in Stripe.', 'give' ) );
			}

			/**
			 * This action hook will be used to extend processing the payment intent succeeded event.
			 *
			 * @since 2.5.5
			 */
			do_action( 'give_stripe_process_payment_intent_succeeded', $event );
		}

		/**
		 * This function will process `payment_intent.failed` webhook event.
		 *
		 * @param \Stripe\Event $event Stripe Event.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_payment_intent_failed( $event ) {

			// Get Payment Intent data from Event.
			$intent      = $event->data->object;
			$donation_id = give_get_purchase_id_by_transaction_id( $intent->id );

			// Update payment status to donation.
			give_update_payment_status( $donation_id, 'failed' );

			// Insert donation note to inform admin that charge succeeded.
			give_insert_payment_note( $donation_id, __( 'Charge failed in Stripe.', 'give' ) );

			/**
			 * This action hook will be used to extend processing the payment intent failed event.
			 *
			 * @since 2.5.5
			 */
			do_action( 'give_stripe_process_payment_intent_failed', $event );
		}

		/**
		 * This function will process `charge.refunded` webhook event.
		 *
		 * @param \Stripe\Event $event Stripe Event.
		 *
		 * @since  2.5.5
		 * @access public
		 *
		 * @return void
		 */
		public function process_charge_refunded( $event ) {
			global $wpdb;

			$charge = $event->data->object;

			if ( $charge->refunded ) {

				$payment_id = $wpdb->get_var( $wpdb->prepare( "SELECT donation_id FROM {$wpdb->donationmeta} WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $charge->id ) );

				if ( $payment_id ) {

					give_update_payment_status( $payment_id, 'refunded' );
					give_insert_payment_note( $payment_id, __( 'Charge refunded in Stripe.', 'give' ) );

				}
			}

			/**
			 * This action hook will be used to extend processing the charge refunded event.
			 *
			 * @since 2.5.5
			 */
			do_action( 'give_stripe_process_charge_refunded', $event );
		}
	}
}

new Give_Stripe_Webhooks();
