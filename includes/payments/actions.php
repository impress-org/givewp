<?php
/**
 * Payment Actions
 *
 * @package     Give
 * @subpackage  Payments
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Complete a purchase aka donation
 *
 * Performs all necessary actions to complete a purchase.
 * Triggered by the give_update_payment_status() function.
 *
 * @since 1.0
 *
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 *
 * @return void
 */
function give_complete_purchase( $payment_id, $new_status, $old_status ) {

	// Make sure that payments are only completed once
	if ( $old_status == 'publish' || $old_status == 'complete' ) {
		return;
	}

	// Make sure the payment completion is only processed when new status is complete
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

	do_action( 'give_pre_complete_purchase', $payment_id );

	// Ensure these actions only run once, ever
	if ( empty( $completed_date ) ) {

		give_record_sale_in_log( $form_id, $payment_id, $price_id, $creation_date );
		do_action( 'give_complete_form_donation', $form_id, $payment_id, $payment_meta );

	}

	// Increase the earnings for this form ID
	give_increase_earnings( $form_id, $amount );
	give_increase_purchase_count( $form_id );

	// Clear the total earnings cache
	delete_transient( 'give_earnings_total' );
	// Clear the This Month earnings (this_monththis_month is NOT a typo)
	delete_transient( md5( 'give_earnings_this_monththis_month' ) );
	delete_transient( md5( 'give_earnings_todaytoday' ) );
	
	// Increase the donor's purchase stats
	$customer = new Give_Customer( $customer_id );
	$customer->increase_purchase_count();
	$customer->increase_value( $amount );

	give_increase_total_earnings( $amount );

	// Ensure this action only runs once ever
	if ( empty( $completed_date ) ) {

		// Save the completed date
		$payment->completed_date = current_time( 'mysql' );
		$payment->save();
		do_action( 'give_complete_purchase', $payment_id );
	}

}

add_action( 'give_update_payment_status', 'give_complete_purchase', 100, 3 );


/**
 * Record payment status change
 *
 * @since 1.0
 *
 * @param int $payment_id the ID number of the payment
 * @param string $new_status the status of the payment, probably "publish"
 * @param string $old_status the status of the payment prior to being marked as "complete", probably "pending"
 *
 * @return void
 */
function give_record_status_change( $payment_id, $new_status, $old_status ) {

	// Get the list of statuses so that status in the payment note can be translated
	$stati      = give_get_payment_statuses();
	$old_status = isset( $stati[ $old_status ] ) ? $stati[ $old_status ] : $old_status;
	$new_status = isset( $stati[ $new_status ] ) ? $stati[ $new_status ] : $new_status;

	$status_change = sprintf(
		/* translators: 1: old status 2: new status */
		esc_html__( 'Status changed from %1$s to %2$s.', 'give' ),
		$old_status,
		$new_status
	);

	give_insert_payment_note( $payment_id, $status_change );
}

add_action( 'give_update_payment_status', 'give_record_status_change', 100, 3 );


/**
 * Flushes the current user's purchase history transient when a payment status
 * is updated
 *
 * @since 1.0
 *
 * @param $payment_id
 * @param $new_status the status of the payment, probably "publish"
 * @param $old_status the status of the payment prior to being marked as "complete", probably "pending"
 */
function give_clear_user_history_cache( $payment_id, $new_status, $old_status ) {

	$payment = new Give_Payment( $payment_id );

	if ( ! empty( $payment->user_id ) ) {
		delete_transient( 'give_user_' . $payment->user_id . '_purchases' );
	}

}

add_action( 'give_update_payment_status', 'give_clear_user_history_cache', 10, 3 );

/**
 * Updates all old payments, prior to 1.2, with new
 * meta for the total purchase amount
 *
 * This is so that payments can be queried by their totals
 *
 * @since 1.0
 *
 * @param array $data Arguments passed
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
		'mode'   => 'all'
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
 * Updates week-old+ 'pending' orders to 'abandoned'
 *
 * @since 1.0
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
		foreach ( $payments as $payment ) {
			$gateway = give_get_payment_gateway( $payment );
			//Skip offline gateway payments
			if ( $gateway == 'offline' ) {
				continue;
			}
			$payment->status = 'abandoned';
			$payment->save();
		}
	}
}

add_action( 'give_weekly_scheduled_events', 'give_mark_abandoned_donations' );