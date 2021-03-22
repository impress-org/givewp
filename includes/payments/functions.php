<?php
/**
 * Payment Functions
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
 * Get Payments
 *
 * Retrieve payments from the database.
 *
 * Since 1.0, this function takes an array of arguments, instead of individual
 * parameters. All of the original parameters remain, but can be passed in any
 * order via the array.
 *
 * @since 1.0
 *
 * @param array $args     {
 *                        Optional. Array of arguments passed to payments query.
 *
 * @type int    $offset   The number of payments to offset before retrieval.
 *                            Default is 0.
 * @type int    $number   The number of payments to query for. Use -1 to request all
 *                            payments. Default is 20.
 * @type string $mode     Default is 'live'.
 * @type string $order    Designates ascending or descending order of payments.
 *                            Accepts 'ASC', 'DESC'. Default is 'DESC'.
 * @type string $orderby  Sort retrieved payments by parameter. Default is 'ID'.
 * @type string $status   The status of the payments. Default is 'any'.
 * @type string $user     User. Default is null.
 * @type string $meta_key Custom field key. Default is null.
 *
 * }
 *
 * @return array $payments Payments retrieved from the database
 */
function give_get_payments( $args = [] ) {

	// Fallback to post objects to ensure backwards compatibility.
	if ( ! isset( $args['output'] ) ) {
		$args['output'] = 'posts';
	}

	$args     = apply_filters( 'give_get_payments_args', $args );
	$payments = new Give_Payments_Query( $args );

	return $payments->get_payments();
}

/**
 * Retrieve payment by a given field
 *
 * @since  1.0
 *
 * @param  string $field The field to retrieve the payment with.
 * @param  mixed  $value The value for $field.
 *
 * @return mixed
 */
function give_get_payment_by( $field = '', $value = '' ) {

	if ( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch ( strtolower( $field ) ) {

		case 'id':
			$payment = new Give_Payment( $value );
			$id      = $payment->ID;

			if ( empty( $id ) ) {
				return false;
			}

			break;

		case 'key':
			$payment = give_get_payments(
				[
					'meta_key'       => '_give_payment_purchase_key',
					'meta_value'     => $value,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			);

			if ( $payment ) {
				$payment = new Give_Payment( $payment[0] );
			}

			break;

		case 'payment_number':
			$payment = give_get_payments(
				[
					'meta_key'       => '_give_payment_number',
					'meta_value'     => $value,
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			);

			if ( $payment ) {
				$payment = new Give_Payment( $payment[0] );
			}

			break;

		default:
			return false;
	}// End switch().

	if ( $payment ) {
		return $payment;
	}

	return false;
}

/**
 * Insert Payment
 *
 * @since  1.0
 *
 * @param  array $payment_data Arguments passed.
 *
 * @return int|bool Payment ID if payment is inserted, false otherwise.
 */
function give_insert_payment( $payment_data = [] ) {

	if ( empty( $payment_data ) ) {
		return false;
	}

	/**
	 * Fire the filter on donation data before insert.
	 *
	 * @since 1.8.15
	 *
	 * @param array $payment_data Arguments passed.
	 */
	$payment_data = apply_filters( 'give_pre_insert_payment', $payment_data );

	$payment    = new Give_Payment();
	$gateway    = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';
	$gateway    = empty( $gateway ) && isset( $_POST['give-gateway'] ) ? give_clean( $_POST['give-gateway'] ) : $gateway; // WPCS: input var ok, sanitization ok, CSRF ok.
	$form_id    = isset( $payment_data['give_form_id'] ) ? $payment_data['give_form_id'] : 0;
	$price_id   = give_get_payment_meta_price_id( $payment_data );
	$form_title = isset( $payment_data['give_form_title'] ) ? $payment_data['give_form_title'] : get_the_title( $form_id );

	// Set properties.
	$payment->total          = $payment_data['price'];
	$payment->status         = ! empty( $payment_data['status'] ) ? $payment_data['status'] : 'pending';
	$payment->currency       = ! empty( $payment_data['currency'] ) ? $payment_data['currency'] : give_get_currency( $payment_data['give_form_id'], $payment_data );
	$payment->user_info      = $payment_data['user_info'];
	$payment->gateway        = $gateway;
	$payment->form_title     = $form_title;
	$payment->form_id        = $form_id;
	$payment->price_id       = $price_id;
	$payment->donor_id       = ( ! empty( $payment_data['donor_id'] ) ? $payment_data['donor_id'] : '' );
	$payment->user_id        = $payment_data['user_info']['id'];
	$payment->email          = $payment_data['user_email'];
	$payment->first_name     = $payment_data['user_info']['first_name'];
	$payment->last_name      = $payment_data['user_info']['last_name'];
	$payment->title_prefix   = ! empty( $payment_data['user_info']['title'] ) ? $payment_data['user_info']['title'] : '';
	$payment->email          = $payment_data['user_info']['email'];
	$payment->ip             = give_get_ip();
	$payment->key            = $payment_data['purchase_key'];
	$payment->mode           = ( ! empty( $payment_data['mode'] ) ? (string) $payment_data['mode'] : ( give_is_test_mode() ? 'test' : 'live' ) );
	$payment->parent_payment = ! empty( $payment_data['parent'] ) ? absint( $payment_data['parent'] ) : '';

	// Add the donation.
	$args = [
		'price'    => $payment->total,
		'price_id' => $payment->price_id,
	];

	$payment->add_donation( $payment->form_id, $args );

	// Set date if present.
	if ( isset( $payment_data['post_date'] ) ) {
		$payment->date = $payment_data['post_date'];
	}

	// Save payment.
	$payment->save();

	// Setup donor id.
	$payment_data['user_info']['donor_id'] = $payment->donor_id;

	// Set donation id to purchase session only donor session for donation exist.
	$purchase_session = (array) Give()->session->get( 'give_purchase' );
	if ( $purchase_session && array_key_exists( 'purchase_key', $purchase_session ) ) {
		$purchase_session['donation_id'] = $payment->ID;
		Give()->session->set( 'give_purchase', $purchase_session );
	}

	/**
	 * Fires while inserting payments.
	 *
	 * @since 1.0
	 *
	 * @param int   $payment_id   The payment ID.
	 * @param array $payment_data Arguments passed.
	 */
	do_action( 'give_insert_payment', $payment->ID, $payment_data );

	// Return payment ID upon success.
	if ( ! empty( $payment->ID ) ) {
		return $payment->ID;
	}

	// Return false if no payment was inserted.
	return false;

}

/**
 * Create payment.
 *
 * @param $payment_data
 *
 * @return bool|int
 */
function give_create_payment( $payment_data ) {

	$form_id  = intval( $payment_data['post_data']['give-form-id'] );
	$price_id = isset( $payment_data['post_data']['give-price-id'] ) ? $payment_data['post_data']['give-price-id'] : '';

	// Collect payment data.
	$insert_payment_data = [
		'price'           => $payment_data['price'],
		'give_form_title' => $payment_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $payment_data['date'],
		'user_email'      => $payment_data['user_email'],
		'purchase_key'    => $payment_data['purchase_key'],
		'currency'        => give_get_currency( $form_id, $payment_data ),
		'user_info'       => $payment_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'paypal',
	];

	/**
	 * Filter the payment params.
	 *
	 * @since 1.8
	 *
	 * @param array $insert_payment_data
	 */
	$insert_payment_data = apply_filters( 'give_create_payment', $insert_payment_data );

	// Record the pending payment.
	return give_insert_payment( $insert_payment_data );
}

/**
 * Updates a payment status.
 *
 * @param  int    $payment_id Payment ID.
 * @param  string $new_status New Payment Status. Default is 'publish'.
 *
 * @since  1.0
 *
 * @return bool
 */
function give_update_payment_status( $payment_id, $new_status = 'publish' ) {

	$updated = false;
	$payment = new Give_Payment( $payment_id );

	if ( $payment && $payment->ID > 0 ) {

		$payment->status = $new_status;
		$updated         = $payment->save();

	}

	return $updated;
}


/**
 * Deletes a Donation
 *
 * @since  1.0
 *
 * @param  int  $payment_id   Payment ID (default: 0).
 * @param  bool $update_donor If we should update the donor stats (default:true).
 *
 * @return void
 */
function give_delete_donation( $payment_id = 0, $update_donor = true ) {
	$payment = new Give_Payment( $payment_id );

	// Bailout.
	if ( ! $payment->ID ) {
		return;
	}

	$amount = give_donation_amount( $payment_id );
	$status = $payment->post_status;
	$donor  = new Give_Donor( $payment->donor_id );

	// Only undo donations that aren't these statuses.
	$dont_undo_statuses = apply_filters(
		'give_undo_donation_statuses',
		[
			'pending',
			'cancelled',
		]
	);

	if ( ! in_array( $status, $dont_undo_statuses ) ) {
		give_undo_donation( $payment_id );
	}

	// Only undo donations that aren't these statuses.
	$status_to_decrease_stats = apply_filters( 'give_decrease_donor_statuses', [ 'publish' ] );

	if ( in_array( $status, $status_to_decrease_stats ) ) {

		// Only decrease earnings if they haven't already been decreased (or were never increased for this payment).
		give_decrease_total_earnings( $amount );

		// @todo: Refresh only range related stat cache
		give_delete_donation_stats();

		if ( $donor->id && $update_donor ) {

			// Decrement the stats for the donor.
			$donor->decrease_donation_count();
			$donor->decrease_value( $amount );

		}
	}

	/**
	 * Fires before deleting payment.
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @since 1.0
	 */
	do_action( 'give_payment_delete', $payment_id );

	if ( $donor->id && $update_donor ) {
		// Remove the payment ID from the donor.
		$donor->remove_payment( $payment_id );
	}

	// Remove the payment.
	wp_delete_post( $payment_id, true );

	Give()->payment_meta->delete_all_meta( $payment_id );

	/**
	 * Fires after payment deleted.
	 *
	 * @param int $payment_id Payment ID.
	 *
	 * @since 1.0
	 */
	do_action( 'give_payment_deleted', $payment_id );
}

/**
 * Undo Donation
 *
 * Undoes a donation, including the decrease of donations and earning stats.
 * Used for when refunding or deleting a donation.
 *
 * @param  int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return void
 */
function give_undo_donation( $payment_id ) {

	$payment = new Give_Payment( $payment_id );

	$maybe_decrease_earnings = apply_filters( 'give_decrease_earnings_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_earnings ) {
		// Decrease earnings.
		give_decrease_form_earnings( $payment->form_id, $payment->total, $payment_id );
	}

	$maybe_decrease_donations = apply_filters( 'give_decrease_donations_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_donations ) {
		// Decrease donation count.
		give_decrease_donation_count( $payment->form_id );
	}

}


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @param  array $args Arguments passed.
 *
 * @since  1.0
 *
 * @return object $stats Contains the number of payments per payment status.
 */
function give_count_payments( $args = [] ) {
	// Backward compatibility.
	if ( ! empty( $args['start-date'] ) ) {
		$args['start_date'] = $args['start-date'];
		unset( $args['start-date'] );
	}

	if ( ! empty( $args['end-date'] ) ) {
		$args['end_date'] = $args['end-date'];
		unset( $args['end-date'] );
	}

	if ( ! empty( $args['form_id'] ) ) {
		$args['give_forms'] = $args['form_id'];
		unset( $args['form_id'] );
	}

	// Extract all donations
	$args['number']   = - 1;
	$args['group_by'] = 'post_status';
	$args['count']    = 'true';

	$donations_obj   = new Give_Payments_Query( $args );
	$donations_count = $donations_obj->get_payment_by_group();

	/**
	 * Filter the payment counts group by status
	 *
	 * @since 1.0
	 */
	return (object) apply_filters( 'give_count_payments', $donations_count, $args, $donations_obj );
}


/**
 * Check For Existing Payment
 *
 * @param  int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return bool $exists True if payment exists, false otherwise.
 */
function give_check_for_existing_payment( $payment_id ) {
	global $wpdb;

	return (bool) $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT ID
			FROM {$wpdb->posts}
			WHERE ID=%s
			AND post_status=%s
			",
			$payment_id,
			'publish'
		)
	);
}

/**
 * Get Payment Status
 *
 * @param WP_Post|Give_Payment|int $payment_id      Payment object or payment ID.
 * @param bool                     $return_label Whether to return the translated status label instead of status value.
 *                                               Default false.
 *
 * @since 1.0
 *
 * @return bool|mixed True if payment status exists, false otherwise.
 */
function give_get_payment_status( $payment_id, $return_label = false ) {

	if ( ! is_numeric( $payment_id ) ) {
		if (
			$payment_id instanceof Give_Payment
			|| $payment_id instanceof WP_Post
		) {
			$payment_id = $payment_id->ID;
		}
	}

	if ( ! $payment_id > 0 ) {
		return false;
	}

	$payment_status = get_post_status( $payment_id );

	$statuses = give_get_payment_statuses();

	if ( empty( $payment_status ) || ! is_array( $statuses ) || empty( $statuses ) ) {
		return false;
	}

	if ( array_key_exists( $payment_status, $statuses ) ) {
		if ( true === $return_label ) {
			// Return translated status label.
			return $statuses[ $payment_status ];
		} else {
			// Account that our 'publish' status is labeled 'Complete'
			$post_status = 'publish' === $payment_status ? 'Complete' : $payment_status;

			// Make sure we're matching cases, since they matter
			return array_search( strtolower( $post_status ), array_map( 'strtolower', $statuses ) );
		}
	}

	return false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since  1.0
 *
 * @return array $payment_status All the available payment statuses.
 */
function give_get_payment_statuses() {
	$payment_statuses = [
		'pending'     => __( 'Pending', 'give' ),
		'publish'     => __( 'Complete', 'give' ),
		'refunded'    => __( 'Refunded', 'give' ),
		'failed'      => __( 'Failed', 'give' ),
		'cancelled'   => __( 'Cancelled', 'give' ),
		'abandoned'   => __( 'Abandoned', 'give' ),
		'preapproval' => __( 'Pre-Approved', 'give' ),
		'processing'  => __( 'Processing', 'give' ),
		'revoked'     => __( 'Revoked', 'give' ),
	];

	return apply_filters( 'give_payment_statuses', $payment_statuses );
}

/**
 * Get Payment Status Keys
 *
 * Retrieves keys for all available statuses for payments
 *
 * @since 1.0
 *
 * @return array $payment_status All the available payment statuses.
 */
function give_get_payment_status_keys() {
	$statuses = array_keys( give_get_payment_statuses() );
	asort( $statuses );

	return array_values( $statuses );
}

/**
 * Get Earnings By Date
 *
 * @param int $day       Day number. Default is null.
 * @param int $month_num Month number. Default is null.
 * @param int $year      Year number. Default is null.
 * @param int $hour      Hour number. Default is null.
 *
 * @since 1.0
 *
 * @return int $earnings Earnings
 */
function give_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {
	// This is getting deprecated soon. Use Give_Payment_Stats with the get_earnings() method instead.
	global $wpdb;

	$args = [
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'monthnum'               => $month_num,
		'post_status'            => [ 'publish' ],
		'fields'                 => 'ids',
		'update_post_term_cache' => false,
	];
	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( isset( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'give_get_earnings_by_date_args', $args );
	$key  = Give_Cache::get_key( 'give_stats', $args );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'give-refresh-reports' ) ) {
		$earnings = false;
	} else {
		$earnings = Give_Cache::get( $key );
	}

	if ( false === $earnings ) {
		$donations = get_posts( $args );
		$earnings  = 0;

		$donation_table     = Give()->payment_meta->table_name;
		$donation_table_col = Give()->payment_meta->get_meta_type() . '_id';

		if ( $donations ) {
			$donations      = implode( ',', $donations );
			$earning_totals = $wpdb->get_var( "SELECT SUM(meta_value) FROM {$donation_table} WHERE meta_key = '_give_payment_total' AND {$donation_table_col} IN ({$donations})" );

			/**
			 * Filter The earnings by dates.
			 *
			 * @since 1.8.17
			 *
			 * @param float $earning_totals Total earnings between the dates.
			 * @param array $donations      Donations lists.
			 * @param array $args           Donation query args.
			 */
			$earnings = apply_filters( 'give_get_earnings_by_date', $earning_totals, $donations, $args );
		}
		// Cache the results for one hour.
		Give_Cache::set( $key, $earnings, HOUR_IN_SECONDS );
	}

	return round( $earnings, 2 );
}

/**
 * Get Donations (sales) By Date
 *
 * @param int $day       Day number. Default is null.
 * @param int $month_num Month number. Default is null.
 * @param int $year      Year number. Default is null.
 * @param int $hour      Hour number. Default is null.
 *
 * @since 1.0
 *
 * @return int $count Sales
 */
function give_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use Give_Payment_Stats with the get_sales() method instead.
	$args = [
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'fields'                 => 'ids',
		'post_status'            => [ 'publish' ],
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	];

	$show_free = apply_filters( 'give_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = [
			[
				'key'     => '_give_payment_total',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			],
		];
	}

	if ( ! empty( $month_num ) ) {
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( isset( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'give_get_sales_by_date_args', $args );

	$key = Give_Cache::get_key( 'give_stats', $args );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'give-refresh-reports' ) ) {
		$count = false;
	} else {
		$count = Give_Cache::get( $key );
	}

	if ( false === $count ) {
		$donations = new WP_Query( $args );
		$count     = (int) $donations->post_count;
		// Cache the results for one hour.
		Give_Cache::set( $key, $count, HOUR_IN_SECONDS );
	}

	return $count;
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @param int $payment_id Payment ID to check against.
 *
 * @since 1.0
 *
 * @return bool $ret True if complete, false otherwise.
 */
