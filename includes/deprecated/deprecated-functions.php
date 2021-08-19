<?php
/**
 * Deprecated Functions
 *
 * All functions that have been deprecated.
 *
 * @package     Give
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.4.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Deprecated global variables.
 *
 * @since 2.0
 */
function _give_load_deprecated_global_params( $give_object ) {
	$GLOBALS['give_logs'] = Give()->logs;
	$GLOBALS['give_cron'] = Give_Cron::get_instance();
}

add_action( 'give_init', '_give_load_deprecated_global_params' );


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

	$views = [
		'sales'          => __( 'Donations', 'give' ),
		'gateway_errors' => __( 'Payment Errors', 'give' ),
		'api_requests'   => __( 'API Requests', 'give' ),
	];

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
function give_get_purchase_form_user( $valid_data = [] ) {

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

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_donation_stats_by_user', $backtrace );

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

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_get_users_donations', $backtrace );

	return give_get_users_donations( $user, $number, $pagination, $status );

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

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_has_donations', $backtrace );

	return give_has_donations( $user_id );
}

/**
 * Counts the total number of donors.
 *
 * @access        public
 * @since         1.0
 *
 * @return        int The total number of donors.
 */
function give_count_total_customers() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_count_total_donors', $backtrace );

	return give_count_total_donors();
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
function give_purchase_total_of_user( $user = null ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_donation_total_of_user', $backtrace );

	return give_donation_total_of_user( $user );
}

/**
 * Deletes a Donation
 *
 * @since  1.0
 *
 * @param  int  $payment_id      Payment ID (default: 0).
 * @param  bool $update_customer If we should update the customer stats (default:true).
 *
 * @return void
 */
function give_delete_purchase( $payment_id = 0, $update_customer = true ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_delete_donation', $backtrace );

	give_delete_donation( $payment_id, $update_customer );

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

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_undo_donation', $backtrace );

	give_undo_donation( $payment_id );
}


/**
 * Trigger a Donation Deletion.
 *
 * @since 1.0
 *
 * @param array $data Arguments passed.
 *
 * @return void
 */
function give_trigger_purchase_delete( $data ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_trigger_donation_delete', $backtrace );

	give_trigger_donation_delete( $data );
}


/**
 * Increases the donation total count of a donation form.
 *
 * @since 1.0
 *
 * @param int $form_id  Give Form ID
 * @param int $quantity Quantity to increase donation count by
 *
 * @return bool|int
 */
function give_increase_purchase_count( $form_id = 0, $quantity = 1 ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_increase_donation_count', $backtrace );

	give_increase_donation_count( $form_id, $quantity );
}


/**
 * Record Donation In Log
 *
 * Stores log information for a donation.
 *
 * @since 1.0
 *
 * @param int         $give_form_id Give Form ID.
 * @param int         $payment_id   Payment ID.
 * @param bool|int    $price_id     Price ID, if any.
 * @param string|null $sale_date    The date of the sale.
 *
 * @return void
 */
function give_record_sale_in_log( $give_form_id = 0, $payment_id, $price_id = false, $sale_date = null ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'give_record_donation_in_log', $backtrace );

	give_record_donation_in_log( $give_form_id, $payment_id, $price_id, $sale_date );
}

/**
 * Print Errors
 *
 * Prints all stored errors. Ensures errors show up on the appropriate form;
 * For use during donation process. If errors exist, they are returned.
 *
 * @since 1.0
 * @uses  give_get_errors()
 * @uses  give_clear_errors()
 *
 * @param int $form_id Form ID.
 *
 * @return void
 */
function give_print_errors( $form_id ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'Give_Notice::print_frontend_errors', $backtrace );

	do_action( 'give_frontend_notices', $form_id );
}

/**
 * Give Output Error
 *
 * Helper function to easily output an error message properly wrapped; used commonly with shortcodes
 *
 * @since      1.3
 *
 * @param string $message  Message to store with the error.
 * @param bool   $echo     Flag to print or return output.
 * @param string $error_id ID of the error being set.
 *
 * @return   string  $error
 */
function give_output_error( $message, $echo = true, $error_id = 'warning' ) {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.9', 'Give_Notice::print_frontend_notice', $backtrace );

	Give_Notices::print_frontend_notice( $message, $echo, $error_id );
}


/**
 * Get Donation Summary
 *
 * Retrieves the donation summary.
 *
 * @since       1.0
 *
 * @param array $purchase_data
 * @param bool  $email
 *
 * @return string
 */
function give_get_purchase_summary( $purchase_data, $email = true ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.12', 'give_payment_gateway_donation_summary', $backtrace );

	give_payment_gateway_donation_summary( $purchase_data, $email );

}

/**
 * Retrieves the emails for which admin notifications are sent to (these can be changed in the Give Settings).
 *
 * @since      1.0
 * @deprecated 2.0
 *
 * @return mixed
 */
function give_get_admin_notice_emails() {

	$email_option = give_get_option( 'admin_notice_emails' );

	$emails = ! empty( $email_option ) && strlen( trim( $email_option ) ) > 0 ? $email_option : get_bloginfo( 'admin_email' );
	$emails = array_map( 'trim', explode( "\n", $emails ) );

	return apply_filters( 'give_admin_notice_emails', $emails );
}

/**
 * Checks whether admin donation notices are disabled
 *
 * @since      1.0
 * @deprecated 2.0
 *
 * @param int $payment_id
 *
 * @return mixed
 */
function give_admin_notices_disabled( $payment_id = 0 ) {
	return apply_filters(
		'give_admin_notices_disabled',
		! give_is_setting_enabled( Give_Email_Notification::get_instance( 'new-donation' )->get_notification_status() ),
		$payment_id
	);
}


/** Generate Item Title for Payment Gateway
 *
 * @param array $payment_data Payment Data.
 *
 * @since 1.8.14
 *
 * @return string
 */
function give_build_paypal_item_title( $payment_data ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '1.8.14', 'give_payment_gateway_item_title', $backtrace );

	return give_payment_gateway_item_title( $payment_data );

}


/**
 * Set the number of decimal places per currency
 *
 * @since      1.0
 * @since      1.6 $decimals parameter removed from function params
 * @deprecated 1.8.15
 * *
 * @return int $decimals
 */
function give_currency_decimal_filter() {
	// Set default number of decimals.
	$decimals = give_get_price_decimals();

	// Get number of decimals with backward compatibility ( version < 1.6 )
	if ( 1 <= func_num_args() ) {
		$decimals = ( false === func_get_arg( 0 ) ? $decimals : absint( func_get_arg( 0 ) ) );
	}

	$currency = give_get_currency();

	switch ( $currency ) {
		// case 'RIAL' :
		case 'JPY':
		case 'KRW':
			// case 'TWD' :
			// case 'HUF' :

			$decimals = 0;
			break;
	}

	return apply_filters( 'give_currency_decimal_count', $decimals, $currency );
}


/**
 * Get field custom attributes as string.
 *
 * @since      1.8
 * @deprecated 1.8.17
 *
 * @param $field
 *
 * @return string
 */
function give_get_custom_attributes( $field ) {
	// Custom attribute handling
	$custom_attributes = '';

	if ( ! empty( $field['attributes'] ) && is_array( $field['attributes'] ) ) {
		$custom_attributes = give_get_attribute_str( $field['attributes'] );
	}

	return $custom_attributes;
}


/**
 * Get Payment Amount
 *
 * Get the fully formatted payment amount which is sent through give_currency_filter()
 * and give_format_amount() to format the amount correctly.
 *
 * @param int $payment_id Payment ID.
 *
 * @since      1.0
 * @deprecated 1.8.17
 *
 * @return string $amount Fully formatted payment amount.
 */
function give_payment_amount( $payment_id ) {
	return give_donation_amount( $payment_id );
}

/**
 * Get Payment Amount
 *
 * Get the fully formatted payment amount which is sent through give_currency_filter()
 * and give_format_amount() to format the amount correctly.
 *
 * @param int $payment_id Payment ID.
 *
 * @since      1.0
 * @deprecated 1.8.17
 *
 * @return string $amount Fully formatted payment amount.
 */
function give_get_payment_amount( $payment_id ) {
	return give_donation_amount( $payment_id );
}

/**
 * Decrease form earnings.
 *
 * @deprecated 1.8.17
 *
 * @param int    $form_id
 * @param     $amount
 *
 * @return bool|int
 */
function give_decrease_earnings( $form_id = 0, $amount ) {
	return give_decrease_form_earnings( $form_id, $amount );
}

/**
 * Retrieve the donation ID based on the key
 *
 * @param string $key the key to search for.
 *
 * @since      1.0
 * @deprecated 1.8.18
 *
 * @return int $purchase Donation ID.
 */
function give_get_purchase_id_by_key( $key ) {
	return give_get_donation_id_by_key( $key );
}

/**
 * Retrieve Donation Form Title with/without Donation Levels.
 *
 * @param array  $meta       List of Donation Meta.
 * @param bool   $only_level True/False, whether to show only level or not.
 * @param string $separator  Display separator symbol to separate the form title and donation level.
 *
 * @since 2.0
 *
 * @return string
 */
