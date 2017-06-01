<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Give
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Checks if Guest checkout is enabled for a particular donation form
 *
 * @since      1.0
 * @deprecated 1.4.1
 *
 * @param int $form_id
 *
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function give_no_guest_checkout( $form_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.4.1', null, $backtrace );

	$ret = give_get_meta( $form_id, '_give_logged_in_only', true );

	return (bool) apply_filters( 'give_no_guest_checkout', give_is_setting_enabled( $ret ) );
}


/**
 * Default Log Views
 *
 * @since      1.0
 * @deprecated 1.8
 * @return array $views Log Views
 */
function give_log_default_views() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8', null, $backtrace );

	$views = array(
		'sales'          => __( 'Donations', 'give' ),
		'gateway_errors' => __( 'Payment Errors', 'give' ),
		'api_requests'   => __( 'API Requests', 'give' ),
	);

	$views = apply_filters( 'give_log_views', $views );

	return $views;
}

/**
 * Donation form validate agree to "Terms and Conditions".
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_agree_to_terms() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_agree_to_terms', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_agree_to_terms();

}

/**
 * Donation Form Validate Logged In User.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_logged_in_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_logged_in_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_logged_in_user();

}

/**
 * Donation Form Validate Logged In User.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_gateway() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_gateway', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_gateway();

}

/**
 * Donation Form Validate Fields.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_fields() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_fields', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_fields();

}

/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_cc() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_cc', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_cc();

}

/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_get_purchase_cc_info() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_cc_info', $backtrace );

	// Call new renamed function.
	give_get_donation_cc_info();

}


/**
 * Validates the credit card info.
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param int    $zip
 * @param string $country_code
 */
function give_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_cc_zip', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_cc_zip( $zip, $country_code );

}

/**
 * Donation form validate user login.
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_user_login() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_user_login', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_user_login();

}

/**
 * Donation Form Validate Guest User
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_guest_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_guest_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_guest_user();

}

/**
 * Donate Form Validate New User
 *
 * @since      1.0
 * @deprecated 1.8.8
 */
function give_purchase_form_validate_new_user() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_donation_form_validate_new_user', $backtrace );

	// Call new renamed function.
	give_donation_form_validate_new_user();

}


/**
 * Get Donation Form User
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param array $valid_data
 */
function give_get_purchase_form_user( $valid_data = array() ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_form_user', $backtrace );

	// Call new renamed function.
	give_get_donation_form_user( $valid_data );

}

/**
 * Give Checkout Button.
 *
 * Renders the button on the Checkout.
 *
 * @since      1.0
 * @deprecated 1.8.8
 *
 * @param  int $form_id The form ID.
 *
 * @return string
 */
function give_checkout_button_purchase( $form_id ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.8', 'give_get_donation_form_submit_button', $backtrace );

	return give_get_donation_form_submit_button( $form_id );

}

/**
 * Get the donor ID associated with a payment.
 *
 * @since 1.0
 *
 * @param int $payment_id Payment ID.
 *
 * @return int $customer_id Customer ID.
 */
function give_get_payment_customer_id( $payment_id ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_payment_donor_id', $backtrace );

	return give_get_payment_donor_id( $payment_id );
}


/**
 * Get Total Donations.
 *
 * @since  1.0
 *
 * @return int $count Total sales.
 */
function give_get_total_sales() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_total_donations', $backtrace );

	return give_get_total_donations();
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
 * @return      int The total number of donations
 */
function give_count_purchases_of_customer( $user = null ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_donations_of_donor', $backtrace );

	return give_count_donations_of_donor( $user );
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
function give_get_purchase_stats_by_user( $user = '' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_donations_of_donor', $backtrace );

	return give_get_donation_stats_by_user( $user );

}

/**
 * Get Users Donations
 *
 * Retrieves a list of all donations by a specific user.
 *
 * @since  1.0
 *
 * @param int    $user   User ID or email address
 * @param int    $number Number of donations to retrieve
 * @param bool   $pagination
 * @param string $status
 *
 * @return bool|object List of all user donations
 */
function give_get_users_purchases( $user = 0, $number = 20, $pagination = false, $status = 'complete' ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_donations_of_donor', $backtrace );

	return give_get_users_donations( $user, $number, $pagination, $status );

}