function give_is_payment_complete( $payment_id ) {
	$ret            = false;
	$payment_status = '';

	if ( $payment_id > 0 && 'give_payment' === get_post_type( $payment_id ) ) {
		$payment_status = get_post_status( $payment_id );

		if ( 'publish' === $payment_status ) {
			$ret = true;
		}
	}

	/**
	 * Filter the flag
	 *
	 * @since 1.0
	 */
	return apply_filters( 'give_is_payment_complete', $ret, $payment_id, $payment_status );
}

/**
 * Get Total Donations.
 *
 * @since 1.0
 *
 * @return int $count Total number of donations.
 */
function give_get_total_donations() {

	$payments = give_count_payments();

	return $payments->publish;
}

/**
 * Get Total Earnings
 *
 * @param bool $recalculate Recalculate earnings forcefully.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function give_get_total_earnings( $recalculate = false ) {

	$total      = get_option( 'give_earnings_total', 0 );
	$meta_table = __give_v20_bc_table_details( 'payment' );

	// Calculate total earnings.
	if ( ! $total || $recalculate ) {
		global $wpdb;

		$total = (float) 0;

		$args = apply_filters(
			'give_get_total_earnings_args',
			[
				'offset' => 0,
				'number' => - 1,
				'status' => [ 'publish' ],
				'fields' => 'ids',
			]
		);

		$payments = give_get_payments( $args );
		if ( $payments ) {

			/**
			 * If performing a donation, we need to skip the very last payment in the database,
			 * since it calls give_increase_total_earnings() on completion,
			 * which results in duplicated earnings for the very first donation.
			 */
			if ( did_action( 'give_update_payment_status' ) ) {
				array_pop( $payments );
			}

			if ( ! empty( $payments ) ) {
				$payments = implode( ',', $payments );
				$total   += $wpdb->get_var( "SELECT SUM(meta_value) FROM {$meta_table['name']} WHERE meta_key = '_give_payment_total' AND {$meta_table['column']['id']} IN({$payments})" );
			}
		}

		update_option( 'give_earnings_total', $total, false );
	}

	if ( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings.
	}

	return apply_filters( 'give_total_earnings', round( $total, give_get_price_decimals() ), $total );
}

/**
 * Increase the Total Earnings
 *
 * @param int $amount The amount you would like to increase the total earnings by. Default is 0.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function give_increase_total_earnings( $amount = 0 ) {
	$total  = give_get_total_earnings();
	$total += $amount;
	update_option( 'give_earnings_total', $total, false );

	return $total;
}

/**
 * Decrease the Total Earnings
 *
 * @param int $amount The amount you would like to decrease the total earnings by.
 *
 * @since 1.0
 *
 * @return float $total Total earnings.
 */
function give_decrease_total_earnings( $amount = 0 ) {
	$total  = give_get_total_earnings();
	$total -= $amount;
	if ( $total < 0 ) {
		$total = 0;
	}
	update_option( 'give_earnings_total', $total, false );

	return $total;
}

/**
 * Get Payment Meta for a specific Payment
 *
 * @param int    $payment_id Payment ID.
 * @param string $meta_key   The meta key to pull.
 * @param bool   $single     Pull single meta entry or as an object.
 *
 * @since 1.0
 *
 * @return mixed $meta Payment Meta.
 */
function give_get_payment_meta( $payment_id = 0, $meta_key = '_give_payment_meta', $single = true ) {
	return give_get_meta( $payment_id, $meta_key, $single );
}

/**
 * Update the meta for a payment
 *
 * @param  int    $payment_id Payment ID.
 * @param  string $meta_key   Meta key to update.
 * @param  string $meta_value Value to update to.
 * @param  string $prev_value Previous value.
 *
 * @return mixed Meta ID if successful, false if unsuccessful.
 */
