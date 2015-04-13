<?php
/**
 * User Functions
 *
 * Functions related to users / customers
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Users Purchases
 *
 * Retrieves a list of all purchases by a specific user.
 *
 * @since  1.0
 *
 * @param int    $user   User ID or email address
 * @param int    $number Number of purchases to retrieve
 * @param bool   $pagination
 * @param string $status
 *
 * @return bool|object List of all user purchases
 */
function give_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$status = $status === 'complete' ? 'publish' : $status;

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} else if ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
	}

	$args = apply_filters( 'give_get_users_purchases_args', array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date'
	) );

	if ( $pagination ) {

		$args['page'] = $paged;

	} else {

		$args['nopaging'] = true;

	}


	if ( is_email( $user ) ) {

		$field = 'email';

	} else {

		$field = 'user_id';

	}

	/*
	$payment_ids = Give()->customers->get_column_by( 'payment_ids', $field, $user );

	if( ! empty( $payment_ids ) ) {
		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $payment_ids ) );
	}
	*/

	$purchases = give_get_payments( $args );

	// No purchases
	if ( ! $purchases ) {
		return false;
	}

	return $purchases;
}

/**
 * Get Users Purchased Products
 *
 * Returns a list of unique forms purchased by a specific user
 *
 * @since  1.0
 *
 * @param int    $user User ID or email address
 * @param string $status
 *
 * @return bool|object List of unique forms purchased by user
 */
function give_get_users_purchased_products( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	// Get the purchase history
	$purchase_history = give_get_users_purchases( $user, - 1, false, $status );

	if ( empty( $purchase_history ) ) {
		return false;
	}

	// Get all the items purchased
	$purchase_data = array();

	if ( empty( $purchase_data ) ) {
		return false;
	}

	// Grab only the post ids of the forms purchased on this order
	$purchase_product_ids = array();
	foreach ( $purchase_data as $purchase_meta ) {
		$purchase_product_ids[] = wp_list_pluck( $purchase_meta, 'id' );
	}

	if ( empty( $purchase_product_ids ) ) {
		return false;
	}

	// Merge all orders into a single array of all items purchased
	$purchased_products = array();
	foreach ( $purchase_product_ids as $product ) {
		$purchased_products = array_merge( $product, $purchased_products );
	}

	// Only include each product purchased once
	$product_ids = array_unique( $purchased_products );

	// Make sure we still have some products and a first item
	if ( empty ( $product_ids ) || ! isset( $product_ids[0] ) ) {
		return false;
	}

	$post_type = get_post_type( $product_ids[0] );

	$args = apply_filters( 'give_get_users_purchased_products_args', array(
		'include'        => $product_ids,
		'post_type'      => $post_type,
		'posts_per_page' => - 1
	) );

	return apply_filters( 'give_users_purchased_products_list', get_posts( $args ) );
}

/**
 * Has Purchases
 *
 * Checks to see if a user has purchased at least one item.
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user_id int - the ID of the user to check
 *
 * @return      bool - true if has purchased, false other wise.
 */
function give_has_purchases( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( give_get_users_purchases( $user_id, 1 ) ) {
		return true; // User has at least one purchase
	}

	return false; // User has never purchased anything
}


/**
 * Get Purchase Status for User
 *
 * Retrieves the purchase count and the total amount spent for a specific user
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user int|string - the ID or email of the customer to retrieve stats for
 * @param       $mode string - "test" or "live"
 *
 * @return      array
 */
function give_get_purchase_stats_by_user( $user = '' ) {

	if ( is_email( $user ) ) {

		$field = 'email';

	} elseif ( is_numeric( $user ) ) {

		$field = 'user_id';

	}

	$customer = Give()->customers->get_by( $field, $user );

	if ( empty( $customer ) ) {

		$stats['purchases']   = 0;
		$stats['total_spent'] = give_sanitize_amount( 0 );

	} else {

		$stats['purchases']   = absint( $customer->purchase_count );
		$stats['total_spent'] = give_sanitize_amount( $customer->purchase_value );

	}

	return (array) apply_filters( 'give_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a customer
 *
 * Returns total number of purchases a customer has made
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user mixed - ID or email
 *
 * @return      int - the total number of purchases
 */
function give_count_purchases_of_customer( $user = null ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	$stats = give_get_purchase_stats_by_user( $user );

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user mixed - ID or email
 *
 * @return      float - the total amount the user has spent
 */
function give_purchase_total_of_user( $user = null ) {

	$stats = give_get_purchase_stats_by_user( $user );

	return $stats['total_spent'];
}


/**
 * Validate a potential username
 *
 * @access      public
 * @since       1.0
 *
 * @param       $username string - the username to validate
 *
 * @return      bool
 */
function give_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid     = ( $sanitized == $username );

	return (bool) apply_filters( 'give_validate_username', $valid, $username );
}


/**
 * Looks up purchases by email that match the registering user
 *
 * This is for users that purchased as a guest and then came
 * back and created an account.
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user_id INT - the new user's ID
 *
 * @return      void
 */
function give_add_past_purchases_to_new_user( $user_id ) {

	$email = get_the_author_meta( 'user_email', $user_id );

	$payments = give_get_payments( array( 's' => $email ) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {
			if ( intval( give_get_payment_user_id( $payment->ID ) ) > 0 ) {
				continue;
			} // This payment already associated with an account

			$meta                    = give_get_payment_meta( $payment->ID );
			$meta['user_info']       = maybe_unserialize( $meta['user_info'] );
			$meta['user_info']['id'] = $user_id;
			$meta['user_info']       = $meta['user_info'];

			// Store the updated user ID in the payment meta
			give_update_payment_meta( $payment->ID, '_give_payment_meta', $meta );
			give_update_payment_meta( $payment->ID, '_give_payment_user_id', $user_id );
		}
	}

}

add_action( 'user_register', 'give_add_past_purchases_to_new_user' );


/**
 * Counts the total number of customers.
 *
 * @access        public
 * @since         1.0
 * @return        int - The total number of customers.
 */
function give_count_total_customers() {
	return Give()->customers->count();
}


/**
 * Returns the saved address for a customer
 *
 * @access        public
 * @since         1.0
 * @return        array - The customer's address, if any
 */
function give_get_customer_address( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$address = get_user_meta( $user_id, '_give_user_address', true );

	if ( ! isset( $address['line1'] ) ) {
		$address['line1'] = '';
	}

	if ( ! isset( $address['line2'] ) ) {
		$address['line2'] = '';
	}

	if ( ! isset( $address['city'] ) ) {
		$address['city'] = '';
	}

	if ( ! isset( $address['zip'] ) ) {
		$address['zip'] = '';
	}

	if ( ! isset( $address['country'] ) ) {
		$address['country'] = '';
	}

	if ( ! isset( $address['state'] ) ) {
		$address['state'] = '';
	}

	return $address;
}

/**
 * Sends the new user notification email when a user registers during checkout
 *
 * @access        public
 * @since         1.0
 * @return        void
 */
function give_new_user_notification( $user_id = 0, $user_data = array() ) {

	if ( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	wp_new_user_notification( $user_id, __( '[Password entered at checkout]', 'give' ) );
}

add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );
