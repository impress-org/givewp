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
 * @param int    $user   User ID or email address.
 * @param int    $number Number of donations to retrieve.
 * @param bool   $pagination
 * @param string $status
 *
 * @return bool|array List of all user donations.
 */
function give_get_users_donations( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	if ( 0 === $user && ! Give()->email_access->token_exists ) {
		return false;
	}

	$status = $status === 'complete' ? 'publish' : $status;
	$paged = 1;

	if ( $pagination ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		}
	}

	$args = apply_filters( 'give_get_users_donations_args', array(
		'user'    => $user,
		'number'  => $number,
		'status'  => $status,
		'orderby' => 'date',
	) );

	if ( $pagination ) {
		$args['page'] = $paged;
	} else {
		$args['nopaging'] = true;
	}

	$by_user_id = is_numeric( $user ) ? true : false;
	$donor   = new Give_Donor( $user, $by_user_id );

	if ( ! empty( $donor->payment_ids ) ) {

		unset( $args['user'] );
		$args['post__in'] = array_map( 'absint', explode( ',', $donor->payment_ids ) );

	}

	$donations = give_get_payments( apply_filters( 'give_get_users_donations_args', $args ) );

	// No donations
	if ( ! $donations ) {
		return false;
	}

	return $donations;
}

/**
 * Get Users Donations
 *
 * Returns a list of unique donation forms given to by a specific user.
 *
 * @since  1.0
 *
 * @param int    $user User ID or email address
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

	$donor = new Give_Donor( $user, $by_user_id );

	if ( empty( $donor->payment_ids ) ) {
		return false;
	}

	// Get all the items donated.
	$payment_ids    = array_reverse( explode( ',', $donor->payment_ids ) );
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
	foreach ( $donation_data as $donation_meta ) {
		$completed_donations_ids[] = isset( $donation_meta['form_id'] ) ? $donation_meta['form_id'] : '';
	}

	if ( empty( $completed_donations_ids ) ) {
		return false;
	}

	// Only include each donation once
	$form_ids = array_unique( $completed_donations_ids );

	// Make sure we still have some products and a first item
	if ( empty( $form_ids ) || ! isset( $form_ids[0] ) ) {
		return false;
	}

	$post_type = get_post_type( $form_ids[0] );

	$args = apply_filters( 'give_get_users_completed_donations_args', array(
		'include'        => $form_ids,
		'post_type'      => $post_type,
		'posts_per_page' => - 1,
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
function give_has_donations( $user_id = null ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	if ( give_get_users_donations( $user_id, 1 ) ) {
		return true; // User has at least one donation.
	}

	// User has never donated anything.
	return false;
}


/**
 * Get Donation Status for User.
 *
 * Retrieves the donation count and the total amount spent for a specific user.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor to retrieve stats for.
 *
 * @return      array
 */
function give_get_donation_stats_by_user( $user = '' ) {

	$field = '';

	if ( is_email( $user ) ) {
		$field = 'email';
	} elseif ( is_numeric( $user ) ) {
		$field = 'user_id';
	}

	$stats    = array();
	$donor = Give()->donors->get_donor_by( $field, $user );

	if ( $donor ) {
		$donor = new Give_Donor( $donor->id );
		$stats['purchases']   = absint( $donor->purchase_count );
		$stats['total_spent'] = give_maybe_sanitize_amount( $donor->purchase_value );
	}

	/**
	 * Filter the donation stats.
	 *
	 * @since 1.7
	 */
	$stats = (array) apply_filters( 'give_donation_stats_by_user', $stats, $user );

	return $stats;
}


/**
 * Count number of donations of a donor.
 *
 * Returns total number of donations a donor has made.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      int The total number of donations.
 */
function give_count_donations_of_donor( $user = null ) {

	// Logged in?
	if ( empty( $user ) ) {
		$user = get_current_user_id();
	}

	// Email access?
	if ( empty( $user ) && Give()->email_access->token_email ) {
		$user = Give()->email_access->token_email;
	}

	$stats = ! empty( $user ) ? give_get_donation_stats_by_user( $user ) : false;

	return isset( $stats['purchases'] ) ? $stats['purchases'] : 0;
}

/**
 * Calculates the total amount spent by a user.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int|string $user The ID or email of the donor.
 *
 * @return      float The total amount the user has spent
 */
function give_donation_total_of_user( $user = null ) {

	$stats = give_get_donation_stats_by_user( $user );

	return $stats['total_spent'];
}


/**
 * Validate a potential username.
 *
 * @since 1.0
 *
 * @param string $username The username to validate.
 * @param int    $form_id
 *
 * @return bool
 */
function give_validate_username( $username, $form_id = 0 ) {
	$valid = true;

	// Validate username.
	if ( ! empty( $username ) ) {

		// Sanitize username.
		$sanitized_user_name = sanitize_user( $username, false );

		// We have an user name, check if it already exists.
		if ( username_exists( $username ) ) {
			// Username already registered.
			give_set_error( 'username_unavailable', __( 'Username already taken.', 'give' ) );
			$valid = false;

			// Check if it's valid.
		} elseif ( $sanitized_user_name !== $username ) {
			// Invalid username.
			if ( is_multisite() ) {
				give_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed.', 'give' ) );
				$valid = false;
			} else {
				give_set_error( 'username_invalid', __( 'Invalid username.', 'give' ) );
				$valid = false;
			}
		}
	} else {
		// Username is empty.
		give_set_error( 'username_empty', __( 'Enter a username.', 'give' ) );
		$valid = false;

		// Check if guest checkout is disable for form.
		if ( $form_id && give_logged_in_only( $form_id ) ) {
			give_set_error( 'registration_required', __( 'You must register or login to complete your donation.', 'give' ) );
			$valid = false;
		}
	}

	/**
	 * Filter the username validation result.
	 *
	 * @since 1.8
	 *
	 * @param bool   $valid
	 * @param string $username
	 * @param bool   $form_id
	 */
	$valid = (bool) apply_filters( 'give_validate_username', $valid, $username, $form_id );

	return $valid;
}


/**
 * Validate user email.
 *
 * @since 1.8
 *
 * @param string $email                User email.
 * @param bool   $registering_new_user Flag to check user register or not.
 *
 * @return bool
 */
function give_validate_user_email( $email, $registering_new_user = false ) {
	$valid = true;

	if ( empty( $email ) ) {
		// No email.
		give_set_error( 'email_empty', __( 'Enter an email.', 'give' ) );
		$valid = false;

	} elseif ( ! is_email( $email ) ) {
		// Validate email.
		give_set_error( 'email_invalid', __( 'Invalid email.', 'give' ) );
		$valid = false;

	} elseif ( $registering_new_user && ( give_donor_email_exists( $email ) || email_exists( $email ) ) ) {
		// Check if email exists.
		give_set_error( 'email_used', __( 'The email address provided is already active for another user.', 'give' ) );
		$valid = false;
	}

	/**
	 * Filter the email validation result.
	 *
	 * @since 1.8
	 *
	 * @param bool   $valid
	 * @param string $email
	 * @param bool   $registering_new_user
	 */
	$valid = (bool) apply_filters( 'give_validate_user_email', $valid, $email, $registering_new_user );

	return $valid;
}

/**
 * Validate password.
 *
 * @since 1.8
 *
 * @param string $password
 * @param string $confirm_password
 * @param bool   $registering_new_user
 *
 * @return bool
 */