function give_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {
	return give_update_meta( $payment_id, $meta_key, $meta_value );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return array $user_info User Info Meta Values.
 */
function give_get_payment_meta_user_info( $payment_id ) {
	$donor_id   = 0;
	$donor_info = [
		'first_name' => give_get_meta( $payment_id, '_give_donor_billing_first_name', true ),
		'last_name'  => give_get_meta( $payment_id, '_give_donor_billing_last_name', true ),
		'email'      => give_get_meta( $payment_id, '_give_donor_billing_donor_email', true ),
	];

	if ( empty( $donor_info['first_name'] ) ) {
		$donor_id                 = give_get_payment_donor_id( $payment_id );
		$donor_info['first_name'] = Give()->donor_meta->get_meta( $donor_id, '_give_donor_first_name', true );
	}

	if ( empty( $donor_info['last_name'] ) ) {
		$donor_id                = $donor_id ? $donor_id : give_get_payment_donor_id( $payment_id );
		$donor_info['last_name'] = Give()->donor_meta->get_meta( $donor_id, '_give_donor_last_name', true );
	}

	if ( empty( $donor_info['email'] ) ) {
		$donor_id            = $donor_id ? $donor_id : give_get_payment_donor_id( $payment_id );
		$donor_info['email'] = Give()->donors->get_column_by( 'email', 'id', $donor_id );
	}

	$donor_info['title'] = Give()->donor_meta->get_meta( $donor_id, '_give_donor_title_prefix', true );

	$donor_info['address']  = give_get_donation_address( $payment_id );
	$donor_info['id']       = give_get_payment_user_id( $payment_id );
	$donor_info['donor_id'] = give_get_payment_donor_id( $payment_id );

	return $donor_info;
}

/**
 * Get the donations Key from Payment Meta
 *
 * Retrieves the form_id from a (Previously titled give_get_payment_meta_donations)
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return int $form_id Form ID.
 */
function give_get_payment_form_id( $payment_id ) {
	return (int) give_get_meta( $payment_id, '_give_payment_form_id', true );
}

/**
 * Get the user email associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $email User email.
 */
function give_get_payment_user_email( $payment_id ) {
	$email = give_get_meta( $payment_id, '_give_payment_donor_email', true );

	if ( empty( $email ) && ( $donor_id = give_get_payment_donor_id( $payment_id ) ) ) {
		$email = Give()->donors->get_column( 'email', $donor_id );
	}

	return $email;
}

/**
 * Is the payment provided associated with a user account
 *
 * @param int $payment_id The payment ID.
 *
 * @since 1.3
 *
 * @return bool $is_guest_payment If the payment is associated with a user (false) or not (true)
 */
function give_is_guest_payment( $payment_id ) {
	$payment_user_id  = give_get_payment_user_id( $payment_id );
	$is_guest_payment = ! empty( $payment_user_id ) && $payment_user_id > 0 ? false : true;

	return (bool) apply_filters( 'give_is_guest_payment', $is_guest_payment, $payment_id );
}

/**
 * Get the user ID associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.3
 *
 * @return int $user_id User ID.
 */
function give_get_payment_user_id( $payment_id ) {
	global $wpdb;
	$paymentmeta_table        = Give()->payment_meta->table_name;
	$donationmeta_primary_key = Give()->payment_meta->get_meta_type() . '_id';

	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"
			SELECT user_id
			FROM $wpdb->donors
			WHERE id=(
				SELECT meta_value
				FROM $paymentmeta_table
				WHERE {$donationmeta_primary_key}=%s
				AND meta_key=%s
			)
			",
			$payment_id,
			'_give_payment_donor_id'
		)
	);
}

/**
 * Get the donor ID associated with a payment.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return int $payment->customer_id Donor ID.
 */
function give_get_payment_donor_id( $payment_id ) {
	return give_get_meta( $payment_id, '_give_payment_donor_id', true );
}

/**
 * Get the donor email associated with a donation.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 2.1.0
 *
 * @return string
 */
function give_get_donation_donor_email( $payment_id ) {
	return give_get_meta( $payment_id, '_give_payment_donor_email', true );
}

/**
 * Get the IP address used to make a donation
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $ip User IP.
 */
function give_get_payment_user_ip( $payment_id ) {
	return give_get_meta( $payment_id, '_give_payment_donor_ip', true );
}

/**
 * Get the date a payment was completed
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $date The date the payment was completed.
 */
function give_get_payment_completed_date( $payment_id = 0 ) {
	return give_get_meta( $payment_id, '_give_completed_date', true );
}

/**
 * Get the gateway associated with a payment
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $gateway Gateway.
 */
function give_get_payment_gateway( $payment_id ) {
	return give_get_meta( $payment_id, '_give_payment_gateway', true );
}

/**
 * Check if donation have specific gateway or not
 *
 * @since 2.1.0
 *
 * @param int|Give_Payment $donation_id Donation ID
 * @param string           $gateway_id  Gateway ID
 *
 * @return bool
 */
function give_has_payment_gateway( $donation_id, $gateway_id ) {
	$donation_gateway = $donation_id instanceof Give_Payment ?
		$donation_id->gateway :
		give_get_payment_gateway( $donation_id );

	return $gateway_id === $donation_gateway;
}

/**
 * Get the currency code a payment was made in
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $currency The currency code.
 */
function give_get_payment_currency_code( $payment_id = 0 ) {
	return give_get_meta( $payment_id, '_give_payment_currency', true );
}

/**
 * Get the currency name a payment was made in
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $currency The currency name.
 */
function give_get_payment_currency( $payment_id = 0 ) {
	$currency = give_get_payment_currency_code( $payment_id );

	return apply_filters( 'give_payment_currency', give_get_currency_name( $currency ), $payment_id );
}

/**
 * Get the key for a donation
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $key Donation key.
 */
function give_get_payment_key( $payment_id = 0 ) {
	return give_get_meta( $payment_id, '_give_payment_purchase_key', true );
}

/**
 * Get the payment order number
 *
 * This will return the payment ID if sequential order numbers are not enabled or the order number does not exist
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.0
 *
 * @return string $number Payment order number.
 */
function give_get_payment_number( $payment_id = 0 ) {
	return Give()->seq_donation_number->get_serial_code( $payment_id );
}


/**
 * Get Donation Amount
 *
 * Get the fully formatted or unformatted donation amount which is sent through give_currency_filter()
 * and give_format_amount() to format the amount correctly in case of formatted amount.
 *
 * @param int|Give_Payment $donation_id Donation ID or Donation Object.
 * @param bool|array       $format_args Currency Formatting Arguments.
 *
 * @since 1.0
 * @since 1.8.17 Added filter and internally use functions.
 *
 * @return string $amount Fully formatted donation amount.
 */
function give_donation_amount( $donation_id, $format_args = [] ) {
	if ( ! $donation_id ) {
		return '';
	} elseif ( ! is_numeric( $donation_id ) && ( $donation_id instanceof Give_Payment ) ) {
		$donation_id = $donation_id->ID;
	}

	$amount        = $formatted_amount = give_get_payment_total( $donation_id );
	$currency_code = give_get_payment_currency_code( $donation_id );

	if ( is_bool( $format_args ) ) {
		$format_args = [
			'currency' => (bool) $format_args,
			'amount'   => (bool) $format_args,
		];
	}

	$format_args = wp_parse_args(
		$format_args,
		[
			'currency' => false,
			'amount'   => false,

			// Define context of donation amount, by default keep $type as blank.
			// Pass as 'stats' to calculate donation report on basis of base amount for the Currency-Switcher Add-on.
			// For Eg. In Currency-Switcher add on when donation has been made through
			// different currency other than base currency, in that case for correct
			// report calculation based on base currency we will need to return donation
			// base amount and not the converted amount .
			'type'     => '',
		]
	);

	if ( $format_args['amount'] || $format_args['currency'] ) {

		if ( $format_args['amount'] ) {

			$formatted_amount = give_format_amount(
				$amount,
				! is_array( $format_args['amount'] ) ?
					[
						'sanitize' => false,
						'currency' => $currency_code,
					] :
					$format_args['amount']
			);
		}

		if ( $format_args['currency'] ) {
			$formatted_amount = give_currency_filter(
				$formatted_amount,
				! is_array( $format_args['currency'] ) ?
					[ 'currency_code' => $currency_code ] :
					$format_args['currency']
			);
		}
	}

	/**
	 * Filter Donation amount.
	 *
	 * @since 1.8.17
	 *
	 * @param string $formatted_amount Formatted/Un-formatted amount.
	 * @param float  $amount           Donation amount.
	 * @param int    $donation_id      Donation ID.
	 * @param string $type             Donation amount type.
	 */
	return apply_filters( 'give_donation_amount', (string) $formatted_amount, $amount, $donation_id, $format_args );
}

/**
 * Payment Subtotal
 *
 * Retrieves subtotal for payment and then returns a full formatted amount. This
 * function essentially calls give_get_payment_subtotal()
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.5
 *
 * @see   give_get_payment_subtotal()
 *
 * @return array Fully formatted payment subtotal.
 */
function give_payment_subtotal( $payment_id = 0 ) {
	$subtotal = give_get_payment_subtotal( $payment_id );

	return give_currency_filter( give_format_amount( $subtotal, [ 'sanitize' => false ] ), [ 'currency_code' => give_get_payment_currency_code( $payment_id ) ] );
}

/**
 * Get Payment Subtotal
 *
 * Retrieves subtotal for payment and then returns a non formatted amount.
 *
 * @param int $payment_id Payment ID.
 *
 * @since 1.5
 *
 * @return float $subtotal Subtotal for payment (non formatted).
 */
function give_get_payment_subtotal( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->subtotal;
}

/**
 * Retrieves the donation ID
 *
 * @param int $payment_id Payment ID.
 *
 * @since  1.0
 *
 * @return string The donation ID.
 */
function give_get_payment_transaction_id( $payment_id = 0 ) {
	$transaction_id = give_get_meta( $payment_id, '_give_payment_transaction_id', true );

	if ( empty( $transaction_id ) ) {
		$gateway        = give_get_payment_gateway( $payment_id );
		$transaction_id = apply_filters( "give_get_payment_transaction_id-{$gateway}", $payment_id );
	}

	return $transaction_id;
}

/**
 * Sets a Transaction ID in post meta for the given Payment ID.
 *
 * @param int    $payment_id     Payment ID.
 * @param string $transaction_id The transaction ID from the gateway.
 *
 * @since  1.0
 *
 * @return bool|mixed
 */
function give_set_payment_transaction_id( $payment_id = 0, $transaction_id = '' ) {

	if ( empty( $payment_id ) || empty( $transaction_id ) ) {
		return false;
	}

	$transaction_id = apply_filters( 'give_set_payment_transaction_id', $transaction_id, $payment_id );

	return give_update_payment_meta( $payment_id, '_give_payment_transaction_id', $transaction_id );
}

/**
 * Retrieve the donation ID based on the key
 *
 * @param string $key  the key to search for.
 *
 * @since 1.0
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @return int $purchase Donation ID.
 */
function give_get_donation_id_by_key( $key ) {
	global $wpdb;

	$meta_table = __give_v20_bc_table_details( 'payment' );

	$purchase = $wpdb->get_var(
		$wpdb->prepare(
			"
				SELECT {$meta_table['column']['id']}
				FROM {$meta_table['name']}
				WHERE meta_key = '_give_payment_purchase_key'
				AND meta_value = %s
				ORDER BY {$meta_table['column']['id']} DESC
				LIMIT 1
				",
			$key
		)
	);

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}


/**
 * Retrieve the donation ID based on the transaction ID
 *
 * @param string $key  The transaction ID to search for.
 *
 * @since 1.3
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @return int $purchase Donation ID.
 */
function give_get_purchase_id_by_transaction_id( $key ) {
	global $wpdb;
	$meta_table = __give_v20_bc_table_details( 'payment' );

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT {$meta_table['column']['id']} FROM {$meta_table['name']} WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}

/**
 * Retrieve all notes attached to a donation
 *
 * @param int    $payment_id The donation ID to retrieve notes for.
 * @param string $search     Search for notes that contain a search term.
 *
 * @since 1.0
 *
 * @return array $notes Donation Notes
 */
function give_get_payment_notes( $payment_id = 0, $search = '' ) {
	return Give_Comment::get( $payment_id, 'payment', [], $search );
}


