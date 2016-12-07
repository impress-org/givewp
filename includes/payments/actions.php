<?php
/**
 * Payment Actions
 *
 * @package     Give
 * @subpackage  Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Complete a donation
 *
 * Performs all necessary actions to complete a donation.
 * Triggered by the give_update_payment_status() function.
 *
 * @since  1.0
 *
 * @param  int    $payment_id The ID number of the payment.
 * @param  string $new_status The status of the payment, probably "publish".
 * @param  string $old_status The status of the payment prior to being marked as "complete", probably "pending".
 *
 * @return void
 */
function give_complete_purchase( $payment_id, $new_status, $old_status ) {

	// Make sure that payments are only completed once.
	if ( $old_status == 'publish' || $old_status == 'complete' ) {
		return;
	}

	// Make sure the payment completion is only processed when new status is complete.
	if ( $new_status != 'publish' && $new_status != 'complete' ) {
		return;
	}

	$payment = new Give_Payment( $payment_id );

	$creation_date  = get_post_field( 'post_date', $payment_id, 'raw' );
	$payment_meta   = $payment->payment_meta;
	$completed_date = $payment->completed_date;
	$user_info      = $payment->user_info;
	$customer_id    = $payment->customer_id;
	$amount         = $payment->total;
	$price_id       = $payment->price_id;
	$form_id        = $payment->form_id;

	/**
	 * Fires before completing donation.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id The ID of the payment.
	 */
	do_action( 'give_pre_complete_purchase', $payment_id );

	// Ensure these actions only run once, ever.
	if ( empty( $completed_date ) ) {

		give_record_sale_in_log( $form_id, $payment_id, $price_id, $creation_date );

		/**
		 * Fires after logging donation record.
		 *
		 * @since 1.0
		 *
		 * @param int   $form_id      The ID number of the form.
		 * @param int   $payment_id   The ID number of the payment.
		 * @param array $payment_meta The payment meta.
		 */
		do_action( 'give_complete_form_donation', $form_id, $payment_id, $payment_meta );

	}

	// Increase the earnings for this form ID.
	give_increase_earnings( $form_id, $amount );
	give_increase_purchase_count( $form_id );

	// Clear the total earnings cache.
	delete_transient( 'give_earnings_total' );
	// Clear the This Month earnings (this_monththis_month is NOT a typo).
	delete_transient( md5( 'give_earnings_this_monththis_month' ) );
	delete_transient( md5( 'give_earnings_todaytoday' ) );

	// Increase the donor's donation stats.
	$customer = new Give_Customer( $customer_id );
	$customer->increase_purchase_count();
	$customer->increase_value( $amount );

	give_increase_total_earnings( $amount );

	// Ensure this action only runs once ever.
	if ( empty( $completed_date ) ) {

		// Save the completed date.
		$payment->completed_date = current_time( 'mysql' );
		$payment->save();

		/**
		 * Fires after a donation successfully complete.
		 *
		 * @since 1.0
		 *
		 * @param int $payment_id The ID of the payment.
		 */
		do_action( 'give_complete_donation', $payment_id );
	}

}

add_action( 'give_update_payment_status', 'give_complete_purchase', 100, 3 );


/**
 * Record payment status change
 *
 * @since  1.0
 *
 * @param  int    $payment_id The ID number of the payment.
 * @param  string $new_status The status of the payment, probably "publish".
 * @param  string $old_status The status of the payment prior to being marked as "complete", probably "pending".
 *
 * @return void
 */
function give_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated.
	$stati      = give_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	// translators: 1: old status 2: new status.
	$status_change = sprintf(
		esc_html__( 'Status changed from %1$s to %2$s.', 'give' ),
		$old_status,
		$new_status
	);

	give_insert_payment_note( $payment_id, $status_change );
}

add_action( 'give_update_payment_status', 'give_record_status_change', 100, 3 );


/**
 * Clear User History Cache
 *
 * Flushes the current user's donation history transient when a payment status
 * is updated.
 *
 * @since  1.0
 *
 * @param  int    $payment_id The ID number of the payment.
 * @param  string $new_status The status of the payment, probably "publish".
 * @param  string $old_status The status of the payment prior to being marked as "complete", probably "pending".
 *
 * @return void
 */
function give_clear_user_history_cache( $payment_id, $new_status, $old_status ) {

	$payment = new Give_Payment( $payment_id );

	if ( ! empty( $payment->user_id ) ) {
		delete_transient( 'give_user_' . $payment->user_id . '_purchases' );
	}

}

add_action( 'give_update_payment_status', 'give_clear_user_history_cache', 10, 3 );

/**
 * Update Old Payments Totals
 *
 * Updates all old payments, prior to 1.2, with new meta for the total donation amount.
 *
 * It's done to query payments by their totals.
 *
 * @since  1.0
 *
 * @param  array $data Arguments passed.
 *
 * @return void
 */
function give_update_old_payments_with_totals( $data ) {
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'give_upgrade_payments_nonce' ) ) {
		return;
	}

	if ( get_option( 'give_payment_totals_upgraded' ) ) {
		return;
	}

	$payments = give_get_payments( array(
		'offset' => 0,
		'number' => - 1,
		'mode'   => 'all',
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {

			$payment = new Give_Payment( $payment->ID );
			$meta    = $payment->get_meta();

			$payment->total = $meta['amount'];
			$payment->save();

		}
	}

	add_option( 'give_payment_totals_upgraded', 1 );
}

add_action( 'give_upgrade_payments', 'give_update_old_payments_with_totals' );

/**
 * Mark Abandoned Donations
 *
 * Updates over a week-old 'pending' donations to 'abandoned' status.
 *
 * @since  1.0
 *
 * @return void
 */
function give_mark_abandoned_donations() {
	$args = array(
		'status' => 'pending',
		'number' => - 1,
		'output' => 'give_payments',
	);

	add_filter( 'posts_where', 'give_filter_where_older_than_week' );

	$payments = give_get_payments( $args );

	remove_filter( 'posts_where', 'give_filter_where_older_than_week' );

	if ( $payments ) {
		/**
		 * Filter payment gateways:  Used to set payment gateways which can be skip while transferring pending payment to abandon.
		 *
		 * @since 1.6
		 *
		 * @param array $skip_payment_gateways Array of payment gateways
		 */
		$skip_payment_gateways = apply_filters( 'give_mark_abandoned_donation_gateways', array( 'offline' ) );

		foreach ( $payments as $payment ) {
			$gateway = give_get_payment_gateway( $payment );

			// Skip payment gateways.
			if ( in_array( $gateway, $skip_payment_gateways ) ) {
				continue;
			}

			$payment->status = 'abandoned';
			$payment->save();
		}
	}
}

add_action( 'give_weekly_scheduled_events', 'give_mark_abandoned_donations' );


/**
 * Trigger the refresh of this month reports transients
 *
 * @since 1.7
 *
 * @param int $payment_ID Payment ID.
 *
 * @return void
 */
function give_refresh_thismonth_stat_transients( $payment_ID ) {

	/* @var Give_Payment_Stats $stats Give_Payment_Stats class object.  */
	$stats = new Give_Payment_Stats();

	// Delete transients.
	delete_transient( 'give_estimated_monthly_stats' );
	delete_transient( 'give_earnings_total' );
	delete_transient( $stats->get_earnings_cache_key( 0, 'this_month' ) );
}

add_action( 'save_post_give_payment', 'give_refresh_thismonth_stat_transients' );
