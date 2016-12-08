<?php
/**
 * User Functions
 *
 * Functions related to users / donors
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Users Donations
 *
 * Retrieves a list of all donations by a specific user.
 *
 * @since  1.0
 *
 * @param int $user User ID or email address
 * @param int $number Number of donations to retrieve
 * @param bool $pagination
 * @param string $status
 *
 * @return bool|object List of all user donations
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

	$args = apply_filters( 'give_get_users_donations_args', array(
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

	$purchases = give_get_payments( apply_filters( 'give_get_users_donations_args', $args ) );

	// No donations
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
 * @return bool|object List of unique forms donated by user
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

	// Get all the items donated
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

	// Grab only the post ids "form_id" of the forms donated on this order
	$completed_donations_ids = array();
	foreach ( $donation_data as $purchase_meta ) {
		$completed_donations_ids[] = isset($purchase_meta['form_id']) ? $purchase_meta['form_id'] : '';
	}

	if ( empty( $completed_donations_ids ) ) {
		return false;
	}

	// Only include each donation once
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
 * Has donations
 *
 * Checks to see if a user has donated to at least one form.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int $user_id The ID of the user to check.
 *
 * @return      bool True if has donated, false other wise.
 */
function give_has_purchases( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( give_get_users_purchases( $user_id, 1 ) ) {
		return true; // User has at least one donation
	}

	return false; // User has never donated anything
}


/**
 * Get Donation Status for User
 *
 * Retrieves the donation count and the total amount spent for a specific user
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor to retrieve stats for
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

	/**
	 * Filter the donation stats
	 *
	 * @since 1.7
	 */
	$stats = (array) apply_filters( 'give_donation_stats_by_user', $stats, $user );

	return $stats;
}


/**
 * Count number of donations of a donor
 *
 * Returns total number of donations a donor has made
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      int The total number of donations
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
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      float The total amount the user has spent
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
 * @param       string $username The username to validate.
 *
 * @return      bool
 */
function give_validate_username( $username ) {
	$sanitized = sanitize_user( $username, false );
	$valid     = ( $sanitized == $username );

	return (bool) apply_filters( 'give_validate_username', $valid, $username );
}


/**
 * Looks up donations by email that match the registering user
 *
 * This is for users that donated as a guest and then came
 * back and created an account.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int $user_id The new user's ID.
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
 *
 * @return        int The total number of donors.
 */
function give_count_total_customers() {
	return Give()->customers->count();
}


/**
 * Returns the saved address for a donor
 *
 * @access        public
 * @since         1.0
 *
 * @param         int $user_id The donor ID.
 *
 * @return        array The donor's address, if any
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
 * Sends the new user notification email when a user registers within the donation form
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

	/* translators: %s: site name */
	$message  = sprintf( esc_attr__( 'New user registration on your site %s:', 'give' ), $blogname ) . "\r\n\r\n";
	/* translators: %s: user login */
	$message .= sprintf( esc_attr__( 'Username: %s', 'give' ), $user_data['user_login'] ) . "\r\n\r\n";
	/* translators: %s: user email */
	$message .= sprintf( esc_attr__( 'E-mail: %s', 'give' ), $user_data['user_email'] ) . "\r\n";

	@wp_mail(
		get_option( 'admin_email' ),
		sprintf(
			/* translators: %s: site name */
			esc_attr__( '[%s] New User Registration', 'give' ),
			$blogname
		),
		$message
	);

	/* translators: %s: user login */
	$message  = sprintf( esc_attr__( 'Username: %s', 'give' ), $user_data['user_login'] ) . "\r\n";
	/* translators: %s: paswword */
	$message .= sprintf( esc_attr__( 'Password: %s', 'give' ), esc_attr__( '[Password entered during donation]', 'give' ) ) . "\r\n";

	$message .= '<a href="' . wp_login_url() . '"> ' . esc_attr__( 'Click Here to Login &raquo;', 'give' ) . '</a>' . "\r\n";

	wp_mail(
		$user_data['user_email'],
		sprintf(
			/* translators: %s: site name */
			esc_attr__( '[%s] Your username and password', 'give' ),
			$blogname
		),
		$message
	);

}

add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );
