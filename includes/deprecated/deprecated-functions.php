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
 * @since 1.0
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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.8', null, $backtrace );

	// Call new renamed function.
	give_get_donation_cc_info();

}