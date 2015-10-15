<?php
/**
 * Payment Functions
 *
 * @package     Give
 * @subpackage  Payments
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-1.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
 * $offset = 0, $number = 20, $mode = 'live', $orderby = 'ID', $order = 'DESC',
 * $user = null, $status = 'any', $meta_key = null
 *
 * @since 1.0
 *
 * @param array $args Arguments passed to get payments
 *
 * @return object $payments Payments retrieved from the database
 */
function give_get_payments( $args = array() ) {

	// Fallback to post objects to ensure backwards compatibility
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
 * @since       1.0
 *
 * @param       string $field The field to retrieve the payment with
 * @param       mixed  $value The value for $field
 *
 * @return      mixed
 */
function give_get_payment_by( $field = '', $value = '' ) {

	if ( empty( $field ) || empty( $value ) ) {
		return false;
	}

	switch ( strtolower( $field ) ) {

		case 'id':
			$payment = get_post( $value );

			if ( get_post_type( $payment ) != 'give_payment' ) {
				return false;
			}

			break;

		case 'key':
			$payment = give_get_payments( array(
				'meta_key'       => '_give_payment_purchase_key',
				'meta_value'     => $value,
				'posts_per_page' => 1
			) );

			if ( $payment ) {
				$payment = $payment[0];
			}

			break;

		case 'payment_number':
			$payment = give_get_payments( array(
				'meta_key'       => '_give_payment_number',
				'meta_value'     => $value,
				'posts_per_page' => 1
			) );

			if ( $payment ) {
				$payment = $payment[0];
			}

			break;

		default:
			return false;
	}

	if ( $payment ) {
		return $payment;
	}

	return false;
}

/**
 * Insert Payment
 *
 * @since 1.0
 *
 * @param array $payment_data
 *
 * @return int|bool Payment ID if payment is inserted, false otherwise
 */
function give_insert_payment( $payment_data = array() ) {
	if ( empty( $payment_data ) ) {
		return false;
	}

	// Make sure the payment is inserted with the correct timezone
	date_default_timezone_set( give_get_timezone_id() );

	// Construct the payment title
	if ( isset( $payment_data['user_info']['first_name'] ) || isset( $payment_data['user_info']['last_name'] ) ) {
		$payment_title = $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'];
	} else {
		$payment_title = $payment_data['user_email'];
	}

	// Find the next payment number, if enabled
	if ( give_get_option( 'enable_sequential' ) ) {
		$number = give_get_next_payment_number();
	}

	$args = apply_filters( 'give_insert_payment_args', array(
		'post_title'    => $payment_title,
		'post_status'   => isset( $payment_data['status'] ) ? $payment_data['status'] : 'pending',
		'post_type'     => 'give_payment',
		'post_parent'   => isset( $payment_data['parent'] ) ? $payment_data['parent'] : null,
		'post_date'     => isset( $payment_data['post_date'] ) ? $payment_data['post_date'] : null,
		'post_date_gmt' => isset( $payment_data['post_date'] ) ? get_gmt_from_date( $payment_data['post_date'] ) : null
	), $payment_data );

	// Create a blank payment
	$payment = wp_insert_post( $args );

	if ( $payment ) {

		$payment_meta = array(
			'currency'   => $payment_data['currency'],
			'form_title' => $payment_data['give_form_title'],
			'form_id'    => $payment_data['give_form_id'],
			'price_id'   => give_get_price_id( $payment_data['give_form_id'], $payment_data['price'] ),
			'user_info'  => $payment_data['user_info'],
		);

		$mode    = give_is_test_mode() ? 'test' : 'live';
		$gateway = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';
		$gateway = empty( $gateway ) && isset( $_POST['give-gateway'] ) ? $_POST['give-gateway'] : $gateway;

		if ( ! $payment_data['price'] ) {
			// Ensures the _give_payment_total meta key is created for donations with an amount of 0
			$payment_data['price'] = '0.00';
		}

		// Create or update a customer
		$customer      = new Give_Customer( $payment_data['user_email'] );
		$customer_data = array(
			'name'    => $payment_data['user_info']['first_name'] . ' ' . $payment_data['user_info']['last_name'],
			'email'   => $payment_data['user_email'],
			'user_id' => $payment_data['user_info']['id']
		);

		if ( empty( $customer->id ) ) {
			$customer->create( $customer_data );
		} else {
			// Only update the customer if their name or email has changed
			if ( $customer_data['email'] !== $customer->email || $customer_data['name'] !== $customer->name ) {
				// We shouldn't be updating the User ID here, that is an admin task
				unset( $customer_data['user_id'] );
				$customer->update( $customer_data );
			}
		}

		$customer->attach_payment( $payment, false );

		// Record the payment details
		give_update_payment_meta( $payment, '_give_payment_meta', apply_filters( 'give_payment_meta', $payment_meta, $payment_data ) );
		give_update_payment_meta( $payment, '_give_payment_user_id', $payment_data['user_info']['id'] );
		give_update_payment_meta( $payment, '_give_payment_donor_id', $customer->id );
		give_update_payment_meta( $payment, '_give_payment_user_email', $payment_data['user_email'] );
		give_update_payment_meta( $payment, '_give_payment_user_ip', give_get_ip() );
		give_update_payment_meta( $payment, '_give_payment_purchase_key', $payment_data['purchase_key'] );
		give_update_payment_meta( $payment, '_give_payment_total', $payment_data['price'] );
		give_update_payment_meta( $payment, '_give_payment_mode', $mode );
		give_update_payment_meta( $payment, '_give_payment_gateway', $gateway );

		if ( give_get_option( 'enable_sequential' ) ) {
			give_update_payment_meta( $payment, '_give_payment_number', give_format_payment_number( $number ) );
			update_option( 'give_last_payment_number', $number );
		}

		// Clear the user's purchased cache
		delete_transient( 'give_user_' . $payment_data['user_info']['id'] . '_purchases' );

		do_action( 'give_insert_payment', $payment, $payment_data );

		return $payment; // Return the ID

	}

	// Return false if no payment was inserted
	return false;
}

/**
 * Updates a payment status.
 *
 * @since 1.0
 *
 * @param int    $payment_id Payment ID
 * @param string $new_status New Payment Status (default: publish)
 *
 * @return void
 */
function give_update_payment_status( $payment_id, $new_status = 'publish' ) {

	if ( $new_status == 'completed' || $new_status == 'complete' ) {
		$new_status = 'publish';
	}

	if ( empty( $payment_id ) ) {
		return;
	}

	$payment = get_post( $payment_id );

	if ( is_wp_error( $payment ) || ! is_object( $payment ) ) {
		return;
	}

	$old_status = $payment->post_status;

	if ( $old_status === $new_status ) {
		return; // Don't permit status changes that aren't changes
	}

	$do_change = apply_filters( 'give_should_update_payment_status', true, $payment_id, $new_status, $old_status );

	if ( $do_change ) {

		do_action( 'give_before_payment_status_change', $payment_id, $new_status, $old_status );

		$update_fields = array(
			'ID'          => $payment_id,
			'post_status' => $new_status,
			'edit_date'   => current_time( 'mysql' )
		);

		wp_update_post( apply_filters( 'give_update_payment_status_fields', $update_fields ) );

		do_action( 'give_update_payment_status', $payment_id, $new_status, $old_status );

	}
}

/**
 * Deletes a Donation
 *
 * @since 1.0
 * @global    $give_logs
 * @uses  Give_Logging::delete_logs()
 *
 * @param int $payment_id Payment ID (default: 0)
 *
 * @return void
 */
function give_delete_purchase( $payment_id = 0 ) {
	global $give_logs;

	$post = get_post( $payment_id );

	if ( ! $post ) {
		return;
	}

	$form_id = give_get_payment_form_id( $payment_id );

	give_undo_purchase( $form_id, $payment_id );

	$amount   = give_get_payment_amount( $payment_id );
	$status   = $post->post_status;
	$donor_id = give_get_payment_customer_id( $payment_id );

	if ( $status == 'revoked' || $status == 'publish' ) {
		// Only decrease earnings if they haven't already been decreased (or were never increased for this payment)
		give_decrease_total_earnings( $amount );
		// Clear the This Month earnings (this_monththis_month is NOT a typo)
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );

		if ( $donor_id ) {

			// Decrement the stats for the donor
			Give()->customers->decrement_stats( $donor_id, $amount );

		}
	}

	do_action( 'give_payment_delete', $payment_id );

	if ( $donor_id ) {

		// Remove the payment ID from the donor
		Give()->customers->remove_payment( $donor_id, $payment_id );

	}

	// Remove the payment
	wp_delete_post( $payment_id, true );

	// Remove related sale log entries
	$give_logs->delete_logs(
		null,
		'sale',
		array(
			array(
				'key'   => '_give_log_payment_id',
				'value' => $payment_id
			)
		)
	);

	do_action( 'give_payment_deleted', $payment_id );
}

/**
 * Undoes a donation, including the decrease of donations and earning stats. Used for when refunding or deleting a donation
 *
 * @since 1.0
 *
 * @param int $form_id    Form (Post) ID
 * @param int $payment_id Payment ID
 *
 * @return void
 */
function give_undo_purchase( $form_id, $payment_id ) {

	if ( give_is_test_mode() ) {
		return;
	}

	$amount = give_get_payment_amount( $payment_id );

	// decrease earnings
	give_decrease_earnings( $form_id, $amount );

	// decrease purchase count
	give_decrease_purchase_count( $form_id );


}


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @since 1.0
 *
 * @param array $args
 *
 * @return array $count Number of payments sorted by payment status
 */
function give_count_payments( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'user'       => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$join  = '';
	$where = "WHERE p.post_type = 'give_payment'";

	// Count payments for a specific user
	if ( ! empty( $args['user'] ) ) {

		if ( is_email( $args['user'] ) ) {
			$field = 'email';
		} elseif ( is_numeric( $args['user'] ) ) {
			$field = 'id';
		} else {
			$field = '';
		}

		$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";

		if ( ! empty( $field ) ) {
			$where .= "
				AND m.meta_key = '_give_payment_user_{$field}'
				AND m.meta_value = '{$args['user']}'";
		}

		// Count payments for a search
	} elseif ( ! empty( $args['s'] ) ) {

		if ( is_email( $args['s'] ) || strlen( $args['s'] ) == 32 ) {

			if ( is_email( $args['s'] ) ) {
				$field = '_give_payment_user_email';
			} else {
				$field = '_give_payment_purchase_key';
			}


			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= "
				AND m.meta_key = '{$field}'
				AND m.meta_value = '{$args['s']}'";

		} elseif ( is_numeric( $args['s'] ) ) {

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= "
				AND m.meta_key = '_give_payment_user_id'
				AND m.meta_value = '{$args['s']}'";

		} else {
			$where .= "AND ((p.post_title LIKE '%{$args['s']}%') OR (p.post_content LIKE '%{$args['s']}%'))";
		}

	}

	// Limit payments count by date
	if ( ! empty( $args['start-date'] ) && false !== strpos( $args['start-date'], '/' ) ) {

		$date_parts = explode( '/', $args['start-date'] );
		$month      = ! empty( $date_parts[0] ) && is_numeric( $date_parts[0] ) ? $date_parts[0] : 0;
		$day        = ! empty( $date_parts[1] ) && is_numeric( $date_parts[1] ) ? $date_parts[1] : 0;
		$year       = ! empty( $date_parts[2] ) && is_numeric( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date = new DateTime( $args['start-date'] );
			$where .= $wpdb->prepare( " AND p.post_date >= '%s'", $date->format( 'Y-m-d' ) );

		}

		// Fixes an issue with the payments list table counts when no end date is specified (partiy with stats class)
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}

	}

	if ( ! empty ( $args['end-date'] ) && false !== strpos( $args['end-date'], '/' ) ) {

		$date_parts = explode( '/', $args['end-date'] );

		$month = ! empty( $date_parts[0] ) ? $date_parts[0] : 0;
		$day   = ! empty( $date_parts[1] ) ? $date_parts[1] : 0;
		$year  = ! empty( $date_parts[2] ) ? $date_parts[2] : 0;

		$is_date = checkdate( $month, $day, $year );
		if ( false !== $is_date ) {

			$date = new DateTime( $args['end-date'] );
			$where .= $wpdb->prepare( " AND p.post_date <= '%s'", $date->format( 'Y-m-d' ) );

		}

	}

	$where = apply_filters( 'give_count_payments_where', $where );
	$join  = apply_filters( 'give_count_payments_join', $join );

	$query = "SELECT p.post_status,count( * ) AS num_posts
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( implode( '|', $args ) . $where );

	$count = wp_cache_get( $cache_key, 'counts' );
	if ( false !== $count ) {
		return $count;
	}

	$count = $wpdb->get_results( $query, ARRAY_A );

	$stats    = array();
	$statuses = get_post_stati();
	if ( isset( $statuses['private'] ) && empty( $args['s'] ) ) {
		unset( $statuses['private'] );
	}

	foreach ( $statuses as $state ) {
		$stats[ $state ] = 0;
	}

	foreach ( (array) $count as $row ) {
		if ( 'private' == $row['post_status'] && empty( $args['s'] ) ) {
			continue;
		}

		$stats[ $row['post_status'] ] = $row['num_posts'];
	}

	$stats = (object) $stats;
	wp_cache_set( $cache_key, $stats, 'counts' );

	return $stats;
}