/**
 * Add a note to a payment
 *
 * @param int    $payment_id The payment ID to store a note for.
 * @param string $note       The note to store.
 *
 * @since 1.0
 *
 * @return int The new note ID
 */
function give_insert_payment_note( $payment_id = 0, $note = '' ) {
	return Give_Comment::add( $payment_id, $note, 'payment' );
}

/**
 * Deletes a payment note
 *
 * @param int $comment_id The comment ID to delete.
 * @param int $payment_id The payment ID the note is connected to.
 *
 * @since 1.0
 *
 * @return bool True on success, false otherwise.
 */
function give_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	return Give_Comment::delete( $comment_id, $payment_id, 'payment' );
}

/**
 * Gets the payment note HTML
 *
 * @param object|int $note       The comment object or ID.
 * @param int        $payment_id The payment ID the note is connected to.
 *
 * @since 1.0
 *
 * @return string
 */
function give_get_payment_note_html( $note, $payment_id = 0 ) {

	if ( is_numeric( $note ) ) {
		if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {
			$note = get_comment( $note );
		} else {
			$note = Give()->comment->db->get( $note );
		}
	}

	if ( ! empty( $note->user_id ) ) {
		$user = get_userdata( $note->user_id );
		$user = $user->display_name;
	} else {
		$user = __( 'System', 'give' );
	}

	$date_format = give_date_format() . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url(
		add_query_arg(
			[
				'give-action' => 'delete_payment_note',
				'note_id'     => $note->comment_ID,
				'payment_id'  => $payment_id,
			]
		),
		'give_delete_payment_note_' . $note->comment_ID
	);

	$note_html  = '<div class="give-payment-note" id="give-payment-note-' . $note->comment_ID . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '</span><br/>';
	$note_html .= nl2br( $note->comment_content );
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="give-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '" aria-label="' . __( 'Delete this donation note.', 'give' ) . '">' . __( 'Delete', 'give' ) . '</a>';
	$note_html .= '</p>';
	$note_html .= '</div>';

	return $note_html;

}


/**
 * Filter where older than one week
 *
 * @param string $where Where clause.
 *
 * @access public
 * @since  1.0
 *
 * @return string $where Modified where clause.
 */
function give_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week.
	$start  = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";

	return $where;
}


/**
 * Get Payment Form ID.
 *
 * Retrieves the form title and appends the level name if present.
 *
 * @param int|Give_Payment $donation_id Donation Data Object.
 * @param array            $args     a. only_level = If set to true will only return the level name if multi-level
 *                                   enabled. b. separator  = The separator between the Form Title and the Donation
 *                                   Level.
 *
 * @since 1.5
 *
 * @return string $form_title Returns the full title if $only_level is false, otherwise returns the levels title.
 */
function give_get_donation_form_title( $donation_id, $args = [] ) {
	// Backward compatibility.
	if ( ! is_numeric( $donation_id ) && $donation_id instanceof Give_Payment ) {
		$donation_id = $donation_id->ID;
	}

	if ( ! $donation_id ) {
		return '';
	}

	$defaults = [
		'only_level' => false,
		'separator'  => '',
	];

	$args = wp_parse_args( $args, $defaults );

	$form_id     = give_get_payment_form_id( $donation_id );
	$price_id    = give_get_meta( $donation_id, '_give_payment_price_id', true );
	$form_title  = give_get_meta( $donation_id, '_give_payment_form_title', true );
	$only_level  = $args['only_level'];
	$separator   = $args['separator'];
	$level_label = '';

	$cache_key = Give_Cache::get_key(
		'give_forms',
		[
			$form_id,
			$price_id,
			$form_title,
			$only_level,
			$separator,
		],
		false
	);

	$form_title_html = Give_Cache::get_db_query( $cache_key );

	if ( is_null( $form_title_html ) ) {
		if ( true === $only_level ) {
			$form_title = '';
		}

		$form_title_html = $form_title;

		if ( 'custom' === $price_id ) {

			$custom_amount_text = give_get_meta( $form_id, '_give_custom_amount_text', true );
			$level_label        = ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give' );

			// Show custom amount level only in backend otherwise hide it.
			if ( 'set' === give_get_meta( $form_id, '_give_price_option', true ) && ! is_admin() ) {
				$level_label = '';
			}
		} elseif ( give_has_variable_prices( $form_id ) ) {
			$level_label = give_get_price_option_name( $form_id, $price_id, $donation_id, false );
		}

		// Only add separator if there is a form title.
		if (
			! empty( $form_title_html ) &&
			! empty( $level_label )
		) {
			$form_title_html .= " {$separator} ";
		}

		$form_title_html .= "<span class=\"donation-level-text-wrap\">{$level_label}</span>";
		Give_Cache::set_db_query( $cache_key, $form_title_html );
	}

	/**
	 * Filter form title with level html
	 *
	 * @since 1.0
	 * @todo: remove third param after 2.1.0
	 */
	return apply_filters( 'give_get_donation_form_title', $form_title_html, $donation_id, '' );
}

/**
 * Get Price ID
 *
 * Retrieves the Price ID when provided a proper form ID and price (donation) total
 *
 * @param int    $form_id Form ID.
 * @param string $price   Donation Amount.
 *
 * @return string $price_id
 */
function give_get_price_id( $form_id, $price ) {
	$price_id = null;

	if ( give_has_variable_prices( $form_id ) ) {

		$levels = give_get_meta( $form_id, '_give_donation_levels', true );

		foreach ( $levels as $level ) {

			$level_amount = give_maybe_sanitize_amount( $level['_give_amount'] );

			// Check that this indeed the recurring price.
			if ( $level_amount == $price ) {

				$price_id = $level['_give_id']['level_id'];
				break;

			}
		}

		if ( is_null( $price_id ) && give_is_custom_price_mode( $form_id ) ) {
			$price_id = 'custom';
		}
	}

	// Price ID must be numeric or string.
	$price_id = ! is_numeric( $price_id ) && ! is_string( $price_id ) ? 0 : $price_id;

	/**
	 * Filter the price id
	 *
	 * @since 2.0
	 *
	 * @param string $price_id
	 * @param int    $form_id
	 */
	return apply_filters( 'give_get_price_id', $price_id, $form_id );
}

/**
 * Get/Print give form dropdown html
 *
 * This function is wrapper to public method forms_dropdown of Give_HTML_Elements class to get/print form dropdown html.
 * Give_HTML_Elements is defined in includes/class-give-html-elements.php.
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decides if print form dropdown html output or not.
 *
 * @since 1.6
 *
 * @return string
 */
function give_get_form_dropdown( $args = [], $echo = false ) {
	$form_dropdown_html = Give()->html->forms_dropdown( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}

/**
 * Get/Print give form variable price dropdown html
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decide if print form dropdown html output or not.
 *
 * @since 1.6
 *
 * @return string|bool
 */
function give_get_form_variable_price_dropdown( $args = [], $echo = false ) {

	// Check for give form id.
	if ( empty( $args['id'] ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $args['id'] );

	// Check if form has variable prices or not.
	if ( ! $form->ID || ! $form->has_variable_prices() ) {
		return false;
	}

	$variable_prices        = $form->get_prices();
	$variable_price_options = [];

	// Check if multi donation form support custom donation or not.
	if ( $form->is_custom_price_mode() ) {
		$variable_price_options['custom'] = _x( 'Custom', 'custom donation dropdown item', 'give' );
	}

	// Get variable price and ID from variable price array.
	foreach ( $variable_prices as $variable_price ) {
		$variable_price_options[ $variable_price['_give_id']['level_id'] ] = ! empty( $variable_price['_give_text'] ) ? $variable_price['_give_text'] : give_currency_filter( give_format_amount( $variable_price['_give_amount'], [ 'sanitize' => false ] ) );
	}

	// Update options.
	$args = array_merge(
		$args,
		[
			'options' => $variable_price_options,
		]
	);

	// Generate select html.
	$form_dropdown_html = Give()->html->select( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}

/**
 * Get the price_id from the payment meta.
 *
 * Some gateways use `give_price_id` and others were using just `price_id`;
 * This checks for the difference and falls back to retrieving it from the form as a last resort.
 *
 * @param array $payment_meta Payment Meta.
 *
 * @since 1.8.6
 *
 * @return string
 */
function give_get_payment_meta_price_id( $payment_meta ) {

	if ( isset( $payment_meta['give_price_id'] ) ) {
		$price_id = $payment_meta['give_price_id'];
	} elseif ( isset( $payment_meta['price_id'] ) ) {
		$price_id = $payment_meta['price_id'];
	} else {
		$price_id = give_get_price_id( $payment_meta['give_form_id'], $payment_meta['price'] );
	}

	/**
	 * Filter the price id
	 *
	 * @since 1.8.6
	 *
	 * @param string $price_id
	 * @param array  $payment_meta
	 */
	return apply_filters( 'give_get_payment_meta_price_id', $price_id, $payment_meta );

}


/**
 * Get payment total amount
 *
 * @since 2.1.0
 *
 * @param int $payment_id
 *
 * @return float
 */
function give_get_payment_total( $payment_id = 0 ) {
	return round(
		floatval( give_get_meta( $payment_id, '_give_payment_total', true ) ),
		give_get_price_decimals( $payment_id )
	);
}

/**
 * Get donation address
 *
 * since 2.1.0
 *
 * @param int $donation_id
 *
 * @return array
 */
function give_get_donation_address( $donation_id ) {
	$address['line1']   = give_get_meta( $donation_id, '_give_donor_billing_address1', true, '' );
	$address['line2']   = give_get_meta( $donation_id, '_give_donor_billing_address2', true, '' );
	$address['city']    = give_get_meta( $donation_id, '_give_donor_billing_city', true, '' );
	$address['state']   = give_get_meta( $donation_id, '_give_donor_billing_state', true, '' );
	$address['zip']     = give_get_meta( $donation_id, '_give_donor_billing_zip', true, '' );
	$address['country'] = give_get_meta( $donation_id, '_give_donor_billing_country', true, '' );

	return $address;
}


/**
 *  Check if donation completed or not
 *
 * @since 2.1.0
 *
 * @param int $donation_id
 *
 * @return bool
 */
function give_is_donation_completed( $donation_id ) {
	global $wpdb;

	/**
	 * Filter the flag
	 *
	 * @since 2.1.0
	 *
	 * @param bool
	 * @param int $donation_id
	 */
	return apply_filters(
		'give_is_donation_completed',
		(bool) $wpdb->get_var(
			$wpdb->prepare(
				"
				SELECT meta_value
				FROM {$wpdb->donationmeta}
				WHERE EXISTS (
					SELECT ID
					FROM {$wpdb->posts}
					WHERE post_status=%s
					AND ID=%d
				)
				AND {$wpdb->donationmeta}.meta_key=%s
				",
				'publish',
				$donation_id,
				'_give_completed_date'
			)
		),
		$donation_id
	);
}

/**
 * Verify if donation anonymous or not
 *
 * @since 2.2.1
 * @param $donation_id
 *
 * @return bool
 */
function give_is_anonymous_donation( $donation_id ) {
	$value = false;

	if ( (int) give_get_meta( $donation_id, '_give_anonymous_donation', true ) ) {
		$value = true;
	}

	return $value;
}
