<?php
/**
 * Give - Stripe Core Webhooks
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
		 * WebHook URL.
		 *
		 * @since 2.5.0
		 *
		 * @var $url
		 */
		public $url = '';

		/**
		 * Give_Stripe_Webhooks constructor.
		 *
		 * @since 2.5.0
		 */
		public function __construct() {
			$this->url = site_url() . '?give-listener=stripe';
			add_action( 'init', array( $this, 'check_status' ) );
		}

		/**
		 * This function is used to create webhooks in Stripe.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\ApiResource
		 */
		public function create() {

			// Set Application Info.
			give_stripe_set_app_info();

			try {

				$result = \Stripe\WebhookEndpoint::create(
					array(
						'url'            => $this->url,
						'enabled_events' => array( '*' ),
						'connect'        => true,
					)
				);

				$this->set_data_to_db( $result->id );
				return $result;
			} catch ( \Stripe\Error\InvalidRequest $e ) {
				give_record_gateway_error(
					__( 'Stripe Webhook Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to create a webhook. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				return false;
			}
		}

		/**
		 * This function is used to retrieve Stripe webhooks details based on the webhook id.
		 *
		 * @param string $id WebHook ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\StripeObject
		 */
		public function retrieve( $id ) {

			// Set Application Info.
			give_stripe_set_app_info();

			try {
				return \Stripe\WebhookEndpoint::retrieve( $id );
			} catch ( \Stripe\Error\InvalidRequest $e ) {
				give_record_gateway_error(
					__( 'Stripe Webhook Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to retrieve webhook. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				return false;
			}
		}

		/**
		 * This function is used to list all the webhooks registered with Stripe.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return \Stripe\Collection
		 *
		 * @throws \Stripe\Error\Api Throws API error from Stripe.
		 */
		public function list_all() {

			// Set Application Info.
			give_stripe_set_app_info();

			try {
				return \Stripe\WebhookEndpoint::all(
					array(
						'limit' => 20,
					)
				);
			} catch ( \Stripe\Error\InvalidRequest $e ) {
				give_record_gateway_error(
					__( 'Stripe Webhook Error', 'give' ),
					sprintf(
						/* translators: %s Exception Error Message */
						__( 'Unable to list all webhooks. Details: %s', 'give' ),
						$e->getMessage()
					)
				);
				return false;
			}
		}

		/**
		 * This function is used to set default WebHook data to DB.
		 *
		 * @param int $id Webhook ID.
		 *
		 * @since  2.5.0
		 * @access public
		 *
		 * @return void
		 */
		public function set_data_to_db( $id ) {

			// Bailout, if $id is empty.
			if ( empty( $id ) ) {
				return;
			}

			$mode = give_stripe_get_payment_mode();

			// Set WebHook status flag.
			give_update_option( "give_stripe_is_{$mode}_webhook_exists", true );

			// Set WebHook id in DB.
			give_update_option( "give_stripe_{$mode}_webhook_id", $id );
		}
	}
}