/**
 * Check For Existing Payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return bool true if payment exists, false otherwise
 */
function give_check_for_existing_payment( $payment_id ) {
	$payment = get_post( $payment_id );

	if ( $payment && $payment->post_status == 'publish' ) {
		return true; // Payment exists
	}

	return false; // This payment doesn't exist
}

/**
 * Get Payment Status
 *
 * @since 1.0
 *
 * @param WP_Post $payment
 * @param bool    $return_label Whether to return the donation status or not
 *
 * @return bool|mixed if payment status exists, false otherwise
 */
function give_get_payment_status( $payment, $return_label = false ) {
	if ( ! is_object( $payment ) || ! isset( $payment->post_status ) ) {
		return false;
	}

	$statuses = give_get_payment_statuses();
	if ( ! is_array( $statuses ) || empty( $statuses ) ) {
		return false;
	}

	if ( array_key_exists( $payment->post_status, $statuses ) ) {
		if ( true === $return_label ) {
			return $statuses[ $payment->post_status ];
		} else {
			return array_search( $payment->post_status, $statuses );
		}
	}

	return false;
}

/**
 * Retrieves all available statuses for payments.
 *
 * @since 1.0
 * @return array $payment_status All the available payment statuses
 */
function give_get_payment_statuses() {
	$payment_statuses = array(
		'pending'     => __( 'Pending', 'give' ),
		'publish'     => __( 'Complete', 'give' ),
		'refunded'    => __( 'Refunded', 'give' ),
		'failed'      => __( 'Failed', 'give' ),
		'cancelled'   => __( 'Cancelled', 'give' ),
		'abandoned'   => __( 'Abandoned', 'give' ),
		'preapproval' => __( 'Pre-Approved', 'give' ),
		'revoked'     => __( 'Revoked', 'give' )
	);

	return apply_filters( 'give_payment_statuses', $payment_statuses );
}

/**
 * Get Payment Status Keys
 *
 * @description Retrieves keys for all available statuses for payments
 *
 * @since       1.0
 * @return array $payment_status All the available payment statuses
 */
function give_get_payment_status_keys() {
	$statuses = array_keys( give_get_payment_statuses() );
	asort( $statuses );

	return array_values( $statuses );
}

/**
 * Get Earnings By Date
 *
 * @since 1.0
 *
 * @param int $day       Day number
 * @param int $month_num Month number
 * @param int $year      Year
 * @param int $hour      Hour
 *
 * @return int $earnings Earnings
 */
function give_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use Give_Payment_Stats with the get_earnings() method instead

	global $wpdb;

	$args = array(
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'monthnum'               => $month_num,
		'post_status'            => array( 'publish', 'revoked' ),
		'fields'                 => 'ids',
		'update_post_term_cache' => false
	);
	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'give_get_earnings_by_date_args', $args );
	$key  = 'give_stats_' . substr( md5( serialize( $args ) ), 0, 15 );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'give-refresh-reports' ) ) {
		$earnings = false;
	} else {
		$earnings = get_transient( $key );
	}

	if ( false === $earnings ) {
		$sales    = get_posts( $args );
		$earnings = 0;
		if ( $sales ) {
			$sales = implode( ',', $sales );
			$earnings += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_give_payment_total' AND post_id IN({$sales})" );

		}
		// Cache the results for one hour
		set_transient( $key, $earnings, HOUR_IN_SECONDS );
	}

	return round( $earnings, 2 );
}

