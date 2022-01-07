<?php
/**
 * Give Recurring Shortcodes
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
use Give\Receipt\DonationReceipt;
use GiveRecurring\Receipt\UpdateDonationReceipt;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recurring Shortcodes
 *
 * Adds additional recurring specific shortcodes as well as hooking into existing Give core shortcodes to add
 * additional subscription functionality
 *
 * @since  1.0
 */
class Give_Recurring_Shortcodes {

	/**
	 * Give_Recurring_Shortcodes constructor.
	 */
	function __construct() {

		// Give Recurring template files work.
		add_filter( 'give_template_paths', array( $this, 'add_template_stack' ) );

		// Show recurring details on the [give_receipt].
		add_action( 'give_payment_receipt_after_table', array( $this, 'subscription_receipt' ), 10, 2 );
		add_action( 'give_payment_receipt_after_table', array( $this, 'add_manage_subscriptions_link' ), 11, 1 );
		add_action( 'give_new_receipt', array( $this, 'addSubscriptionDetailsGroupToReceipt'), 10, 1 );

		// Adds the [give_subscriptions] shortcode for display subscription information.
		add_shortcode( 'give_subscriptions', array( $this, 'give_subscriptions' ) );

		// Update subscription payment method.
		add_action( 'give_recurring_update_payment', array( $this, 'verify_profile_update_setup' ), 10 );

		// Update renewal subscription.
		add_action( 'wp_ajax_recurring_update_subscription_amount', array( $this, 'verify_subscription_update' ), 10 );
		add_action( 'wp_ajax_nopriv_recurring_update_subscription_amount', array( $this, 'verify_subscription_update' ), 10 );
	}

	/**
	 * Adds our templates dir to the Give template stack
	 *
	 * @since 1.0
	 *
	 * @param string $paths Path.
	 *
	 * @return mixed
	 */
	public function add_template_stack( $paths ) {

		$paths[50] = GIVE_RECURRING_PLUGIN_DIR . 'templates/';

		return $paths;

	}

	/**
	 * Subscription Receipt
	 *
	 * Displays the recurring details on the [give_receipt]
	 *
	 * @since 1.0
	 *
	 * @param WP_Post $payment      Donation Object.
	 * @param array   $receipt_args Donation Receipt Arguments.
	 *
	 * @return mixed
	 */
	public function subscription_receipt( $payment, $receipt_args ) {

		ob_start();

		give_get_template_part( 'shortcode', 'subscription-receipt' );

		echo ob_get_clean();

	}

	/**
	 * This function will add manage subscription link where required.
	 *
	 * @param WP_Post $donation Donation Object.
	 *
	 * @since 1.8.2
	 *
	 * @return void
	 */
	public function add_manage_subscriptions_link( $donation ) {

		$is_subscription_donation = (bool) Give()->payment_meta->get_meta( $donation->ID, '_give_subscription_payment', true );

		if ( 'give_subscription' === $donation->post_status || true === $is_subscription_donation ) {
			give_recurring_get_manage_subscriptions_link();
		}

	}

	/**
	 * Update donation receipt.
	 *
	 * @param DonationReceipt $receipt
	 *
	 * @return mixed
	 */
	public function addSubscriptionDetailsGroupToReceipt( $receipt ){
		$updateReceipt = new UpdateDonationReceipt($receipt);

		$updateReceipt->apply();
	}


	/**
	 * Sets up the process of verifying the saving of the updated payment method
	 *
	 * @since  x.x
	 * @return void
	 */
	public function verify_profile_update_setup() {

		$subscription_id = ( isset( $_POST['subscription_id'] ) && ! empty( $_POST['subscription_id'] ) ) ? absint( $_POST['subscription_id'] ) : 0;

		if ( empty( $subscription_id ) ){
			give_set_error( 'give_recurring_invalid_subscription_id', __( 'Invalid subscription ID.', 'give-recurring' ) );
		}

		$subscription    = new Give_Subscription( $subscription_id );

		$this->verify_profile_update_action( $subscription->donor_id );

	}