function give_get_payment_form_title( $meta, $only_level = false, $separator = '' ) {

	_give_deprecated_function(
		__FUNCTION__,
		'2.0',
		'give_get_donation_form_title'
	);

	$donation = '';
	if ( is_array( $meta ) && ! empty( $meta['key'] ) ) {
		$donation = give_get_payment_by( 'key', $meta['key'] );
	}

	$args = [
		'only_level' => $only_level,
		'separator'  => $separator,
	];

	return give_get_donation_form_title( $donation, $args );
}

/**
 * This function is used to delete donor for bulk actions on donor listing page.
 *
 * @param array $args List of arguments to delete donor.
 *
 * @since 2.2
 */
function give_delete_donor( $args ) {

	_give_deprecated_function(
		__FUNCTION__,
		'2.2',
		'give_process_donor_deletion'
	);

	give_process_donor_deletion( $args );
}


/**
 * Retrieve all donor comment attached to a donation
 *
 * Note: currently donor can only add one comment per donation
 *
 * @param int    $donor_id The donor ID to retrieve comment for.
 * @param array  $comment_args
 * @param string $search   Search for comment that contain a search term.
 *
 * @since 2.2.0
 * @deprecated 2.3.0
 *
 * @return array
 */
function give_get_donor_donation_comments( $donor_id, $comment_args = [], $search = '' ) {
	_give_deprecated_function(
		__FUNCTION__,
		'2.3.0',
		'Give()->comment->db'
	);

	$comments = Give_Comment::get(
		$donor_id,
		'payment',
		$comment_args,
		$search
	);

	return ( ! empty( $comments ) ? $comments : [] );
}

/**
 * Converts a PHP date format for use in JavaScript.
 *
 * @since 2.2.0
 * @deprecated 2.3.0
 *
 * @param string $php_format The PHP date format.
 *
 * @return string The JS date format.
 */
function give_convert_php_date_format_to_js( $php_format ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.3.0', null, $backtrace );

	$js_format = $php_format;

	switch ( $php_format ) {
		case 'F j, Y':
			$js_format = 'MM dd, yy';
			break;
		case 'Y-m-d':
			$js_format = 'yy-mm-dd';
			break;
		case 'm/d/Y':
			$js_format = 'mm/dd/yy';
			break;
		case 'd/m/Y':
			$js_format = 'dd/mm/yy';
			break;
	}

	/**
	 * Filters the date format for use in JavaScript.
	 *
	 * @since 2.2.0
	 *
	 * @param string $js_format  The JS date format.
	 * @param string $php_format The PHP date format.
	 */
	$js_format = apply_filters( 'give_js_date_format', $js_format, $php_format );

	return $js_format;
}

/**
 * Get localized date format for use in JavaScript.
 *
 * @since 2.2.0
 * @deprecated 2.3.0
 *
 * @return string.
 */
function give_get_localized_date_format_to_js() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.3.0', null, $backtrace );

	return give_convert_php_date_format_to_js( get_option( 'date_format' ) );
}

/**
 * Get donor latest comment
 *
 * @since 2.2.0
 * @deprecated 2.3.0
 *
 * @param int $donor_id
 * @param int $form_id
 *
 * @return WP_Comment/stdClass/array
 */
function give_get_donor_latest_comment( $donor_id, $form_id = 0 ) {
	global $wpdb;

	_give_deprecated_function(
		__FUNCTION__,
		'2.3.0',
		'Give()->comment->db'
	);

	// Backward compatibility.
	if ( ! give_has_upgrade_completed( 'v230_move_donor_note' ) ) {

		$comment_args = [
			'post_id'    => 0,
			'orderby'    => 'comment_ID',
			'order'      => 'DESC',
			'number'     => 1,
			'meta_query' => [
				'related' => 'AND',
				[
					'key'   => '_give_donor_id',
					'value' => $donor_id,
				],
				[
					'key'   => '_give_anonymous_donation',
					'value' => 0,
				],
			],
		];

		// Get donor donation comment for specific form.
		if ( $form_id ) {
			$comment_args['parent'] = $form_id;
		}

		$comment = current( give_get_donor_donation_comments( $donor_id, $comment_args ) );

		return $comment;
	}

	$comment_args = [
		'orderby'    => 'comment_ID',
		'order'      => 'DESC',
		'number'     => 1,
		'meta_query' => [
			'relation' => 'AND',
			[
				'key'   => '_give_anonymous_donation',
				'value' => 0,
			],
			[
				'key'   => '_give_donor_id',
				'value' => $donor_id,
			],
		],
	];

	// Get donor donation comment for specific form.
	if ( $form_id ) {
		$comment_args['meta_query'][] = [
			'key'   => '_give_form_id',
			'value' => $form_id,
		];
	}

	$sql = Give()->comment->db->get_sql( $comment_args );

	$comment = current( $wpdb->get_results( $sql ) );

	return $comment;
}