/**
 * Get Donations (sales) By Date
 *
 * @since  1.0
 *
 * @param int $day       Day number
 * @param int $month_num Month number
 * @param int $year      Year
 * @param int $hour      Hour
 *
 * @return int $count Sales
 */
function give_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use Give_Payment_Stats with the get_sales() method instead
	$args = array(
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'fields'                 => 'ids',
		'post_status'            => array( 'publish', 'revoked' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false
	);

	if ( ! empty( $month_num ) ) {
		$args['monthnum'] = $month_num;
	}

	if ( ! empty( $day ) ) {
		$args['day'] = $day;
	}

	if ( ! empty( $hour ) ) {
		$args['hour'] = $hour;
	}

	$args = apply_filters( 'give_get_sales_by_date_args', $args );
	$key   = 'give_stats_' . substr( md5( serialize( $args ) ), 0, 15 );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'give-refresh-reports' ) ) {
		$count = false;
	} else {
		$count = get_transient( $key );
	}

	if ( false === $count ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;
		// Cache the results for one hour
		set_transient( $key, $count, HOUR_IN_SECONDS );
	}

	return $count;
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID to check against
 *
 * @return bool true if complete, false otherwise
 */
function give_is_payment_complete( $payment_id ) {
	$payment = get_post( $payment_id );
	$ret     = false;
	if ( $payment && $payment->post_status == 'publish' ) {
		$ret = true;
	}

	return apply_filters( 'give_is_payment_complete', $ret, $payment_id, $payment->post_status );
}

/**
 * Get Total Sales (Donations)
 *
 * @since 1.0
 * @return int $count Total sales
 */
function give_get_total_sales() {

	$payments = give_count_payments();

	return $payments->revoked + $payments->publish;
}

/**
 * Get Total Earnings
 *
 * @since 1.0
 * @return float $total Total earnings
 */
function give_get_total_earnings() {

	$total = get_option( 'give_earnings_total', 0 );

	// If no total stored in DB, use old method of calculating total earnings
	if ( ! $total ) {

		global $wpdb;

		$total = get_transient( 'give_earnings_total' );

		if ( false === $total ) {

			$total = (float) 0;

			$args = apply_filters( 'give_get_total_earnings_args', array(
				'offset' => 0,
				'number' => - 1,
				'status' => array( 'publish', 'revoked' ),
				'fields' => 'ids'
			) );


			$payments = give_get_payments( $args );
			if ( $payments ) {

				/*
				 * If performing a purchase, we need to skip the very last payment in the database, since it calls
				 * give_increase_total_earnings() on completion, which results in duplicated earnings for the very
				 * first purchase
				 */

				if ( did_action( 'give_update_payment_status' ) ) {
					array_pop( $payments );
				}

				if ( ! empty( $payments ) ) {
					$payments = implode( ',', $payments );
					$total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_give_payment_total' AND post_id IN({$payments})" );
				}

			}

			// Cache results for 1 day. This cache is cleared automatically when a payment is made
			set_transient( 'give_earnings_total', $total, 86400 );

			// Store the total for the first time
			update_option( 'give_earnings_total', $total );
		}
	}

	if ( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings
	}

	return apply_filters( 'give_total_earnings', round( $total, give_currency_decimal_filter() ) );
}

/**
 * Increase the Total Earnings
 *
 * @since 1.0
 *
 * @param $amount int The amount you would like to increase the total earnings by.
 *
 * @return float $total Total earnings
 */
function give_increase_total_earnings( $amount = 0 ) {
	$total = give_get_total_earnings();
	$total += $amount;
	update_option( 'give_earnings_total', $total );

	return $total;
}

/**
 * Decrease the Total Earnings
 *
 * @since 1.0
 *
 * @param $amount int The amount you would like to decrease the total earnings by.
 *
 * @return float $total Total earnings
 */
function give_decrease_total_earnings( $amount = 0 ) {
	$total = give_get_total_earnings();
	$total -= $amount;
	if ( $total < 0 ) {
		$total = 0;
	}
	update_option( 'give_earnings_total', $total );

	return $total;
}

/**
 * Get Payment Meta for a specific Payment
 *
 * @since 1.0
 *
 * @param int    $payment_id Payment ID
 * @param string $meta_key   The meta key to pull
 * @param bool   $single     Pull single meta entry or as an object
 *
 * @return mixed $meta Payment Meta
 */
function give_get_payment_meta( $payment_id = 0, $meta_key = '_give_payment_meta', $single = true ) {

	$meta = get_post_meta( $payment_id, $meta_key, $single );

	if ( $meta_key === '_give_payment_meta' ) {

		if ( empty( $meta['key'] ) ) {
			$meta['key'] = give_get_payment_key( $payment_id );
		}

		if ( empty( $meta['email'] ) ) {
			$meta['email'] = give_get_payment_user_email( $payment_id );
		}

		if ( empty( $meta['date'] ) ) {
			$meta['date'] = get_post_field( 'post_date', $payment_id );
		}
	}

	$meta = apply_filters( 'give_get_payment_meta_' . $meta_key, $meta, $payment_id );

	return apply_filters( 'give_get_payment_meta', $meta, $payment_id, $meta_key );
}

/**
 * Update the meta for a payment
 *
 * @param  integer $payment_id Payment ID
 * @param  string  $meta_key   Meta key to update
 * @param  string  $meta_value Value to update to
 * @param  string  $prev_value Previous value
 *
 * @return mixed               Meta ID if successful, false if unsuccessful
 */
function give_update_payment_meta( $payment_id = 0, $meta_key = '', $meta_value = '', $prev_value = '' ) {

	if ( empty( $payment_id ) || empty( $meta_key ) ) {
		return;
	}

	if ( $meta_key == 'key' || $meta_key == 'date' ) {

		$current_meta              = give_get_payment_meta( $payment_id );
		$current_meta[ $meta_key ] = $meta_value;

		$meta_key   = '_give_payment_meta';
		$meta_value = $current_meta;

	} else if ( $meta_key == 'email' || $meta_key == '_give_payment_user_email' ) {

		$meta_value = apply_filters( 'give_give_update_payment_meta_' . $meta_key, $meta_value, $payment_id );
		update_post_meta( $payment_id, '_give_payment_user_email', $meta_value );

		$current_meta                       = give_get_payment_meta( $payment_id );
		$current_meta['user_info']['email'] = $meta_value;

		$meta_key   = '_give_payment_meta';
		$meta_value = $current_meta;

	}

	$meta_value = apply_filters( 'give_give_update_payment_meta_' . $meta_key, $meta_value, $payment_id );

	return update_post_meta( $payment_id, $meta_key, $meta_value, $prev_value );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return array $user_info User Info Meta Values
 */
function give_get_payment_meta_user_info( $payment_id ) {
	$payment_meta = give_get_payment_meta( $payment_id );
	$user_info    = isset( $payment_meta['user_info'] ) ? maybe_unserialize( $payment_meta['user_info'] ) : false;

	return apply_filters( 'give_payment_meta_user_info', $user_info );
}

/**
 * Get the donations Key from Payment Meta
 *
 * @description Retrieves the form_id from a (Previously titled give_get_payment_meta_donations)
 * @since       1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return int $form_id
 */
function give_get_payment_form_id( $payment_id ) {
	$payment_meta = give_get_payment_meta( $payment_id );

	$form_id = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;

	return apply_filters( 'give_get_payment_form_id', $form_id );
}

/**
 * Get the user email associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $email User Email
 */
function give_get_payment_user_email( $payment_id ) {
	$email = give_get_payment_meta( $payment_id, '_give_payment_user_email', true );

	return apply_filters( 'give_payment_user_email', $email );
}

/**
 * Is the payment provided associated with a user account
 *
 * @since  1.3
 *
 * @param  int $payment_id The payment ID
 *
 * @return bool            If the payment is associted with a user (false) or not (true)
 */
function give_is_guest_payment( $payment_id ) {
	$payment_user_id  = give_get_payment_user_id( $payment_id );
	$is_guest_payment = ! empty( $payment_user_id ) && $payment_user_id > 0 ? false : true;

	return (bool) apply_filters( 'give_is_guest_payment', $is_guest_payment, $payment_id );
}

/**
 * Get the user ID associated with a payment
 *
 * @since 1.3
 *
 * @param int $payment_id Payment ID
 *
 * @return string $user_id User ID
 */
function give_get_payment_user_id( $payment_id ) {

	$user_id = - 1;

	// check the customer record first
	$customer_id = give_get_payment_customer_id( $payment_id );
	$customer    = new Give_Customer( $customer_id );

	if ( ! empty( $customer->user_id ) && $customer->user_id > 0 ) {
		$user_id = $customer->user_id;
	}

	// check the payment meta if we're still not finding a user with the customer record
	if ( empty( $user_id ) || $user_id < 1 ) {
		$payment_meta_user_id = give_get_payment_meta( $payment_id, '_give_payment_user_id', true );

		if ( ! empty( $payment_meta_user_id ) ) {
			$user_id = $payment_meta_user_id;
		}
	}

	// Last ditch effort is to connect payment email with a user in the user table
	if ( empty( $user_id ) || $user_id < 1 ) {
		$payment_email = give_get_payment_user_email( $payment_id );
		$user          = get_user_by( 'email', $payment_email );

		if ( false !== $user ) {
			$user_id = $user->ID;
		}
	}

	return apply_filters( 'give_payment_user_id', (int) $user_id );
}

/**
 * Get the donor ID associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $donor_id Donor ID
 */
function give_get_payment_customer_id( $payment_id ) {
	$customer_id = get_post_meta( $payment_id, '_give_payment_donor_id', true );

	return apply_filters( 'give_payment_donor_id', $customer_id );
}

/**
 * Get the IP address used to make a purchase
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $ip User IP
 */
function give_get_payment_user_ip( $payment_id ) {
	$ip = give_get_payment_meta( $payment_id, '_give_payment_user_ip', true );

	return apply_filters( 'give_payment_user_ip', $ip );
}

/**
 * Get the date a payment was completed
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $date The date the payment was completed
 */
function give_get_payment_completed_date( $payment_id = 0 ) {

	$payment = get_post( $payment_id );

	if ( 'pending' == $payment->post_status || 'preapproved' == $payment->post_status ) {
		return false; // This payment was never completed
	}

	$date = ( $date = give_get_payment_meta( $payment_id, '_give_completed_date', true ) ) ? $date : $payment->modified_date;

	return apply_filters( 'give_payment_completed_date', $date, $payment_id );
}

/**
 * Get the gateway associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $gateway Gateway
 */
function give_get_payment_gateway( $payment_id ) {
	$gateway = give_get_payment_meta( $payment_id, '_give_payment_gateway', true );

	return apply_filters( 'give_payment_gateway', $gateway );
}

/**
 * Get the currency code a payment was made in
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $currency The currency code
 */
function give_get_payment_currency_code( $payment_id = 0 ) {
	$meta     = give_get_payment_meta( $payment_id );
	$currency = isset( $meta['currency'] ) ? $meta['currency'] : give_get_currency();

	return apply_filters( 'give_payment_currency_code', $currency, $payment_id );
}

/**
 * Get the currency name a payment was made in
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $currency The currency name
 */
function give_get_payment_currency( $payment_id = 0 ) {
	$currency = give_get_payment_currency_code( $payment_id );

	return apply_filters( 'give_payment_currency', give_get_currency_name( $currency ), $payment_id );
}

/**
 * Get the purchase key for a purchase
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $key Purchase key
 */
function give_get_payment_key( $payment_id = 0 ) {
	$key = give_get_payment_meta( $payment_id, '_give_payment_purchase_key', true );

	return apply_filters( 'give_payment_key', $key, $payment_id );
}

/**
 * Get the payment order number
 *
 * This will return the payment ID if sequential order numbers are not enabled or the order number does not exist
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $number Payment order number
 */
function give_get_payment_number( $payment_id = 0 ) {

	$number = $payment_id;

	if ( give_get_option( 'enable_sequential' ) ) {

		$number = give_get_payment_meta( $payment_id, '_give_payment_number', true );

		if ( ! $number ) {

			$number = $payment_id;

		}

	}

	return apply_filters( 'give_payment_number', $number, $payment_id );
}

/**
 * Formats the payment number with the prefix and postfix
 *
 * @since  1.3
 *
 * @param  int $number The payment number to format
 *
 * @return string      The formatted payment number
 */
function give_format_payment_number( $number ) {

	if ( ! give_get_option( 'enable_sequential' ) ) {
		return $number;
	}

	if ( ! is_numeric( $number ) ) {
		return $number;
	}

	$prefix  = give_get_option( 'sequential_prefix' );
	$number  = absint( $number );
	$postfix = give_get_option( 'sequential_postfix' );

	$formatted_number = $prefix . $number . $postfix;

	return apply_filters( 'give_format_payment_number', $formatted_number, $prefix, $number, $postfix );
}

/**
 * Gets the next available order number
 *
 * This is used when inserting a new payment
 *
 * @since 1.0
 * @return string $number The next available payment number
 */
function give_get_next_payment_number() {

	if ( ! give_get_option( 'enable_sequential' ) ) {
		return false;
	}

	$number           = get_option( 'give_last_payment_number' );
	$start            = give_get_option( 'sequential_start', 1 );
	$increment_number = true;

	if ( false !== $number ) {

		if ( empty( $number ) ) {

			$number           = $start;
			$increment_number = false;

		}

	} else {

		// This case handles the first addition of the new option, as well as if it get's deleted for any reason
		$payments     = new Give_Payments_Query( array(
			'number'  => 1,
			'order'   => 'DESC',
			'orderby' => 'ID',
			'output'  => 'posts',
			'fields'  => 'ids'
		) );
		$last_payment = $payments->get_payments();

		if ( $last_payment ) {

			$number = give_get_payment_number( $last_payment[0] );

		}

		if ( ! empty( $number ) && $number !== $last_payment[0] ) {

			$number = give_remove_payment_prefix_postfix( $number );

		} else {

			$number           = $start;
			$increment_number = false;
		}

	}

	$increment_number = apply_filters( 'give_increment_payment_number', $increment_number, $number );

	if ( $increment_number ) {
		$number ++;
	}

	return apply_filters( 'give_get_next_payment_number', $number );
}

/**
 * Given a given a number, remove the pre/postfix
 *
 * @since  1.3
 *
 * @param  string $number The formatted Current Number to increment
 *
 * @return string          The new Payment number without prefix and postfix
 */
function give_remove_payment_prefix_postfix( $number ) {

	$prefix  = give_get_option( 'sequential_prefix' );
	$postfix = give_get_option( 'sequential_postfix' );

	// Remove prefix
	$number = preg_replace( '/' . $prefix . '/', '', $number, 1 );

	// Remove the postfix
	$length      = strlen( $number );
	$postfix_pos = strrpos( $number, $postfix );
	if ( false !== $postfix_pos ) {
		$number = substr_replace( $number, '', $postfix_pos, $length );
	}

	// Ensure it's a whole number
	$number = intval( $number );

	return apply_filters( 'give_remove_payment_prefix_postfix', $number, $prefix, $postfix );

}


/**
 * Get Payment Amount
 *
 * @description Get the fully formatted payment amount. The payment amount is retrieved using give_get_payment_amount() and is then sent through give_currency_filter() and  give_format_amount() to format the amount correctly.
 *
 * @since       1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string $amount Fully formatted payment amount
 */
function give_payment_amount( $payment_id = 0 ) {
	$amount = give_get_payment_amount( $payment_id );

	return give_currency_filter( give_format_amount( $amount ), give_get_payment_currency_code( $payment_id ) );
}

/**
 * Get the amount associated with a payment
 *
 * @access public
 * @since  1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return mixed|void
 */
function give_get_payment_amount( $payment_id ) {

	$amount = give_get_payment_meta( $payment_id, '_give_payment_total', true );

	if ( empty( $amount ) && '0.00' != $amount ) {
		$meta = give_get_payment_meta( $payment_id, '_give_payment_meta', true );
		$meta = maybe_unserialize( $meta );

		if ( isset( $meta['amount'] ) ) {
			$amount = $meta['amount'];
		}
	}

	return apply_filters( 'give_payment_amount', floatval( $amount ), $payment_id );
}


/**
 * Retrieves the transaction ID for the given payment
 *
 * @since  1.0
 *
 * @param int $payment_id Payment ID
 *
 * @return string The Transaction ID
 */
function give_get_payment_transaction_id( $payment_id = 0 ) {

	$transaction_id = false;
	$transaction_id = give_get_payment_meta( $payment_id, '_give_payment_transaction_id', true );

	if ( empty( $transaction_id ) ) {

		$gateway        = give_get_payment_gateway( $payment_id );
		$transaction_id = apply_filters( 'give_get_payment_transaction_id-' . $gateway, $payment_id );

	}

	return apply_filters( 'give_get_payment_transaction_id', $transaction_id, $payment_id );
}

/**
 * Sets a Transaction ID in post meta for the given Payment ID
 *
 * @since  1.0
 *
 * @param int    $payment_id     Payment ID
 * @param string $transaction_id The transaction ID from the gateway
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
 * Retrieve the purchase ID based on the purchase key
 *
 * @since 1.0
 * @global object $wpdb Used to query the database using the WordPress
 *                      Database API
 *
 * @param string  $key  the purchase key to search for
 *
 * @return int $purchase Purchase ID
 */
function give_get_purchase_id_by_key( $key ) {
	global $wpdb;

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_give_payment_purchase_key' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}


/**
 * Retrieve the purchase ID based on the transaction ID
 *
 * @since 1.3
 * @global object $wpdb Used to query the database using the WordPress
 *                      Database API
 *
 * @param string  $key  the transaction ID to search for
 *
 * @return int $purchase Purchase ID
 */
function give_get_purchase_id_by_transaction_id( $key ) {
	global $wpdb;

	$purchase = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_give_payment_transaction_id' AND meta_value = %s LIMIT 1", $key ) );

	if ( $purchase != null ) {
		return $purchase;
	}

	return 0;
}

/**
 * Retrieve all notes attached to a purchase
 *
 * @since 1.0
 *
 * @param int    $payment_id The payment ID to retrieve notes for
 * @param string $search     Search for notes that contain a search term
 *
 * @return array $notes Payment Notes
 */
function give_get_payment_notes( $payment_id = 0, $search = '' ) {

	if ( empty( $payment_id ) && empty( $search ) ) {
		return false;
	}

	remove_action( 'pre_get_comments', 'give_hide_payment_notes', 10 );
	remove_filter( 'comments_clauses', 'give_hide_payment_notes_pre_41', 10, 2 );

	$notes = get_comments( array( 'post_id' => $payment_id, 'order' => 'ASC', 'search' => $search ) );

	add_action( 'pre_get_comments', 'give_hide_payment_notes', 10 );
	add_filter( 'comments_clauses', 'give_hide_payment_notes_pre_41', 10, 2 );

	return $notes;
}


/**
 * Add a note to a payment
 *
 * @since 1.0
 *
 * @param int    $payment_id The payment ID to store a note for
 * @param string $note       The note to store
 *
 * @return int The new note ID
 */
function give_insert_payment_note( $payment_id = 0, $note = '' ) {
	if ( empty( $payment_id ) ) {
		return false;
	}

	do_action( 'give_pre_insert_payment_note', $payment_id, $note );

	$note_id = wp_insert_comment( wp_filter_comment( array(
		'comment_post_ID'      => $payment_id,
		'comment_content'      => $note,
		'user_id'              => is_admin() ? get_current_user_id() : 0,
		'comment_date'         => current_time( 'mysql' ),
		'comment_date_gmt'     => current_time( 'mysql', 1 ),
		'comment_approved'     => 1,
		'comment_parent'       => 0,
		'comment_author'       => '',
		'comment_author_IP'    => '',
		'comment_author_url'   => '',
		'comment_author_email' => '',
		'comment_type'         => 'give_payment_note'

	) ) );

	do_action( 'give_insert_payment_note', $note_id, $payment_id, $note );

	return $note_id;
}

/**
 * Deletes a payment note
 *
 * @since 1.0
 *
 * @param int $comment_id The comment ID to delete
 * @param int $payment_id The payment ID the note is connected to
 *
 * @return bool True on success, false otherwise
 */
function give_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	if ( empty( $comment_id ) ) {
		return false;
	}

	do_action( 'give_pre_delete_payment_note', $comment_id, $payment_id );
	$ret = wp_delete_comment( $comment_id, true );
	do_action( 'give_post_delete_payment_note', $comment_id, $payment_id );

	return $ret;
}

/**
 * Gets the payment note HTML
 *
 * @since 1.0
 *
 * @param     object      /int $note The comment object or ID
 * @param int $payment_id The payment ID the note is connected to
 *
 * @return string
 */
function give_get_payment_note_html( $note, $payment_id = 0 ) {

	if ( is_numeric( $note ) ) {
		$note = get_comment( $note );
	}

	if ( ! empty( $note->user_id ) ) {
		$user = get_userdata( $note->user_id );
		$user = $user->display_name;
	} else {
		$user = __( 'System', 'give' );
	}

	$date_format = get_option( 'date_format' ) . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url( add_query_arg( array(
		'give-action' => 'delete_payment_note',
		'note_id'     => $note->comment_ID,
		'payment_id'  => $payment_id
	) ), 'give_delete_payment_note_' . $note->comment_ID );

	$note_html = '<div class="give-payment-note" id="give-payment-note-' . $note->comment_ID . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '</span><br/>';
	$note_html .= $note->comment_content;
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="give-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '" title="' . __( 'Delete this payment note', 'give' ) . '">' . __( 'Delete', 'give' ) . '</a>';
	$note_html .= '</p>';
	$note_html .= '</div>';

	return $note_html;

}

/**
 * Exclude notes (comments) on give_payment post type from showing in Recent
 * Comments widgets
 *
 * @since 1.0
 *
 * @param obj $query WordPress Comment Query Object
 *
 * @return void
 */
function give_hide_payment_notes( $query ) {
	global $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '>=' ) ) {
		$types = isset( $query->query_vars['type__not_in'] ) ? $query->query_vars['type__not_in'] : array();
		if ( ! is_array( $types ) ) {
			$types = array( $types );
		}
		$types[]                           = 'give_payment_note';
		$query->query_vars['type__not_in'] = $types;
	}
}

add_action( 'pre_get_comments', 'give_hide_payment_notes', 10 );

/**
 * Exclude notes (comments) on give_payment post type from showing in Recent Comments widgets
 *
 * @since 1.0
 *
 * @param array $clauses          Comment clauses for comment query
 * @param obj   $wp_comment_query WordPress Comment Query Object
 *
 * @return array $clauses Updated comment clauses
 */
function give_hide_payment_notes_pre_41( $clauses, $wp_comment_query ) {
	global $wpdb, $wp_version;

	if ( version_compare( floatval( $wp_version ), '4.1', '<' ) ) {
		$clauses['where'] .= ' AND comment_type != "give_payment_note"';
	}

	return $clauses;
}

add_filter( 'comments_clauses', 'give_hide_payment_notes_pre_41', 10, 2 );


/**
 * Exclude notes (comments) on give_payment post type from showing in comment feeds
 *
 * @since 1.0
 *
 * @param array $where
 * @param obj   $wp_comment_query WordPress Comment Query Object
 *
 * @return array $where
 */
function give_hide_payment_notes_from_feeds( $where, $wp_comment_query ) {
	global $wpdb;

	$where .= $wpdb->prepare( " AND comment_type != %s", 'give_payment_note' );

	return $where;
}

add_filter( 'comment_feed_where', 'give_hide_payment_notes_from_feeds', 10, 2 );