function give_validate_user_password( $password = '', $confirm_password = '', $registering_new_user = false ) {
	$valid = true;

	// Passwords Validation For New Donors Only
	if ( $registering_new_user ) {
		// Password or confirmation missing.
		if ( ! $password ) {
			// The password is invalid.
			give_set_error( 'password_empty', __( 'Enter a password.', 'give' ) );
			$valid = false;
		} elseif ( ! $confirm_password ) {
			// Confirmation password is invalid.
			give_set_error( 'confirmation_empty', __( 'Enter the password confirmation.', 'give' ) );
			$valid = false;
		}
	}
	// Passwords Validation For New Donors as well as Existing Donors
	if( $password || $confirm_password ) {
		if ( strlen( $password ) < 6 || strlen( $confirm_password ) < 6 ) {
			// Seems Weak Password
			give_set_error( 'password_weak', __( 'Passwords should have at least 6 characters.', 'give' ) );
			$valid = false;
		}
		if ( $password && $confirm_password ) {
			// Verify confirmation matches.
			if ( $password != $confirm_password ) {
				// Passwords do not match
				give_set_error( 'password_mismatch', __( 'Passwords you entered do not match. Please try again.', 'give' ) );
				$valid = false;
			}
		}
	}

	/**
	 * Filter the password validation result.
	 *
	 * @since 1.8
	 *
	 * @param bool   $valid
	 * @param string $password
	 * @param string $confirm_password
	 * @param bool   $registering_new_user
	 */
	$valid = (bool) apply_filters( 'give_validate_user_email', $valid, $password, $confirm_password, $registering_new_user );

	return $valid;
}


/**
 * Looks up donations by email that match the registering user.
 *
 * This is for users that donated as a guest and then came back and created an account.
 *
 * @access      public
 * @since       1.0
 *
 * @param       int $user_id The new user's ID.
 *
 * @return      void
 */
function give_add_past_donations_to_new_user( $user_id ) {

	$email = get_the_author_meta( 'user_email', $user_id );

	$payments = give_get_payments( array(
		's' => $email,
	) );

	if ( $payments ) {
		foreach ( $payments as $payment ) {
			if ( intval( give_get_payment_user_id( $payment->ID ) ) > 0 ) {
				continue;
			} // End if().

			$meta                    = give_get_payment_meta( $payment->ID );
			$meta['user_info']       = maybe_unserialize( $meta['user_info'] );
			$meta['user_info']['id'] = $user_id;

			// Store the updated user ID in the payment meta.
			give_update_payment_meta( $payment->ID, '_give_payment_meta', $meta );
			give_update_payment_meta( $payment->ID, '_give_payment_user_id', $user_id );
		}
	}

}

add_action( 'user_register', 'give_add_past_donations_to_new_user' );


/**
 * Counts the total number of donors.
 *
 * @access        public
 * @since         1.0
 *
 * @return        int The total number of donors.
 */
function give_count_total_donors() {
	return Give()->donors->count();
}


/**
 * Returns the saved address for a donor
 *
 * @access        public
 * @since         1.0
 *
 * @param         int $user_id The donor ID.x
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
 * @param int   $user_id
 * @param array $user_data
 *
 * @return        void
 */
function give_new_user_notification( $user_id = 0, $user_data = array() ) {
	// Bailout.
	if ( empty( $user_id ) || empty( $user_data ) ) {
		return;
	}

	do_action( 'give_new-donor-register_email_notification', $user_id, $user_data );
	do_action( 'give_donor-register_email_notification', $user_id, $user_data );
}

add_action( 'give_insert_user', 'give_new_user_notification', 10, 2 );


/**
 * Get Donor Name By
 *
 * Retrieves the donor name based on the id and the name of the user or donation
 *
 * @access      public
 * @since       1.8.9
 *
 * @param       int    $id     The ID of donation or donor
 * @param       string $from   From will be a string to be passed as donation or donor
 *
 * @return      string
 */
function give_get_donor_name_by( $id = 0, $from = 'donation' ) {

	// ID shouldn't be empty
	if ( empty( $id ) ) {
		return;
	}

	$name = '';

	switch ( $from ) {

		case 'donation':

			$user_info = give_get_payment_meta_user_info( $id );
			$name = $user_info['first_name'] . ' ' . $user_info['last_name'];

		break;

		case 'donor':

			$donor = new Give_Donor( $id );
			$name = $donor->name;

		break;

	}

	return trim( $name );

}

/**
 * Checks whether the given donor email exists in users as well as additional_email of donors.
 *
 * @since 1.8.9
 *
 * @param  string   $email Donor Email.
 * @return boolean  The user's ID on success, and false on failure.
 */
function give_donor_email_exists( $email ) {
	if ( Give()->donors->get_donor_by( 'email', $email ) ) {
		return true;
	}
	return false;
}