/**
 * Email template tag: {receipt_id}
 *
 * @since      1.0
 * @deprecated 2.4.0
 *
 * @param array $tag_args
 *
 * @return string receipt_id
 */
function give_email_tag_receipt_id( $tag_args ) {
	$receipt_id = '';
	// Backward compatibility.
	$tag_args = __give_20_bc_str_type_email_tag_param( $tag_args );
	switch ( true ) {
		case give_check_variable( $tag_args, 'isset', 0, 'payment_id' ):
			$receipt_id = give_get_payment_key( $tag_args['payment_id'] );
			break;
	}

	/**
	 * Filter the {receipt_id} email template tag output.
	 *
	 * @since 2.0
	 *
	 * @param string $receipt_id
	 * @param array  $tag_args
	 */
	return apply_filters( 'give_email_tag_receipt_id', $receipt_id, $tag_args );
}


/**
 * Check site host
 *
 * @since 1.0
 *
 * @param bool /string $host The host to check
 *
 * @return bool true if host matches, false if not
 */
function give_is_host( $host = false ) {

	_give_deprecated_function(
		__FUNCTION__,
		'2.4.2',
		'give_get_host'
	);

	$return = false;

	if ( $host ) {
		$host = str_replace( ' ', '', strtolower( $host ) );

		switch ( $host ) {
			case 'wpengine':
				if ( defined( 'WPE_APIKEY' ) ) {
					$return = true;
				}
				break;
			case 'pagely':
				if ( defined( 'PAGELYBIN' ) ) {
					$return = true;
				}
				break;
			case 'icdsoft':
				if ( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
					$return = true;
				}
				break;
			case 'networksolutions':
				if ( DB_HOST == 'mysqlv5' ) {
					$return = true;
				}
				break;
			case 'ipage':
				if ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'ipower':
				if ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'mediatemplegrid':
				if ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'pairnetworks':
				if ( strpos( DB_HOST, '.pair.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'rackspacecloud':
				if ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
					$return = true;
				}
				break;
			case 'sysfix.eu':
			case 'sysfix.eupowerhosting':
				if ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
					$return = true;
				}
				break;
			case 'flywheel':
				if ( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
					$return = true;
				}
				break;
			default:
				$return = false;
		}// End switch().
	}// End if().

	return $return;
}

/**
 * Get list of premium add-ons
 *
 * @since 2.5.0
 * @deprecated 2.9.2
 *
 * @return array
 *
 */
function give_get_premium_add_ons() {
	$list = wp_extract_urls( give_add_ons_feed( 'addons-directory', false ) );
	$list = array_values(
		array_filter(
			$list,
			static function ( $url ) {
				return false !== strpos( $url, 'givewp.com/addons' );
			}
		)
	);

	return array_map(
		static function ( $url ) {
			$path = wp_parse_url( untrailingslashit( $url ) )['path'];

			return str_replace( '/addons/', '', $path );
		},
		$list
	);
}

/**
 * Displays Stripe Connect Button.
 *
 * @since 2.5.0
 * @deprecated @unrelesed
 *
 * @return string
 */
function give_stripe_connect_button() {
	_give_deprecated_function(
		__FUNCTION__,
		'2.13.0'
	);

	// Prepare Stripe Connect URL.
	$link = add_query_arg(
		[
			'stripe_action'         => 'connect',
			'mode'                  => give_is_test_mode() ? 'test' : 'live',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'give_stripe_connected' => '0',
		],
		esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);

	return sprintf(
		'<a href="%1$s" class="give-stripe-connect"><span>%2$s</span></a>',
		esc_url( $link ),
		esc_html__( 'Connect with Stripe', 'give' )
	);
}

/**
 * Stripe Disconnect URL.
 *
 * @param string $account_id   Stripe Account ID.
 * @param string $account_name Stripe Account Name.
 *
 * @since 2.5.0
 * @deprecated 2.13.0
 *
 * @return string
 */
function give_stripe_disconnect_url( $account_id = '', $account_name = '' ) {
	_give_deprecated_function(
		__FUNCTION__,
		'2.13.0'
	);

	$args = [
		'stripe_action'  => 'disconnect',
		'mode'           => give_is_test_mode() ? 'test' : 'live',
		'stripe_user_id' => $account_id,
		'return_url'     => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
	];

	// Send Account Name.
	if ( ! empty( $account_name ) ) {
		$args['account_name'] = $account_name;
	}

	// Prepare Stripe Disconnect URL.
	return add_query_arg(
		$args,
		esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);
}