/**
 * Remove Give Comments from the wp_count_comments function
 *
 * @access public
 * @since  1.0
 *
 * @param array $stats   (empty from core filter)
 * @param int   $post_id Post ID
 *
 * @return array Array of comment counts
 */
function give_remove_payment_notes_in_comment_counts( $stats, $post_id ) {
	global $wpdb, $pagenow;

	if ( 'index.php' != $pagenow ) {
		return $stats;
	}

	$post_id = (int) $post_id;

	if ( apply_filters( 'give_count_payment_notes_in_comments', false ) ) {
		return $stats;
	}

	$stats = wp_cache_get( "comments-{$post_id}", 'counts' );

	if ( false !== $stats ) {
		return $stats;
	}

	$where = 'WHERE comment_type != "give_payment_note"';

	if ( $post_id > 0 ) {
		$where .= $wpdb->prepare( " AND comment_post_ID = %d", $post_id );
	}

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total    = 0;
	$approved = array(
		'0'            => 'moderated',
		'1'            => 'approved',
		'spam'         => 'spam',
		'trash'        => 'trash',
		'post-trashed' => 'post-trashed'
	);
	foreach ( (array) $count as $row ) {
		// Don't count post-trashed toward totals
		if ( 'post-trashed' != $row['comment_approved'] && 'trash' != $row['comment_approved'] ) {
			$total += $row['num_comments'];
		}
		if ( isset( $approved[ $row['comment_approved'] ] ) ) {
			$stats[ $approved[ $row['comment_approved'] ] ] = $row['num_comments'];
		}
	}

	$stats['total_comments'] = $total;
	foreach ( $approved as $key ) {
		if ( empty( $stats[ $key ] ) ) {
			$stats[ $key ] = 0;
		}
	}

	$stats = (object) $stats;
	wp_cache_set( "comments-{$post_id}", $stats, 'counts' );

	return $stats;
}