	/**
	 * Verify and fire the hook to update a recurring payment method
	 *
	 * @since  x.x
	 *
	 * @param  int $user_id The User ID to update.
	 *
	 * @return void
	 */
	private function verify_profile_update_action( $user_id ) {
		$subscriptionId = $this->getSubscriptionIdFromPostedData();

		$passed_nonce = isset( $_POST['give_recurring_update_nonce'] )
			? give_clean( $_POST['give_recurring_update_nonce'] )
			: false;

		if ( false === $passed_nonce || ! isset( $_POST['_wp_http_referer'] ) ) {
			give_set_error( 'give_recurring_invalid_payment_update', __( 'Invalid Payment Update', 'give-recurring' ) );
		}

		$verified = wp_verify_nonce( $passed_nonce, "update-payment-{$subscriptionId}" );

		if ( 1 !== $verified || empty( $user_id ) ) {
			give_set_error( 'give_recurring_unable_payment_update', __( 'Unable to verify payment update. Please try again later.', 'give-recurring' ) );
		}

		// Check if a subscription_id is passed to use the new update methods
		if ( $subscriptionId ) {
			do_action( 'give_recurring_update_subscription_payment_method', $user_id, absint( $_POST['subscription_id'] ), $verified );
		}

	}

	/**
	 * Sets up the process of verifying the saving of the updated subscription.
	 *
	 * @since  1.8
	 */
	public function verify_subscription_update() {

		// Get Subscription ID.
		$subscription_id = ( isset( $_POST['subscription_id'] ) && ! empty( $_POST['subscription_id'] ) ) ? absint( $_POST['subscription_id'] ) : 0;

		// Set error if Subscription Id not set.
		if ( empty( $subscription_id ) ) {
			give_set_error( 'give_recurring_invalid_subscription_id', __( 'Invalid subscription ID.', 'give-recurring' ) );
		}

		// Subscription object.
		$subscription = new Give_Subscription( $subscription_id );

		// Verify subscription update action.
		$this->verify_subscription_update_action( $subscription->donor_id );
	}

	/**
	 * Verify and fire the hook to update a subscription.
	 *
	 * @since  1.8
	 *
	 * @param  int $user_id The User ID to update.
	 */
	private function verify_subscription_update_action( $user_id ) {
		$subscriptionId = $this->getSubscriptionIdFromPostedData();

		// Get subscription update nonce.
		$passed_nonce = isset( $_POST['give_recurring_subscription_update_nonce'] )
			? give_clean( $_POST['give_recurring_subscription_update_nonce'] )
			: false;

		// Check nonce refer.
		if ( false === $passed_nonce || ! isset( $_POST['_wp_http_referer'] ) ) {
			give_set_error( 'give_recurring_invalid_subscription_update', __( 'Invalid Subscription Update', 'give-recurring' ) );
		}

		// Check nonce.
		$verified = wp_verify_nonce( $passed_nonce, "update-subscription-{$subscriptionId}" );

		// Set error if nonce verification failed.
		if ( 1 !== $verified || empty( $user_id ) ) {
			give_set_error( 'give_recurring_unable_subscription_update', __( 'Unable to verify subscription update. Please try again later.', 'give-recurring' ) );
		}

		// Check if a subscription_id is passed to use the new update methods.
		if ( $subscriptionId ) {

			/**
			 * Update renewal subscription.
			 * e.g. Update renewal amount.
			 *
			 * @since 1.8
			 *
			 * @param int  $user_id         The User ID to update.
			 * @param int  $subscription_id The Subscription to update.
			 * @param bool $verified        Sanity check that the request to update is coming from a verified source.
			 *
			 */
			do_action( 'give_recurring_update_renewal_subscription', $user_id, absint( $_POST['subscription_id'] ), $verified );
		}
	}

	/**
	 * Subscriptions
	 *
	 * Provides users with an historical overview of their purchased subscriptions
	 *
	 * @param array $atts Shortcode attributes
	 *
	 * @since      1.0
	 *
	 * @return string The html for the subscriptions shortcode.
	 */
	public function give_subscriptions( $atts ) {

		global $give_subscription_args;

		$give_subscription_args = shortcode_atts( array(
			'show_status'            => true,
			'show_renewal_date'      => true,
			'show_progress'          => false,
			'show_start_date'        => false,
			'show_end_date'          => false,
			'subscriptions_per_page' => 30,
			'pagination_type'        => 'next_and_previous',
		), $atts, 'give_subscriptions' );

		// Convert shortcode_atts values to booleans.
		foreach ( $give_subscription_args as $key => $value ) {
			if ( 'subscriptions_per_page' !== $key && 'pagination_type' !== $key ) {
				$give_subscription_args[ $key ] = filter_var( $give_subscription_args[ $key ], FILTER_VALIDATE_BOOLEAN );
			}
		}

		return Give_Recurring()->subscriptions_view();
	}


	/**
	 * Get subscription id from posted data
	 *
	 * @since 1.10.1
	 *
	 * @return int|null
	 */
	private function getSubscriptionIdFromPostedData(){
		return ! empty( $_POST['subscription_id'] ) ? absint( $_POST['subscription_id'] ) : null;
	}

}

new Give_Recurring_Shortcodes();
