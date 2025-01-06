<?php
/**
 * Payment Actions
 *
 * @package     Give
 * @subpackage  Payments
 * @copyright   Copyright (c) 2016, GiveWP
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
	$donor_id       = $payment->customer_id;
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
	do_action( 'give_pre_complete_donation', $payment_id );

	// Ensure these actions only run once, ever.
	if ( empty( $completed_date ) ) {

		give_record_donation_in_log( $form_id, $payment_id, $price_id, $creation_date );

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
	give_increase_earnings( $form_id, $amount, $payment_id );
	give_increase_donation_count( $form_id );

	// Update the goal progress for this form ID.
	give_update_goal_progress( $form_id );

	// @todo: Refresh only range related stat cache
	give_delete_donation_stats();

	// Increase the donor's donation stats.
	$donor = new Give_Donor( $donor_id );
	$donor->increase_purchase_count();
	$donor->increase_value( $amount );

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
	$status_change = sprintf( esc_html__( 'Status changed from %1$s to %2$s.', 'give' ), $old_status, $new_status );

	give_insert_payment_note( $payment_id, $status_change );
}

add_action( 'give_update_payment_status', 'give_record_status_change', 100, 3 );


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

	$payments = give_get_payments(
		array(
			'offset' => 0,
			'number' => - 1,
			'mode'   => 'all',
		)
	);

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
		 * Filter payment gateways:  Used to set payment gateways that can be skipped while updating the donation status from pending to abandoned.
		 *
		 * @since 1.6
		 *
		 * @param array $skip_payment_gateways Array of payment gateways
		 */
		$skip_payment_gateways = apply_filters( 'give_mark_abandoned_donation_gateways', array( 'offline' ) );

		/* @var Give_Payment $payment */
		foreach ( $payments as $payment ) {
			$gateway = give_get_payment_gateway( $payment->ID );

			// Skip payment gateways.
			if ( in_array( $gateway, $skip_payment_gateways ) ) {
				continue;
			}

			$payment->status = 'abandoned';
			$payment->save();
		}
	}
}

Give_Cron::add_weekly_event( 'give_mark_abandoned_donations' );


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
	// Monthly stats.
	Give_Cache::delete( Give_Cache::get_key( 'give_estimated_monthly_stats' ) );

	// @todo: Refresh only range related stat cache
	give_delete_donation_stats();
}

add_action( 'save_post_give_payment', 'give_refresh_thismonth_stat_transients' );


/**
 * Add support to get all payment meta.
 * Note: only use for internal purpose
 *
 * @since 3.19.0 change $donor_data['address'] to array instead of false
 * @since 2.0
 *
 * @param $check
 * @param $object_id
 * @param $meta_key
 * @param $single
 *
 * @return array
 */
