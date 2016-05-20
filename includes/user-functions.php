<?php
/**
 * User Functions
 *
 * Functions related to users / donors
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
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
 * @param int $user User ID or email address
 * @param int $number Number of purchases to retrieve
 * @param bool $pagination
 * @param string $status
 *
 * @return bool|object List of all user purchases
 */
function give_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user && ! Give()->email_access->token_exists ) {
		return false;
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

	$by_user_id = is_numeric( $user ) ? true : false;
	$customer   = new Give_Customer( $user, $by_user_id );

	if ( ! empty( $customer->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $customer->payment_ids ) );

	}

	$purchases = give_get_payments( apply_filters( 'give_get_users_purchases_args', $args ) );

	// No purchases
	if ( ! $purchases ) {
		return false;
	}

	return $purchases;
}

/**
 * Get Users Donations
 *
 * Returns a list of unique donation forms given to by a specific user
 *
 * @since  1.0
 *
 * @param int $user User ID or email address
 * @param string $status
 *
 * @return bool|object List of unique forms purchased by user
 */
function give_get_users_completed_donations( $user = 0, $status = 'complete' ) {
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( empty( $user ) ) {
		return false;
	}

	$by_user_id = is_numeric( $user ) ? true : false;

	$customer = new Give_Customer( $user, $by_user_id );

	if ( empty( $customer->payment_ids ) ) {
		return false;
	}

	// Get all the items purchased
	$payment_ids    = array_reverse( explode( ',', $customer->payment_ids ) );
	$limit_payments = apply_filters( 'give_users_completed_donations_payments', 50 );
	if ( ! empty( $limit_payments ) ) {
		$payment_ids = array_slice( $payment_ids, 0, $limit_payments );
	}
	$donation_data = array();
	foreach ( $payment_ids as $payment_id ) {
		$donation_data[] = give_get_payment_meta( $payment_id );
	}

	if ( empty( $donation_data ) ) {
		return false;
	}

	// Grab only the post ids "form_id" of the forms purchased on this order
	$completed_donations_ids = array();
	foreach ( $donation_data as $purchase_meta ) {
		$completed_donations_ids[] = $purchase_meta['form_id'];
	}
	if ( empty( $completed_donations_ids ) ) {
		return false;
	}

	// Only include each product purchased once
	$form_ids = array_unique( $completed_donations_ids );

	// Make sure we still have some products and a first item
	if ( empty ( $form_ids ) || ! isset( $form_ids[0] ) ) {
		return false;
	}

	$post_type = get_post_type( $form_ids[0] );

	$args = apply_filters( 'give_get_users_completed_donations_args', array(
		'include'        => $form_ids,
		'post_type'      => $post_type,
		'posts_per_page' => - 1
	) );

	return apply_filters( 'give_users_completed_donations_list', get_posts( $args ) );
}


/**
 * Has Purchases
 *
 * Checks to see if a user has donated to at least one form.
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
 * @param       $user int|string - the ID or email of the donor to retrieve stats for
 *
 * @return      array
 */
function give_get_purchase_stats_by_user( $user = '' ) {

	if ( is_email( $user ) ) {

		$field = 'email';

	} elseif ( is_numeric( $user ) ) {

		$field = 'user_id';

	}

	$stats    = array();
	$customer = Give()->customers->get_customer_by( $field, $user );

	if ( $customer ) {

		$customer = new Give_Customer( $customer->id );

		$stats['purchases']   = absint( $customer->purchase_count );
		$stats['total_spent'] = give_sanitize_amount( $customer->purchase_value );

	}


	return (array) apply_filters( 'give_purchase_stats_by_user', $stats, $user );
}


/**
 * Count number of purchases of a donor
 *
 * @description: Returns total number of purchases a donor has made
 *
 * @access      public
 * @since       1.0
 *
 * @param       $user mixed - ID or email
 *
 * @return      int - the total number of purchases
 */
function give_count_purchases_of_customer( $user = null ) {

	//Logged in?
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	//Email access?
	if ( empty( $user ) && Give()->email_access->token_email ) {
		$user = Give()->email_access->token_email;
	}


	$stats = ! empty( $user ) ? give_get_purchase_stats_by_user( $user ) : false;

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
 * Counts the total number of donors.
 *
 * @access        public
 * @since         1.0
 * @return        int - The total number of donors.
 */
function give_count_total_customers() {
	return Give()->customers->count();
}


/**
 * Returns the saved address for a donor
 *
 * @access        public
 * @since         1.0
 * @return        array - The donor's address, if any
 */
function give_get_donor_address( $user_id = 0 ) {
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
 * Give New User Notification
 *
 * @description   : Sends the new user notification email when a user registers within the donation form
 *
 * @access        public
 * @since         1.0
 *
 * @param int $user_id
 * @param array $user_data
 *
 * @return        void
 */
function give_new_user_notification( $user_id = 0, $user_data = array() ) {

	if ( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}
	$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$message  = sprintf( esc_attr__( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
	$message .= sprintf( esc_attr__( 'Username: %s' ), $user_data['user_login'] ) . "\r\n\r\n";
	$message .= sprintf( esc_attr__( 'E-mail: %s' ), $user_data['user_email'] ) . "\r\n";

	@wp_mail( get_option( 'admin_email' ), sprintf( esc_attr__( '[%s] New User Registration' ), $blogname ), $message );

	$message = sprintf( esc_attr__( 'Username: %s' ), $user_data['user_login'] ) . "\r\n";
	$message .= sprintf( esc_attr__( 'Password: %s' ), esc_attr__( '[Password entered during donation]', 'give' ) ) . "\r\n";

	$message .= '<a href="' . wp_login_url() . '"> ' . esc_attr__( 'Click Here to Login', 'give' ) . ' &raquo;</a>' . "\r\n";

	wp_mail( $user_data['user_email'], sprintf( esc_attr__( '[%s] Your username and password' ), $blogname ), $message );

}

add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );
