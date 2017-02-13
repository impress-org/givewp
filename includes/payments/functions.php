<?php
/**
 * Payment Functions
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
 * }
 *
 * @return object $payments Payments retrieved from the database
 */
function give_get_payments( $args = array() ) {

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
			$payment = give_get_payments( array(
				'meta_key'       => '_give_payment_purchase_key',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( $payment ) {
				$payment = new Give_Payment( $payment[0] );
			}

			break;

		case 'payment_number':
			$payment = give_get_payments( array(
				'meta_key'       => '_give_payment_number',
				'meta_value'     => $value,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			) );

			if ( $payment ) {
				$payment = new Give_Payment( $payment[0] );
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
 * @since  1.0
 *
 * @param  array $payment_data Arguments passed.
 *
 * @return int|bool Payment ID if payment is inserted, false otherwise.
 */
function give_insert_payment( $payment_data = array() ) {

	if ( empty( $payment_data ) ) {
		return false;
	}

	$payment    = new Give_Payment();
	$gateway    = ! empty( $payment_data['gateway'] ) ? $payment_data['gateway'] : '';
	$gateway    = empty( $gateway ) && isset( $_POST['give-gateway'] ) ? $_POST['give-gateway'] : $gateway;
	$form_id    = isset( $payment_data['give_form_id'] ) ? $payment_data['give_form_id'] : 0;
	$price_id   = isset( $payment_data['give_price_id'] ) ? $payment_data['give_price_id'] : give_get_price_id( $payment_data['give_form_id'], $payment_data['price'] );
	$form_title = isset( $payment_data['give_form_title'] ) ? $payment_data['give_form_title'] : get_the_title( $form_id );

	// Set properties.
	$payment->total          = $payment_data['price'];
	$payment->status         = ! empty( $payment_data['status'] ) ? $payment_data['status'] : 'pending';
	$payment->currency       = ! empty( $payment_data['currency'] ) ? $payment_data['currency'] : give_get_currency();
	$payment->user_info      = $payment_data['user_info'];
	$payment->gateway        = $gateway;
	$payment->form_title     = $form_title;
	$payment->form_id        = $form_id;
	$payment->price_id       = $price_id;
	$payment->user_id        = $payment_data['user_info']['id'];
	$payment->email          = $payment_data['user_email'];
	$payment->first_name     = $payment_data['user_info']['first_name'];
	$payment->last_name      = $payment_data['user_info']['last_name'];
	$payment->email          = $payment_data['user_info']['email'];
	$payment->ip             = give_get_ip();
	$payment->key            = $payment_data['purchase_key'];
	$payment->mode           = give_is_test_mode() ? 'test' : 'live';
	$payment->parent_payment = ! empty( $payment_data['parent'] ) ? absint( $payment_data['parent'] ) : '';

	// Add the donation.
	$args = array(
		'price'    => $payment->total,
		'price_id' => $payment->price_id,
		'fees'     => isset( $payment_data['fees'] ) ? $payment_data['fees'] : array(),
	);

	$payment->add_donation( $payment->form_id, $args );

	// Set date if present.
	if ( isset( $payment_data['post_date'] ) ) {
		$payment->date = $payment_data['post_date'];
	}

	// Handle sequential payments.
	if ( give_get_option( 'enable_sequential' ) ) {
		$number          = give_get_next_payment_number();
		$payment->number = give_format_payment_number( $number );
		update_option( 'give_last_payment_number', $number );
	}

	// Clear the user's donation cache.
	delete_transient( 'give_user_' . $payment_data['user_info']['id'] . '_purchases' );

	// Save payment.
	$payment->save();

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
	$insert_payment_data = array(
		'price'           => $payment_data['price'],
		'give_form_title' => $payment_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $payment_data['date'],
		'user_email'      => $payment_data['user_email'],
		'purchase_key'    => $payment_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $payment_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'paypal',
	);

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
 * @since  1.0
 *
 * @param  int    $payment_id Payment ID.
 * @param  string $new_status New Payment Status. Default is 'publish'.
 *
 * @return bool
 */
function give_update_payment_status( $payment_id, $new_status = 'publish' ) {

	$payment         = new Give_Payment( $payment_id );
	$payment->status = $new_status;
	$updated         = $payment->save();

	return $updated;
}


/**
 * Deletes a Donation
 *
 * @since  1.0
 * @global      $give_logs
 *
 * @param  int  $payment_id      Payment ID (default: 0).
 * @param  bool $update_customer If we should update the customer stats (default:true).
 *
 * @return void
 */
function give_delete_purchase( $payment_id = 0, $update_customer = true ) {
	global $give_logs;

	$payment     = new Give_Payment( $payment_id );
	$amount      = give_get_payment_amount( $payment_id );
	$status      = $payment->post_status;
	$customer_id = give_get_payment_customer_id( $payment_id );
	$customer    = new Give_Customer( $customer_id );

	// Only undo donations that aren't these statuses.
	$dont_undo_statuses = apply_filters( 'give_undo_purchase_statuses', array(
		'pending',
		'cancelled',
	) );

	if ( ! in_array( $status, $dont_undo_statuses ) ) {
		give_undo_purchase( false, $payment_id );
	}

	if ( $status == 'publish' ) {

		// Only decrease earnings if they haven't already been decreased (or were never increased for this payment).
		give_decrease_total_earnings( $amount );
		// Clear the This Month earnings (this_monththis_month is NOT a typo).
		delete_transient( md5( 'give_earnings_this_monththis_month' ) );

		if ( $customer->id && $update_customer ) {

			// Decrement the stats for the donor.
			$customer->decrease_purchase_count();
			$customer->decrease_value( $amount );

		}
	}

	/**
	 * Fires before deleting payment.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment ID.
	 */
	do_action( 'give_payment_delete', $payment_id );

	if ( $customer->id && $update_customer ) {

		// Remove the payment ID from the donor.
		$customer->remove_payment( $payment_id );

	}

	// Remove the payment.
	wp_delete_post( $payment_id, true );

	// Remove related sale log entries.
	$give_logs->delete_logs(
		null,
		'sale',
		array(
			array(
				'key'   => '_give_log_payment_id',
				'value' => $payment_id,
			),
		)
	);

	/**
	 * Fires after payment deleted.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id Payment ID.
	 */
	do_action( 'give_payment_deleted', $payment_id );
}

/**
 * Undo Donation
 *
 * Undoes a donation, including the decrease of donations and earning stats.
 * Used for when refunding or deleting a donation.
 *
 * @since  1.0
 *
 * @param  int|bool $form_id    Form ID (default: false).
 * @param  int      $payment_id Payment ID.
 *
 * @return void
 */
function give_undo_purchase( $form_id = false, $payment_id ) {

	if ( ! empty( $form_id ) ) {
		$form_id = false;
		_give_deprected_argument( 'form_id', 'give_undo_purchase', '1.5' );
	}

	$payment = new Give_Payment( $payment_id );

	$maybe_decrease_earnings = apply_filters( 'give_decrease_earnings_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_earnings ) {
		// Decrease earnings.
		give_decrease_earnings( $payment->form_id, $payment->total );
	}

	$maybe_decrease_sales = apply_filters( 'give_decrease_sales_on_undo', true, $payment, $payment->form_id );
	if ( true === $maybe_decrease_sales ) {
		// Decrease donation count.
		give_decrease_purchase_count( $payment->form_id );
	}

}


/**
 * Count Payments
 *
 * Returns the total number of payments recorded.
 *
 * @since  1.0
 *
 * @param  array $args Arguments passed.
 *
 * @return array $count Number of payments sorted by payment status.
 */
function give_count_payments( $args = array() ) {

	global $wpdb;

	$defaults = array(
		'user'       => null,
		's'          => null,
		'start-date' => null,
		'end-date'   => null,
		'form_id'    => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$select = 'SELECT p.post_status,count( * ) AS num_posts';
	$join   = '';
	$where  = "WHERE p.post_type = 'give_payment'";

	// Count payments for a specific user.
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

		// Count payments for a search.
	} elseif ( ! empty( $args['s'] ) ) {

		if ( is_email( $args['s'] ) || strlen( $args['s'] ) == 32 ) {

			if ( is_email( $args['s'] ) ) {
				$field = '_give_payment_user_email';
			} else {
				$field = '_give_payment_purchase_key';
			}

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( '
                AND m.meta_key = %s
                AND m.meta_value = %s',
				$field,
				$args['s']
			);

		} elseif ( '#' == substr( $args['s'], 0, 1 ) ) {

			$search = str_replace( '#:', '', $args['s'] );
			$search = str_replace( '#', '', $search );

			$select = 'SELECT p2.post_status,count( * ) AS num_posts ';
			$join   = "LEFT JOIN $wpdb->postmeta m ON m.meta_key = '_give_log_payment_id' AND m.post_id = p.ID ";
			$join .= "INNER JOIN $wpdb->posts p2 ON m.meta_value = p2.ID ";
			$where = "WHERE p.post_type = 'give_log' ";
			$where .= $wpdb->prepare( 'AND p.post_parent = %d} ', $search );

		} elseif ( is_numeric( $args['s'] ) ) {

			$join = "LEFT JOIN $wpdb->postmeta m ON (p.ID = m.post_id)";
			$where .= $wpdb->prepare( "
				AND m.meta_key = '_give_payment_user_id'
				AND m.meta_value = %d",
				$args['s']
			);

		} else {
			$search = $wpdb->esc_like( $args['s'] );
			$search = '%' . $search . '%';

			$where .= $wpdb->prepare( 'AND ((p.post_title LIKE %s) OR (p.post_content LIKE %s))', $search, $search );
		}
	}

	if ( ! empty( $args['form_id'] ) && is_numeric( $args['form_id'] ) ) {

		$where .= $wpdb->prepare( ' AND p.post_parent = %d', $args['form_id'] );

	}
	// Limit payments count by date.
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

		// Fixes an issue with the payments list table counts when no end date is specified (partiy with stats class).
		if ( empty( $args['end-date'] ) ) {
			$args['end-date'] = $args['start-date'];
		}
	}

	if ( ! empty( $args['end-date'] ) && false !== strpos( $args['end-date'], '/' ) ) {

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

	$query = "$select
		FROM $wpdb->posts p
		$join
		$where
		GROUP BY p.post_status
	";

	$cache_key = md5( $query );

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
 * @since  1.0
 *
 * @param  int $payment_id Payment ID
 *
 * @return bool $exists True if payment exists, false otherwise.
 */
function give_check_for_existing_payment( $payment_id ) {
	$exists  = false;
	$payment = new Give_Payment( $payment_id );

	if ( $payment_id === $payment->ID && 'publish' === $payment->status ) {
		$exists = true;
	}

	return $exists;
}

/**
 * Get Payment Status
 *
 * @since 1.0
 *
 * @param WP_Post|Give_Payment $payment      Payment object.
 * @param bool                 $return_label Whether to return the translated status label
 *                                           instead of status value. Default false.
 *
 * @return bool|mixed True if payment status exists, false otherwise.
 */
function give_get_payment_status( $payment, $return_label = false ) {

	if ( ! is_object( $payment ) || ! isset( $payment->post_status ) ) {
		return false;
	}

	$statuses = give_get_payment_statuses();

	if ( ! is_array( $statuses ) || empty( $statuses ) ) {
		return false;
	}

	// Get payment object if no already given.
	$payment = $payment instanceof Give_Payment ? $payment : new Give_Payment( $payment->ID );

	if ( array_key_exists( $payment->status, $statuses ) ) {
		if ( true === $return_label ) {
			// Return translated status label.
			return $statuses[ $payment->status ];
		} else {
			// Account that our 'publish' status is labeled 'Complete'
			$post_status = 'publish' == $payment->status ? 'Complete' : $payment->post_status;

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
	$payment_statuses = array(
		'pending'     => __( 'Pending', 'give' ),
		'publish'     => __( 'Complete', 'give' ),
		'refunded'    => __( 'Refunded', 'give' ),
		'failed'      => __( 'Failed', 'give' ),
		'cancelled'   => __( 'Cancelled', 'give' ),
		'abandoned'   => __( 'Abandoned', 'give' ),
		'preapproval' => __( 'Pre-Approved', 'give' ),
		'revoked'     => __( 'Revoked', 'give' ),
	);

	return apply_filters( 'give_payment_statuses', $payment_statuses );
}

/**
 * Get Payment Status Keys
 *
 * Retrieves keys for all available statuses for payments
 *
 * @since  1.0
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
 * @since  1.0
 *
 * @param  int $day       Day number. Default is null.
 * @param  int $month_num Month number. Default is null.
 * @param  int $year      Year number. Default is null.
 * @param  int $hour      Hour number. Default is null.
 *
 * @return int $earnings  Earnings
 */
function give_get_earnings_by_date( $day = null, $month_num, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use Give_Payment_Stats with the get_earnings() method instead.
	global $wpdb;

	$args = array(
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'monthnum'               => $month_num,
		'post_status'            => array( 'publish' ),
		'fields'                 => 'ids',
		'update_post_term_cache' => false,
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

			$earnings = $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_give_payment_total' AND post_id IN ({$sales})" );

		}
		// Cache the results for one hour.
		set_transient( $key, $earnings, HOUR_IN_SECONDS );
	}

	return round( $earnings, 2 );
}

/**
 * Get Donations (sales) By Date
 *
 * @since  1.0
 *
 * @param  int $day       Day number. Default is null.
 * @param  int $month_num Month number. Default is null.
 * @param  int $year      Year number. Default is null.
 * @param  int $hour      Hour number. Default is null.
 *
 * @return int $count     Sales
 */
function give_get_sales_by_date( $day = null, $month_num = null, $year = null, $hour = null ) {

	// This is getting deprecated soon. Use Give_Payment_Stats with the get_sales() method instead.
	$args = array(
		'post_type'              => 'give_payment',
		'nopaging'               => true,
		'year'                   => $year,
		'fields'                 => 'ids',
		'post_status'            => array( 'publish' ),
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$show_free = apply_filters( 'give_sales_by_date_show_free', true, $args );

	if ( false === $show_free ) {
		$args['meta_query'] = array(
			array(
				'key'     => '_give_payment_total',
				'value'   => 0,
				'compare' => '>',
				'type'    => 'NUMERIC',
			),
		);
	}

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

	$key = 'give_stats_' . substr( md5( serialize( $args ) ), 0, 15 );

	if ( ! empty( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'give-refresh-reports' ) ) {
		$count = false;
	} else {
		$count = get_transient( $key );
	}

	if ( false === $count ) {
		$sales = new WP_Query( $args );
		$count = (int) $sales->post_count;
		// Cache the results for one hour.
		set_transient( $key, $count, HOUR_IN_SECONDS );
	}

	return $count;
}

/**
 * Checks whether a payment has been marked as complete.
 *
 * @since  1.0
 *
 * @param  int $payment_id Payment ID to check against.
 *
 * @return bool $ret True if complete, false otherwise.
 */
function give_is_payment_complete( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	$ret = false;

	if ( $payment->ID > 0 ) {

		if ( (int) $payment_id === (int) $payment->ID && 'publish' == $payment->status ) {
			$ret = true;
		}
	}

	return apply_filters( 'give_is_payment_complete', $ret, $payment_id, $payment->post_status );
}

/**
 * Get Total Donations.
 *
 * @since  1.0
 *
 * @return int $count Total sales.
 */
function give_get_total_sales() {

	$payments = give_count_payments();

	return $payments->publish;
}

/**
 * Get Total Earnings
 *
 * @since  1.0
 *
 * @return float $total Total earnings.
 */
function give_get_total_earnings() {

	$total = get_option( 'give_earnings_total', false );

	// If no total stored in DB, use old method of calculating total earnings.
	if ( false === $total ) {

		global $wpdb;

		$total = get_transient( 'give_earnings_total' );

		if ( false === $total ) {

			$total = (float) 0;

			$args = apply_filters( 'give_get_total_earnings_args', array(
				'offset' => 0,
				'number' => - 1,
				'status' => array( 'publish' ),
				'fields' => 'ids',
			) );

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
					$total += $wpdb->get_var( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = '_give_payment_total' AND post_id IN({$payments})" );
				}
			}

			// Cache results for 1 day. This cache is cleared automatically when a payment is made.
			set_transient( 'give_earnings_total', $total, 86400 );

			// Store the total for the first time.
			update_option( 'give_earnings_total', $total );
		}
	}

	if ( $total < 0 ) {
		$total = 0; // Don't ever show negative earnings.
	}

	return apply_filters( 'give_total_earnings', round( $total, give_currency_decimal_filter() ) );
}

/**
 * Increase the Total Earnings
 *
 * @since  1.0
 *
 * @param  int $amount   The amount you would like to increase the total earnings by.
 *                       Default is 0.
 *
 * @return float $total  Total earnings.
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
 * @param int $amount The amount you would like to decrease the total earnings by.
 *
 * @return float $total Total earnings.
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
 * @param int    $payment_id Payment ID.
 * @param string $meta_key   The meta key to pull.
 * @param bool   $single     Pull single meta entry or as an object.
 *
 * @return mixed $meta Payment Meta.
 */
function give_get_payment_meta( $payment_id = 0, $meta_key = '_give_payment_meta', $single = true ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->get_meta( $meta_key, $single );
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
	$payment = new Give_Payment( $payment_id );

	return $payment->update_meta( $meta_key, $meta_value, $prev_value );
}

/**
 * Get the user_info Key from Payment Meta
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $user_info User Info Meta Values.
 */
function give_get_payment_meta_user_info( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->user_info;
}

/**
 * Get the donations Key from Payment Meta
 *
 * Retrieves the form_id from a (Previously titled give_get_payment_meta_donations)
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return int $form_id Form ID.
 */
function give_get_payment_form_id( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->form_id;
}

/**
 * Get the user email associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $email User email.
 */
function give_get_payment_user_email( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->email;
}

/**
 * Is the payment provided associated with a user account
 *
 * @since  1.3
 *
 * @param  int $payment_id The payment ID.
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
 * @since 1.3
 *
 * @param int $payment_id Payment ID.
 *
 * @return int $user_id User ID.
 */
function give_get_payment_user_id( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->user_id;
}

/**
 * Get the donor ID associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return int $customer_id Customer ID.
 */
function give_get_payment_customer_id( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->customer_id;
}

/**
 * Get the IP address used to make a donation
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $ip User IP.
 */
function give_get_payment_user_ip( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->ip;
}

/**
 * Get the date a payment was completed
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $date The date the payment was completed.
 */
function give_get_payment_completed_date( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->completed_date;
}

/**
 * Get the gateway associated with a payment
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $gateway Gateway.
 */
function give_get_payment_gateway( $payment_id ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->gateway;
}

/**
 * Get the currency code a payment was made in
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $currency The currency code.
 */
function give_get_payment_currency_code( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->currency;
}

/**
 * Get the currency name a payment was made in
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
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
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $key Donation key.
 */
function give_get_payment_key( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->key;
}

/**
 * Get the payment order number
 *
 * This will return the payment ID if sequential order numbers are not enabled or the order number does not exist
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $number Payment order number.
 */
function give_get_payment_number( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->number;
}

/**
 * Formats the payment number with the prefix and postfix
 *
 * @since  1.3
 *
 * @param  int $number The payment number to format.
 *
 * @return string      The formatted payment number.
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
 * @return string $number The next available payment number.
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

		// This case handles the first addition of the new option, as well as if it get's deleted for any reason.
		$payments     = new Give_Payments_Query( array(
			'number'  => 1,
			'order'   => 'DESC',
			'orderby' => 'ID',
			'output'  => 'posts',
			'fields'  => 'ids',
		) );
		$last_payment = $payments->get_payments();

		if ( ! empty( $last_payment ) ) {

			$number = give_get_payment_number( $last_payment[0] );

		}

		if ( ! empty( $number ) && $number !== (int) $last_payment[0] ) {

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
 * @param  string $number The formatted Current Number to increment.
 *
 * @return string The new Payment number without prefix and postfix.
 */
function give_remove_payment_prefix_postfix( $number ) {

	$prefix  = give_get_option( 'sequential_prefix' );
	$postfix = give_get_option( 'sequential_postfix' );

	// Remove prefix.
	$number = preg_replace( '/' . $prefix . '/', '', $number, 1 );

	// Remove the postfix.
	$length      = strlen( $number );
	$postfix_pos = strrpos( $number, $postfix );
	if ( false !== $postfix_pos ) {
		$number = substr_replace( $number, '', $postfix_pos, $length );
	}

	// Ensure it's a whole number.
	$number = intval( $number );

	return apply_filters( 'give_remove_payment_prefix_postfix', $number, $prefix, $postfix );

}


/**
 * Get Payment Amount
 *
 * Get the fully formatted payment amount. The payment amount is retrieved using give_get_payment_amount() and is then sent through give_currency_filter() and  give_format_amount() to format the amount correctly.
 *
 * @since       1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string $amount Fully formatted payment amount.
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
 * @param int $payment_id Payment ID.
 *
 * @return mixed|void
 */
function give_get_payment_amount( $payment_id ) {

	$payment = new Give_Payment( $payment_id );

	return apply_filters( 'give_payment_amount', floatval( $payment->total ), $payment_id );
}

/**
 * Payment Subtotal
 *
 * Retrieves subtotal for payment (this is the amount before fees) and then returns a full formatted amount. This function essentially calls give_get_payment_subtotal()
 *
 * @since 1.5
 *
 * @param int $payment_id Payment ID.
 *
 * @see   give_get_payment_subtotal()
 *
 * @return array Fully formatted payment subtotal.
 */
function give_payment_subtotal( $payment_id = 0 ) {
	$subtotal = give_get_payment_subtotal( $payment_id );

	return give_currency_filter( give_format_amount( $subtotal ), give_get_payment_currency_code( $payment_id ) );
}

/**
 * Get Payment Subtotal
 *
 * Retrieves subtotal for payment (this is the amount before fees) and then returns a non formatted amount.
 *
 * @since 1.5
 *
 * @param int $payment_id Payment ID.
 *
 * @return float $subtotal Subtotal for payment (non formatted).
 */
function give_get_payment_subtotal( $payment_id = 0 ) {
	$payment = new G_Payment( $payment_id );

	return $payment->subtotal;
}

/**
 * Retrieves arbitrary fees for the payment
 *
 * @since 1.5
 *
 * @param int    $payment_id Payment ID.
 * @param string $type       Fee type.
 *
 * @return mixed array if payment fees found, false otherwise.
 */
function give_get_payment_fees( $payment_id = 0, $type = 'all' ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->get_fees( $type );
}

/**
 * Retrieves the donation ID
 *
 * @since  1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return string The donation ID.
 */
function give_get_payment_transaction_id( $payment_id = 0 ) {
	$payment = new Give_Payment( $payment_id );

	return $payment->transaction_id;
}

/**
 * Sets a Transaction ID in post meta for the given Payment ID.
 *
 * @since  1.0
 *
 * @param int    $payment_id     Payment ID.
 * @param string $transaction_id The transaction ID from the gateway.
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
 * @since 1.0
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @param string  $key  the key to search for.
 *
 * @return int $purchase Donation ID.
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
 * Retrieve the donation ID based on the transaction ID
 *
 * @since 1.3
 * @global object $wpdb Used to query the database using the WordPress Database API.
 *
 * @param string  $key  The transaction ID to search for.
 *
 * @return int $purchase Donation ID.
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
 * Retrieve all notes attached to a donation
 *
 * @since 1.0
 *
 * @param int    $payment_id The donation ID to retrieve notes for.
 * @param string $search     Search for notes that contain a search term.
 *
 * @return array $notes Donation Notes
 */
function give_get_payment_notes( $payment_id = 0, $search = '' ) {

	if ( empty( $payment_id ) && empty( $search ) ) {
		return false;
	}

	remove_action( 'pre_get_comments', 'give_hide_payment_notes', 10 );
	remove_filter( 'comments_clauses', 'give_hide_payment_notes_pre_41', 10 );

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
 * @param int    $payment_id The payment ID to store a note for.
 * @param string $note       The note to store.
 *
 * @return int The new note ID
 */
function give_insert_payment_note( $payment_id = 0, $note = '' ) {
	if ( empty( $payment_id ) ) {
		return false;
	}

	/**
	 * Fires before inserting payment note.
	 *
	 * @since 1.0
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $note       The note.
	 */
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
		'comment_type'         => 'give_payment_note',

	) ) );

	/**
	 * Fires after payment note inserted.
	 *
	 * @since 1.0
	 *
	 * @param int    $note_id    Note ID.
	 * @param int    $payment_id Payment ID.
	 * @param string $note       The note.
	 */
	do_action( 'give_insert_payment_note', $note_id, $payment_id, $note );

	return $note_id;
}

/**
 * Deletes a payment note
 *
 * @since 1.0
 *
 * @param int $comment_id The comment ID to delete.
 * @param int $payment_id The payment ID the note is connected to.
 *
 * @return bool True on success, false otherwise.
 */
function give_delete_payment_note( $comment_id = 0, $payment_id = 0 ) {
	if ( empty( $comment_id ) ) {
		return false;
	}

	/**
	 * Fires before deleting donation note.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id Note ID.
	 * @param int $payment_id Payment ID.
	 */
	do_action( 'give_pre_delete_payment_note', $comment_id, $payment_id );

	$ret = wp_delete_comment( $comment_id, true );

	/**
	 * Fires after donation note deleted.
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id Note ID.
	 * @param int $payment_id Payment ID.
	 */
	do_action( 'give_post_delete_payment_note', $comment_id, $payment_id );

	return $ret;
}

/**
 * Gets the payment note HTML
 *
 * @since 1.0
 *
 * @param object|int $note       The comment object or ID.
 * @param int        $payment_id The payment ID the note is connected to.
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
		$user = esc_html__( 'System', 'give' );
	}

	$date_format = give_date_format() . ', ' . get_option( 'time_format' );

	$delete_note_url = wp_nonce_url( add_query_arg( array(
		'give-action' => 'delete_payment_note',
		'note_id'     => $note->comment_ID,
		'payment_id'  => $payment_id,
	) ),
		'give_delete_payment_note_' . $note->comment_ID
	);

	$note_html = '<div class="give-payment-note" id="give-payment-note-' . $note->comment_ID . '">';
	$note_html .= '<p>';
	$note_html .= '<strong>' . $user . '</strong>&nbsp;&ndash;&nbsp;<span style="color:#aaa;font-style:italic;">' . date_i18n( $date_format, strtotime( $note->comment_date ) ) . '</span><br/>';
	$note_html .= $note->comment_content;
	$note_html .= '&nbsp;&ndash;&nbsp;<a href="' . esc_url( $delete_note_url ) . '" class="give-delete-payment-note" data-note-id="' . absint( $note->comment_ID ) . '" data-payment-id="' . absint( $payment_id ) . '" aria-label="' . esc_attr__( 'Delete this donation note.', 'give' ) . '">' . esc_html__( 'Delete', 'give' ) . '</a>';
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
 * @param object $query WordPress Comment Query Object.
 *
 * @return void
 */
function give_hide_payment_notes( $query ) {
	if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.1', '>=' ) ) {
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
 * @param array  $clauses          Comment clauses for comment query.
 * @param object $wp_comment_query WordPress Comment Query Object.
 *
 * @return array $clauses Updated comment clauses.
 */
function give_hide_payment_notes_pre_41( $clauses, $wp_comment_query ) {
	if ( version_compare( floatval( get_bloginfo( 'version' ) ), '4.1', '<' ) ) {
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
 * @param string $where
 * @param object $wp_comment_query WordPress Comment Query Object.
 *
 * @return string $where
 */
function give_hide_payment_notes_from_feeds( $where, $wp_comment_query ) {
	global $wpdb;

	$where .= $wpdb->prepare( ' AND comment_type != %s', 'give_payment_note' );

	return $where;
}

add_filter( 'comment_feed_where', 'give_hide_payment_notes_from_feeds', 10, 2 );


/**
 * Remove Give Comments from the wp_count_comments function
 *
 * @access public
 * @since  1.0
 *
 * @param array $stats   (empty from core filter).
 * @param int   $post_id Post ID.
 *
 * @return array Array of comment counts.
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
		$where .= $wpdb->prepare( ' AND comment_post_ID = %d', $post_id );
	}

	$count = $wpdb->get_results( "SELECT comment_approved, COUNT( * ) AS num_comments FROM {$wpdb->comments} {$where} GROUP BY comment_approved", ARRAY_A );

	$total    = 0;
	$approved = array(
		'0'            => 'moderated',
		'1'            => 'approved',
		'spam'         => 'spam',
		'trash'        => 'trash',
		'post-trashed' => 'post-trashed',
	);
	foreach ( (array) $count as $row ) {
		// Don't count post-trashed toward totals.
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
 * @param string $where Where clause.
 *
 * @return string $where Modified where clause.
 */
function give_filter_where_older_than_week( $where = '' ) {
	// Payments older than one week.
	$start = date( 'Y-m-d', strtotime( '-7 days' ) );
	$where .= " AND post_date <= '{$start}'";

	return $where;
}


/**
 * Get Payment Form ID.
 *
 * Retrieves the form title and appends the level name if present.
 *
 * @since 1.5
 *
 * @param array  $payment_meta       Payment meta data.
 * @param bool   $only_level         If set to true will only return the level name if multi-level enabled.
 * @param string $separator          The separator between the .
 *
 * @return string $form_title Returns the full title if $only_level is false, otherwise returns the levels title.
 */
function give_get_payment_form_title( $payment_meta, $only_level = false, $separator = '' ) {

	$form_id    = isset( $payment_meta['form_id'] ) ? $payment_meta['form_id'] : 0;
	$price_id   = isset( $payment_meta['price_id'] ) ? $payment_meta['price_id'] : null;
	$form_title = isset( $payment_meta['form_title'] ) ? $payment_meta['form_title'] : '';

	if ( $only_level == true ) {
		$form_title = '';
	}

	//If multi-level, append to the form title.
	if ( give_has_variable_prices( $form_id ) ) {

		//Only add separator if there is a form title.
		if ( ! empty( $form_title ) ) {
			$form_title .= ' ' . $separator . ' ';
		}

		$form_title .= '<span class="donation-level-text-wrap">';

		if ( $price_id == 'custom' ) {
			$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
			$form_title .= ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give' );
		} else {
			$form_title .= give_get_price_option_name( $form_id, $price_id );
		}

		$form_title .= '</span>';

	}

	return apply_filters( 'give_get_payment_form_title', $form_title, $payment_meta );

}

/**
 * Get Price ID
 *
 * Retrieves the Price ID when provided a proper form ID and price (donation) total
 *
 * @param int    $form_id Form ID.
 * @param string $price   Price ID.
 *
 * @return string $price_id
 */
function give_get_price_id( $form_id, $price ) {

	$price_id = 0;

	if ( give_has_variable_prices( $form_id ) ) {

		$levels = maybe_unserialize( get_post_meta( $form_id, '_give_donation_levels', true ) );

		foreach ( $levels as $level ) {

			$level_amount = (float) give_sanitize_amount( $level['_give_amount'] );

			// Check that this indeed the recurring price.
			if ( $level_amount == $price ) {

				$price_id = $level['_give_id']['level_id'];

			}
		}
	}

	return $price_id;

}

/**
 * Get/Print give form dropdown html
 *
 * This function is wrapper to public method forms_dropdown of Give_HTML_Elements class to get/print form dropdown html.
 * Give_HTML_Elements is defined in includes/class-give-html-elements.php.
 *
 * @since 1.6
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decides if print form dropdown html output or not.
 *
 * @return string|void
 */
function give_get_form_dropdown( $args = array(), $echo = false ) {
	$form_dropdown_html = Give()->html->forms_dropdown( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}

/**
 * Get/Print give form variable price dropdown html
 *
 * @since 1.6
 *
 * @param array $args Arguments for form dropdown.
 * @param bool  $echo This parameter decide if print form dropdown html output or not.
 *
 * @return string|bool
 */
function give_get_form_variable_price_dropdown( $args = array(), $echo = false ) {

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
	$variable_price_options = array();

	// Check if multi donation form support custom donation or not.
	if ( $form->is_custom_price_mode() ) {
		$variable_price_options['custom'] = _x( 'Custom', 'custom donation dropdown item', 'give' );
	}

	// Get variable price and ID from variable price array.
	foreach ( $variable_prices as $variable_price ) {
		$variable_price_options[ $variable_price['_give_id']['level_id'] ] = ! empty( $variable_price['_give_text'] ) ? $variable_price['_give_text'] : give_currency_filter( give_format_amount( $variable_price['_give_amount'] ) );
	}

	// Update options.
	$args = array_merge( $args, array( 'options' => $variable_price_options ) );

	// Generate select html.
	$form_dropdown_html = Give()->html->select( $args );

	if ( ! $echo ) {
		return $form_dropdown_html;
	}

	echo $form_dropdown_html;
}