add_filter( 'wp_count_comments', 'give_remove_payment_notes_in_comment_counts', 10, 2 );


/**
 * Filter where older than one week
 *
 * @access public
 * @since  1.0
 *
 * @param string $where Where clause
 *
 * @return string $where Modified where clause
 */
function give_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week
	$start = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";

	return $where;
}


/**
 * Get Price ID
 *
 * @description Retrieves the Price ID given a proper form ID and price (donation) total
 *
 * @param $form_id
 * @param $price
 *
 * @return string $price_id
 */
function give_get_price_id( $form_id, $price ) {

	$price_id = 0;

	if ( give_has_variable_prices( $form_id ) ) {

		$levels = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );

		foreach ( $levels as $level ) {

			$level_amount = (float) give_sanitize_amount( $level['_give_amount'] );

			//check that this indeed the recurring price
			if ( $level_amount == $price ) {

				$price_id = $level['_give_id']['level_id'];

			}

		}

	}

	return $price_id;

}

/**
 * Retrieves arbitrary fees for the donation (Currently not in use!!)
 * @TODO  - Incorporate a fee-based functionality similar to below
 * @since 1.0
 *
 * @param int    $payment_id Payment ID
 * @param string $type       Fee type
 *
 * @return mixed array if payment fees found, false otherwise
 */
function give_get_payment_fees( $payment_id = 0, $type = 'all' ) {
	$payment_meta = give_get_payment_meta( $payment_id );
	$fees         = array();
	$payment_fees = isset( $payment_meta['fees'] ) ? $payment_meta['fees'] : false;
	if ( ! empty( $payment_fees ) && is_array( $payment_fees ) ) {
		foreach ( $payment_fees as $fee_id => $fee ) {
			if ( 'all' != $type && ! empty( $fee['type'] ) && $type != $fee['type'] ) {
				unset( $payment_fees[ $fee_id ] );
			} else {
				$fees[] = array(
					'id'     => $fee_id,
					'amount' => $fee['amount'],
					'label'  => $fee['label']
				);
			}
		}
	}

	return apply_filters( 'give_get_payment_fees', $fees, $payment_id );
}