function give_bc_v20_get_payment_meta( $check, $object_id, $meta_key, $single ) {
	// Bailout.
	if (
		'give_payment' !== get_post_type( $object_id )
		|| '_give_payment_meta' !== $meta_key
	) {
		return $check;
	}

	$cache_key = "_give_payment_meta_{$object_id}";

	// Get already calculate payment meta from cache.
	$payment_meta = Give_Cache::get_db_query( $cache_key );

	if ( is_null( $payment_meta ) ) {
		// Remove filter.
		remove_filter( 'get_post_metadata', 'give_bc_v20_get_payment_meta', 999 );

		$donation = new Give_Payment( $object_id );

		// Get all payment meta.
		$payment_meta = give_get_meta( $object_id );

		// Set default value to array.
		if ( empty( $payment_meta ) ) {
			return $check;
		}

		// Convert all meta key value to string instead of array
		array_walk(
			$payment_meta,
			function ( &$meta, $key ) {
				$meta = current( $meta );
			}
		);

		/**
		 * Add backward compatibility to old meta keys.
		 */
		// Donation key.
		$payment_meta['key'] = ! empty( $payment_meta['_give_payment_purchase_key'] ) ? $payment_meta['_give_payment_purchase_key'] : '';

		// Donation form.
		$payment_meta['form_title'] = ! empty( $payment_meta['_give_payment_form_title'] ) ? $payment_meta['_give_payment_form_title'] : '';

		// Donor email.
		$payment_meta['email'] = ! empty( $payment_meta['_give_payment_donor_email'] ) ? $payment_meta['_give_payment_donor_email'] : '';
		$payment_meta['email'] = ! empty( $payment_meta['email'] ) ?
			$payment_meta['email'] :
			Give()->donors->get_column( 'email', $donation->donor_id );

		// Form id.
		$payment_meta['form_id'] = ! empty( $payment_meta['_give_payment_form_id'] ) ? $payment_meta['_give_payment_form_id'] : '';

		// Price id.
		$payment_meta['price_id'] = isset( $payment_meta['_give_payment_price_id'] ) ? $payment_meta['_give_payment_price_id'] : '';

		// Date.
		$payment_meta['date'] = ! empty( $payment_meta['_give_payment_date'] ) ? $payment_meta['_give_payment_date'] : '';
		$payment_meta['date'] = ! empty( $payment_meta['date'] ) ?
			$payment_meta['date'] :
			get_post_field( 'post_date', $object_id );

		// Currency.
		$payment_meta['currency'] = ! empty( $payment_meta['_give_payment_currency'] ) ? $payment_meta['_give_payment_currency'] : '';

		// Decode donor data.
		$donor_id = ! empty( $payment_meta['_give_payment_donor_id'] ) ? $payment_meta['_give_payment_donor_id'] : 0;
		$donor    = new Give_Donor( $donor_id );

		// Donor first name.
		$donor_data['first_name'] = ! empty( $payment_meta['_give_donor_billing_first_name'] ) ? $payment_meta['_give_donor_billing_first_name'] : '';
		$donor_data['first_name'] = ! empty( $donor_data['first_name'] ) ?
			$donor_data['first_name'] :
			$donor->get_first_name();

		// Donor last name.
		$donor_data['last_name'] = ! empty( $payment_meta['_give_donor_billing_last_name'] ) ? $payment_meta['_give_donor_billing_last_name'] : '';
		$donor_data['last_name'] = ! empty( $donor_data['last_name'] ) ?
			$donor_data['last_name'] :
			$donor->get_last_name();

		// Donor email.
		$donor_data['email'] = $payment_meta['email'];

		// User ID.
		$donor_data['id'] = $donation->user_id;

		$donor_data['address'] = [];

		// Address1.
		$address1 = ! empty( $payment_meta['_give_donor_billing_address1'] ) ? $payment_meta['_give_donor_billing_address1'] : '';
		if ( $address1 ) {
			$donor_data['address']['line1'] = $address1;
		}

		// Address2.
		$address2 = ! empty( $payment_meta['_give_donor_billing_address2'] ) ? $payment_meta['_give_donor_billing_address2'] : '';
		if ( $address2 ) {
			$donor_data['address']['line2'] = $address2;
		}

		// City.
		$city = ! empty( $payment_meta['_give_donor_billing_city'] ) ? $payment_meta['_give_donor_billing_city'] : '';
		if ( $city ) {
			$donor_data['address']['city'] = $city;
		}

		// Zip.
		$zip = ! empty( $payment_meta['_give_donor_billing_zip'] ) ? $payment_meta['_give_donor_billing_zip'] : '';
		if ( $zip ) {
			$donor_data['address']['zip'] = $zip;
		}

		// State.
		$state = ! empty( $payment_meta['_give_donor_billing_state'] ) ? $payment_meta['_give_donor_billing_state'] : '';
		if ( $state ) {
			$donor_data['address']['state'] = $state;
		}

		// Country.
		$country = ! empty( $payment_meta['_give_donor_billing_country'] ) ? $payment_meta['_give_donor_billing_country'] : '';
		if ( $country ) {
			$donor_data['address']['country'] = $country;
		}

		$payment_meta['user_info'] = $donor_data;

		// Add filter
		add_filter( 'get_post_metadata', 'give_bc_v20_get_payment_meta', 999, 4 );

		// Set custom meta key into payment meta.
		if ( ! empty( $payment_meta['_give_payment_meta'] ) ) {
			$payment_meta['_give_payment_meta'] = is_array( $payment_meta['_give_payment_meta'] ) ? $payment_meta['_give_payment_meta'] : array();

			$payment_meta = array_merge( maybe_unserialize( $payment_meta['_give_payment_meta'] ), $payment_meta );
		}

		// Set cache.
		Give_Cache::set_db_query( $cache_key, $payment_meta );
	}

	if ( $single ) {
		/**
		 * Filter the payment meta
		 * Add custom meta key to payment meta
		 *
		 * @since 2.0
		 */
		$new_payment_meta[0] = apply_filters( 'give_get_payment_meta', $payment_meta, $object_id, $meta_key );

		$payment_meta = $new_payment_meta;
	}

	return $payment_meta;
}

if ( give_has_upgrade_completed( 'v20_upgrades_payment_metadata' ) ) {
	add_filter( 'get_post_metadata', 'give_bc_v20_get_payment_meta', 999, 4 );
}

/**
 * Add meta in payment that store page id and page url.
 *
 * Will add/update when user add click on the checkout page.
 * The status of the donation doest not matter as it get change when user had made the payment successfully.
 *
 * @since 1.8.13
 *
 * @param int $payment_id Payment id for which the meta value should be updated.
 */
function give_payment_save_page_data( $payment_id ) {
	$page_url = ( ! empty( $_REQUEST['give-current-url'] ) ? esc_url( $_REQUEST['give-current-url'] ) : false );

	// Check $page_url is not empty.
	if ( $page_url ) {
		update_post_meta( $payment_id, '_give_current_url', $page_url );
		$page_id = url_to_postid( $page_url );
		// Check $page_id is not empty.
		if ( $page_id ) {
			update_post_meta( $payment_id, '_give_current_page_id', $page_id );
		}
	}
}

// Fire when payment is save.
add_action( 'give_insert_payment', 'give_payment_save_page_data' );
