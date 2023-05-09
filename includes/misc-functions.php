<?php
/**
 * Misc Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
use Give\License\PremiumAddonsListManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Test Mode Enabled.
 *
 * @return bool $ret True if return mode is enabled, false otherwise
 * @since 1.0
 */
function give_is_test_mode() {

	$ret = give_is_setting_enabled( give_get_option( 'test_mode' ) );

	return (bool) apply_filters( 'give_is_test_mode', $ret );

}

/**
 * Get the current page URL.
 *
 * @return string $current_url Current page URL.
 * @since 1.0
 */
function give_get_current_page_url() {

	global $wp;

	if ( get_option( 'permalink_structure' ) ) {
		$base = trailingslashit( home_url( $wp->request ) );
	} else {
		$base = add_query_arg( $wp->query_string, '', trailingslashit( home_url( $wp->request ) ) );
		$base = remove_query_arg( [ 'post_type', 'name' ], $base );
	}

	$scheme      = is_ssl() ? 'https' : 'http';
	$current_uri = set_url_scheme( $base, $scheme );

	if ( is_front_page() ) {
		$current_uri = home_url( '/' );
	}

	/**
	 * Filter the current page url
	 *
	 * @param string $current_uri
	 *
	 * @since 1.0
	 */
	return esc_url_raw( apply_filters( 'give_get_current_page_url', $current_uri ) );

}


/**
 * Verify credit card numbers live?
 *
 * @return bool $ret True is verify credit cards is live
 * @since 1.0
 */
function give_is_cc_verify_enabled() {

	$ret = true;

	/**
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards.
	 * Enable if using more than one gateway if they are not both PayPal and manual, again assuming credit card usage.
	 */
	$gateways = give_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} elseif ( count( $gateways ) == 1 ) {
		$ret = false;
	} elseif ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	/**
	 * Fire the filter
	 *
	 * @param bool $ret
	 *
	 * @since 1.0
	 */
	return (bool) apply_filters( 'give_is_cc_verify_enabled', $ret );
}

/**
 * Retrieve timezone.
 *
 * @return string $timezone The timezone ID.
 * @since 1.0
 */
function give_get_timezone_id() {

	// if site timezone string exists, return it.
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// get UTC offset, if it isn't set return UTC.
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) ) {
		return 'UTC';
	}

	// attempt to guess the timezone string from the UTC offset.
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually.
	if ( $timezone === false ) {

		$is_dst = date( 'I' );

		foreach ( timezone_abbreviations_list() as $abbr ) {
			foreach ( $abbr as $city ) {
				if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
					return $city['timezone_id'];
				}
			}
		}
	}

	// Fallback.
	return 'UTC';
}


/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @return string $ip User's IP address
 * @since 1.0
 */
function give_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		// check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		// to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Filter the IP
	 *
	 * @since 1.0
	 */
	$ip = apply_filters( 'give_get_ip', $ip );

	// Filter empty values.
	if ( false !== strpos( $ip, ',' ) ) {
		$ip = give_clean( explode( ',', $ip ) );
		$ip = array_filter( $ip );
		$ip = implode( ',', $ip );
	} else {
		$ip = give_clean( $ip );
	}

	return $ip;
}


/**
 * Store Donation Data in Sessions
 *
 * Used for storing info about donation
 *
 * @param $purchase_data
 *
 * @since 1.0
 *
 * @uses  Give()->session->set()
 */
function give_set_purchase_session( $purchase_data = [] ) {
	Give()->session->set( 'give_purchase', $purchase_data );
	Give()->session->set( 'give_email', $purchase_data['user_email'] );
}

/**
 * Retrieve Donation Data from Session
 *
 * Used for retrieving info about donation
 * after completing a donation
 *
 * @return mixed array | false
 * @uses  Give()->session->get()
 * @since 1.0
 */
function give_get_purchase_session() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Retrieve Payment Key of the Receipt Access Session.
 *
 * @return array|string
 * @since 1.8.17
 */
function give_get_receipt_session() {
	return Give()->session->get( 'receipt_access' );
}

/**
 * Retrieve Payment Key of the History Access Session.
 *
 * @return array|string
 * @since 1.8.17
 */
function give_get_history_session() {
	return (bool) Give()->session->get( 'history_access' );
}

/**
 * Generate Item Title for Payment Gateway.
 *
 * @since 1.8.14
 * @since 2.9.6  Function will return form title with selected form level if price id set to zero. Added second param to return result with requested character length.
 *
 * @param  array  $payment_data  Payment Data.
 *
 * @param  string|null  $length
 *
 * @return string By default, the name of the form. Then the price level text if any is found.
 */
function give_payment_gateway_item_title( $payment_data, $length = null ) {

	$form_id   = intval( $payment_data['post_data']['give-form-id'] );
	$item_name = isset( $payment_data['post_data']['give-form-title'] ) ? $payment_data['post_data']['give-form-title'] : '';
	$price_id  = isset( $payment_data['post_data']['give-price-id'] ) ? $payment_data['post_data']['give-price-id'] : '';

	// Verify has variable prices.
	if ( give_has_variable_prices( $form_id ) ) {

		$item_price_level_text = give_get_price_option_name( $form_id, $price_id, 0, false );

		/**
		 * Output donation level text if:
		 *
		 * 1. It's not a custom amount
		 * 2. The level field has actual text and isn't the amount (which is already displayed on the receipt).
		 */
		if ( 'custom' !== $price_id && ! empty( $item_price_level_text ) ) {
			// Matches a donation level - append level text.
			$item_name .= ' - ' . $item_price_level_text;
		}
	}

	/**
	 * Filter the Item Title of Payment Gateway.
	 *
	 * @param string $item_name    Item Title of Payment Gateway.
	 * @param int    $form_id      Donation Form ID.
	 * @param array  $payment_data Payment Data.
	 *
	 * @return string
	 * @since 1.8.14
	 */
	$item_name = apply_filters( 'give_payment_gateway_item_title', $item_name, $form_id, $payment_data );

	// Cut the length
	if ( $length ) {
		$item_name = substr( $item_name, 0, $length );
	}

	return $item_name;
}

/**
 * Get Donation Summary
 *
 * Creates a donation summary for payment gateways from the donation data before the payment is created in the database.
 *
 * @param array $donation_data
 * @param bool  $name_and_email
 * @param int   $length
 *
 * @return string
 * @since       1.8.12
 */
function give_payment_gateway_donation_summary( $donation_data, $name_and_email = true, $length = 255 ) {

	$form_id  = isset( $donation_data['post_data']['give-form-id'] ) ? $donation_data['post_data']['give-form-id'] : '';
	$price_id = isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : '';

	// Form title.
	$summary = ( ! empty( $donation_data['post_data']['give-form-title'] ) ? $donation_data['post_data']['give-form-title'] : ( ! empty( $form_id ) ? wp_sprintf( __( 'Donation Form ID: %d', 'give' ), $form_id ) : __( 'Untitled donation form', 'give' ) ) );

	// Form multilevel if applicable.
	if ( ! empty( $price_id ) && 'custom' !== $price_id ) {
		$summary .= ': ' . give_get_price_option_name( $form_id, $donation_data['post_data']['give-price-id'] );
	}

	// Add Donor's name + email if requested.
	if ( $name_and_email ) {

		// First name
		if ( isset( $donation_data['user_info']['first_name'] ) && ! empty( $donation_data['user_info']['first_name'] ) ) {
			$summary .= ' - ' . $donation_data['user_info']['first_name'];
		}

		if ( isset( $donation_data['user_info']['last_name'] ) && ! empty( $donation_data['user_info']['last_name'] ) ) {
			$summary .= ' ' . $donation_data['user_info']['last_name'];
		}

		$summary .= ' (' . $donation_data['user_email'] . ')';
	}

	// Cut the length
	$summary = substr( $summary, 0, $length );

	return apply_filters( 'give_payment_gateway_donation_summary', $summary );
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @return string $host if detected, false otherwise
 * @since 1.0
 */
function give_get_host() {
	$find_host = gethostname();

	if ( strpos( $find_host, 'sgvps.net' ) ) {
		$host = 'Siteground';
	} elseif ( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif ( defined( 'PAGELYBIN' ) || strpos( $find_host, 'pagelyhosting.com' ) ) {
		$host = 'Pagely';
	} elseif ( strpos( $find_host, 'secureserver.net' ) ) {
		$host = 'GoDaddy/Media Temple';
	} elseif ( DB_HOST == 'localhost:/tmp/mysql5.sock' ) {
		$host = 'ICDSoft';
	} elseif ( DB_HOST == 'mysqlv5' ) {
		$host = 'NetworkSolutions';
	} elseif ( strpos( DB_HOST, 'ipagemysql.com' ) !== false ) {
		$host = 'iPage';
	} elseif ( strpos( DB_HOST, 'ipowermysql.com' ) !== false ) {
		$host = 'IPower';
	} elseif ( strpos( DB_HOST, '.gridserver.com' ) !== false ) {
		$host = 'MediaTemple Grid';
	} elseif ( strpos( DB_HOST, '.pair.com' ) !== false ) {
		$host = 'pair Networks';
	} elseif ( strpos( DB_HOST, '.stabletransit.com' ) !== false ) {
		$host = 'Rackspace Cloud';
	} elseif ( strpos( DB_HOST, '.sysfix.eu' ) !== false ) {
		$host = 'SysFix.eu Power Hosting';
	} elseif ( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false || strpos( $find_host, 'fw' ) ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}

/**
 * Marks a function as deprecated and informs when it has been used.
 *
 * There is a hook give_deprecated_function_run that will be called that can be used
 * to get the backtrace up to what file and function called the deprecated
 * function.
 *
 * The current behavior is to trigger a user error if WP_DEBUG is true.
 *
 * This function is to be used in every function that is deprecated.
 *
 * @param string $function    The function that was called.
 * @param string $version     The plugin version that deprecated the function.
 * @param string $replacement Optional. The function that should have been called.
 * @param array  $backtrace   Optional. Contains stack backtrace of deprecated function.
 *
 * @uses do_action() Calls 'give_deprecated_function_run' and passes the function name, what to use instead,
 *       and the version the function was deprecated in.
 * @uses apply_filters() Calls 'give_deprecated_function_trigger_error' and expects boolean value of true to do
 *       trigger or false to not trigger error.
 */
function _give_deprecated_function( $function, $version, $replacement = null, $backtrace = null ) {

	/**
	 * Fires while give deprecated function call occurs.
	 *
	 * Allow you to hook to deprecated function call.
	 *
	 * @param string $function    The function that was called.
	 * @param string $replacement Optional. The function that should have been called.
	 * @param string $version     The plugin version that deprecated the function.
	 *
	 * @since 1.0
	 */
	do_action( 'give_deprecated_function_run', $function, $replacement, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger.
	if ( WP_DEBUG && apply_filters( 'give_deprecated_function_trigger_error', $show_errors ) ) {
		if ( ! is_null( $replacement ) ) {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since GiveWP version %2$s! Use %3$s instead.', 'give' ), $function, $version, $replacement ) );
			trigger_error( print_r( $backtrace, 1 ) ); // Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		} else {
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since GiveWP version %2$s with no alternative available.', 'give' ), $function, $version ) );
			trigger_error( print_r( $backtrace, 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
			// Alternatively we could dump this to a file.
		}
	}
}

/**
 * Give Get Admin ID
 *
 * Helper function to return the ID of the post for admin usage
 *
 * @return string $post_id
 */
function give_get_admin_post_id() {
	$post_id = isset( $_REQUEST['post'] ) ? absint( $_REQUEST['post'] ) : null;

	$post_id = ! empty( $post_id ) ? $post_id : ( isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : null );

	$post_id = ! empty( $post_id ) ? $post_id : ( isset( $_REQUEST['post_ID'] ) ? absint( $_REQUEST['post_ID'] ) : null );

	return $post_id;
}

/**
 * Get PHP Arg Separator Output
 *
 * @return string Arg separator output
 * @since 1.0
 */
function give_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}


/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @param int $n
 *
 * @return string Short month name
 * @since 1.0
 */
function give_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( 'M', $timestamp );
}

/**
 * Checks whether function is disabled.
 *
 * @param string $function Name of the function.
 *
 * @return bool Whether or not function is disabled.
 * @since 1.0
 */
function give_is_func_disabled( $function ) {
	$disabled = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}


/**
 * Create SVG library function
 *
 * @param string $icon
 *
 * @return string
 */
function give_svg_icons( $icon ) {

	// Store your SVGs in an associative array
	$svgs = [
		'microphone'    => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjY0cHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgLTIwIDY0IDEyMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgNjQgMTAwOyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8Zz4NCgk8Zz4NCgkJPHBhdGggZD0iTTYyLDM2LjIxNWgtM2MtMS4xLDAtMiwwLjktMiwyVjUyYzAsNi42ODYtNS4yNjYsMTgtMjUsMThTNyw1OC42ODYsNyw1MlYzOC4yMTVjMC0xLjEtMC45LTItMi0ySDJjLTEuMSwwLTIsMC45LTIsMlY1Mg0KCQkJYzAsMTEuMTg0LDguMjE1LDIzLjE1MiwyNywyNC44MDFWOTBIMTRjLTEuMSwwLTIsMC44OTgtMiwydjZjMCwxLjEsMC45LDIsMiwyaDM2YzEuMSwwLDItMC45LDItMnYtNmMwLTEuMTAyLTAuOS0yLTItMkgzN1Y3Ni44MDENCgkJCUM1NS43ODUsNzUuMTUyLDY0LDYzLjE4NCw2NCw1MlYzOC4yMTVDNjQsMzcuMTE1LDYzLjEsMzYuMjE1LDYyLDM2LjIxNXoiLz4NCgkJPHBhdGggZD0iTTMyLDYwYzExLjczMiwwLDE1LTQuODE4LDE1LThWMzYuMjE1SDE3VjUyQzE3LDU1LjE4MiwyMC4yNjYsNjAsMzIsNjB6Ii8+DQoJCTxwYXRoIGQ9Ik00Nyw4YzAtMy4xODQtMy4yNjgtOC0xNS04QzIwLjI2NiwwLDE3LDQuODE2LDE3LDh2MjEuMjE1aDMwVjh6Ii8+DQoJPC9nPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPC9zdmc+DQo=',
		'alert'         => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE2LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPCFET0NUWVBFIHN2ZyBQVUJMSUMgIi0vL1czQy8vRFREIFNWRyAxLjEvL0VOIiAiaHR0cDovL3d3dy53My5vcmcvR3JhcGhpY3MvU1ZHLzEuMS9EVEQvc3ZnMTEuZHRkIj4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgd2lkdGg9IjI4LjkzOHB4IiBoZWlnaHQ9IjI1LjAwNXB4IiB2aWV3Qm94PSIwIDAgMjguOTM4IDI1LjAwNSIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMjguOTM4IDI1LjAwNTsiDQoJIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHBhdGggc3R5bGU9ImZpbGw6IzAwMDAwMDsiIGQ9Ik0yOC44NTksMjQuMTU4TDE0Ljk1NywwLjI3OUMxNC44NTYsMC4xMDYsMTQuNjcsMCwxNC40NjgsMGMtMC4xOTgsMC0wLjM4MywwLjEwNi0wLjQ4MSwwLjI3OQ0KCUwwLjA3OSwyNC4xNThjLTAuMTAyLDAuMTc1LTAuMTA2LDAuMzg5LTAuMDA2LDAuNTY1YzAuMTAzLDAuMTc0LDAuMjg3LDAuMjgyLDAuNDg4LDAuMjgyaDI3LjgxNGMwLjIwMSwwLDAuMzg5LTAuMTA4LDAuNDg4LTAuMjgyDQoJYzAuMDQ3LTAuMDg4LDAuMDc0LTAuMTg2LDAuMDc0LTAuMjgxQzI4LjkzOCwyNC4zNDMsMjguOTExLDI0LjI0NSwyOC44NTksMjQuMTU4eiBNMTYuMzY5LDguNDc1bC0wLjQ2Miw5LjQ5M2gtMi4zODlsLTAuNDYxLTkuNDkzDQoJSDE2LjM2OXogTTE0LjcxMSwyMi44MjhoLTAuMDQyYy0xLjA4OSwwLTEuODQzLTAuODE3LTEuODQzLTEuOTA3YzAtMS4xMzEsMC43NzQtMS45MDcsMS44ODUtMS45MDdzMS44NDYsMC43NzUsMS44NjcsMS45MDcNCglDMTYuNTc5LDIyLjAxMSwxNS44NDQsMjIuODI4LDE0LjcxMSwyMi44Mjh6Ii8+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8L3N2Zz4NCg==',
		'placemark'     => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB3aWR0aD0iMTAwcHgiIGhlaWdodD0iMTAwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAxMDAgMTAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxnPg0KCTxwYXRoIGQ9Ik01MC40MzQsMjAuMjcxYy0xMi40OTksMC0yMi42NjgsMTAuMTY5LTIyLjY2OCwyMi42NjhjMCwxMS44MTQsMTguODE1LDMyLjE1NSwyMC45NiwzNC40MzdsMS43MDgsMS44MTZsMS43MDgtMS44MTYNCgkJYzIuMTQ1LTIuMjgxLDIwLjk2LTIyLjYyMywyMC45Ni0zNC40MzdDNzMuMTAzLDMwLjQ0LDYyLjkzNCwyMC4yNzEsNTAuNDM0LDIwLjI3MXogTTUwLjQzNCw1Mi4zMmMtNS4xNzIsMC05LjM4LTQuMjA4LTkuMzgtOS4zOA0KCQlzNC4yMDgtOS4zOCw5LjM4LTkuMzhjNS4xNzMsMCw5LjM4LDQuMjA4LDkuMzgsOS4zOFM1NS42MDcsNTIuMzIsNTAuNDM0LDUyLjMyeiIvPg0KPC9nPg0KPC9zdmc+DQo=',
		'give_grey'     => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiPjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iTGF5ZXJfMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjEwMC4xIDAgNDAwIDQwMCIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAxMDAuMSAwIDQwMCA0MDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxnIGlkPSJMYXllcl8xXzFfIj48Y2lyY2xlIGZpbGw9IiM2NkJCNkEiIGN4PSItNDA3LjMiIGN5PSIzNDYuMyIgcj0iNDIuMiIvPjxnPjxnPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzg2LjQsMTMzLjh2LTEyLjVoNC44YzMuOCwwLDYuNiwyLjUsNi42LDYuNHMtMi44LDYuNC02LjYsNi40aC00LjhWMTMzLjh6IE0tNzc3LjUsMTI3LjVjMC0yLjMtMS4zLTMuOC0zLjgtMy44aC0yLjN2Ny45aDIuM0MtNzc5LDEzMS42LTc3Ny41LDEyOS44LTc3Ny41LDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzcxLjYsMTMzLjh2LTEyLjVoOC45djIuM2gtNi4xdjIuNWg2LjF2Mi4zaC02LjF2Mi44aDYuMXYyLjNoLTguOVYxMzMuOHoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTc0OC41LDEzMy44di04LjdsLTMuNiw4LjdoLTEuM2wtMy42LTguN3Y4LjdoLTIuNXYtMTIuNWgzLjhsMy4xLDcuNmwzLjEtNy42aDMuOHYxMi41SC03NDguNXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTc0Mi40LDEyNy41YzAtMy44LDIuOC02LjQsNi42LTYuNHM2LjYsMi44LDYuNiw2LjRjMCwzLjgtMi44LDYuNC02LjYsNi40Qy03MzkuOCwxMzQuMS03NDIuNCwxMzEuMy03NDIuNCwxMjcuNXogTS03MzIuMiwxMjcuNWMwLTIuMy0xLjUtNC4xLTMuOC00LjFjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFDLTczMy43LDEzMS42LTczMi4yLDEyOS44LTczMi4yLDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNzI2LjgsMTI3LjVjMC0zLjgsMi44LTYuNCw2LjYtNi40YzIuOCwwLDQuMywxLjUsNS4zLDMuMWwtMi4zLDFjLTAuNS0xLTEuNS0xLjgtMy4xLTEuOGMtMi4zLDAtMy44LDEuOC0zLjgsNC4xYzAsMi4zLDEuNSw0LjEsMy44LDQuMWMxLjMsMCwyLjMtMC44LDMuMS0xLjhsMi4zLDFjLTEsMS41LTIuNSwzLjEtNS4zLDMuMUMtNzIzLjgsMTM0LjEtNzI2LjgsMTMxLjMtNzI2LjgsMTI3LjV6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS03MDQuNywxMzMuOGwtMi41LTQuM2gtMnY0LjNoLTIuNXYtMTIuNWg1LjljMi41LDAsNC4xLDEuOCw0LjEsNC4xYzAsMi4zLTEuMywzLjMtMi44LDMuOGwyLjgsNC44aC0yLjhWMTMzLjh6IE0tNzA0LjUsMTI1LjJjMC0xLTAuOC0xLjgtMS44LTEuOGgtMi44djMuM2gyLjhDLTcwNS41LDEyNy03MDQuNSwxMjYuNS03MDQuNSwxMjUuMnoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTY4OS43LDEzMy44bC0wLjgtMmgtNS4zbC0wLjgsMmgtMy4xbDQuOC0xMi41aDMuM2w0LjgsMTIuNUgtNjg5Ljd6IE0tNjkzLjMsMTIzLjlsLTIsNS4zaDMuOEwtNjkzLjMsMTIzLjl6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS02ODIuNiwxMzMuOHYtMTAuMmgtMy42di0yLjNoOS45djIuM2gtMy42djEwLjJILTY4Mi42eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjczLjIsMTMzLjh2LTEyLjVoMi41djEyLjVILTY3My4yeiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjY3LDEzMy44di0ybDUuOS03LjloLTUuOXYtMi4zaDkuNHYybC01LjksOC4xaDYuMXYyLjNoLTkuN1YxMzMuOHoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTY1NC4xLDEzMy44di0xMi41aDIuNXYxMi41SC02NTQuMXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTYzOS4xLDEzMy44bC01LjktOC4xdjguMWgtMi41di0xMi41aDIuOGw1LjksNy45di03LjloMi41djEyLjVILTYzOS4xeiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjMzLjIsMTI3LjVjMC00LjEsMy4xLTYuNCw2LjYtNi40YzIuNSwwLDQuMywxLjMsNS4xLDIuOGwtMi4zLDEuM2MtMC41LTAuOC0xLjUtMS41LTMuMS0xLjVjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFjMSwwLDItMC41LDIuNS0xdi0xLjVoLTMuM1YxMjdoNS45djQuOGMtMS4zLDEuNS0zLjEsMi4zLTUuMywyLjNDLTYzMC4yLDEzNC4xLTYzMy4yLDEzMS42LTYzMy4yLDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNjEyLjEsMTI3LjVjMC00LjEsMy4xLTYuNCw2LjYtNi40YzIuNSwwLDQuMywxLjMsNS4xLDIuOGwtMi4zLDEuM2MtMC41LTAuOC0xLjUtMS41LTMuMS0xLjVjLTIuMywwLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFjMSwwLDItMC41LDIuNS0xdi0xLjVoLTMuM1YxMjdoNS45djQuOGMtMS4zLDEuNS0zLjEsMi4zLTUuMywyLjNDLTYwOSwxMzQuMS02MTIuMSwxMzEuNi02MTIuMSwxMjcuNXoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU5Ni42LDEzMy44di0xMi41aDguOXYyLjNoLTYuMXYyLjVoNi4xdjIuM2gtNi4xdjIuOGg2LjF2Mi4zaC04LjlWMTMzLjh6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01NzUuNywxMzMuOGwtNS45LTguMXY4LjFoLTIuNXYtMTIuNWgyLjhsNS45LDcuOXYtNy45aDIuNXYxMi41SC01NzUuN3oiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU2OS4xLDEzMy44di0xMi41aDguOXYyLjNoLTYuMXYyLjVoNi4xdjIuM2gtNi4xdjIuOGg2LjF2Mi4zaC04LjlWMTMzLjh6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01NDkuNywxMzMuOGwtMi41LTQuM2gtMnY0LjNoLTIuNXYtMTIuNWg1LjljMi41LDAsNC4xLDEuOCw0LjEsNC4xYzAsMi4zLTEuMywzLjMtMi44LDMuOGwyLjgsNC44aC0yLjhWMTMzLjh6IE0tNTQ5LjUsMTI1LjJjMC0xLTAuOC0xLjgtMS44LTEuOGgtMi44djMuM2gyLjhDLTU1MC4zLDEyNy01NDkuNSwxMjYuNS01NDkuNSwxMjUuMnoiLz48cGF0aCBmaWxsPSIjNTQ2RTdBIiBkPSJNLTU0My45LDEyNy41YzAtMy44LDIuOC02LjQsNi42LTYuNHM2LjYsMi44LDYuNiw2LjRjMCwzLjgtMi44LDYuNC02LjYsNi40Qy01NDEuMywxMzQuMS01NDMuOSwxMzEuMy01NDMuOSwxMjcuNXogTS01MzMuNywxMjcuNWMwLTIuMy0xLjUtNC4xLTMuOC00LjFzLTMuOCwxLjgtMy44LDQuMWMwLDIuMywxLjUsNC4xLDMuOCw0LjFDLTUzNS4yLDEzMS42LTUzMy43LDEyOS44LTUzMy43LDEyNy41eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNTI4LjYsMTMyLjFsMS41LTJjMC44LDEsMi4zLDEuOCw0LjEsMS44YzEuNSwwLDIuMy0wLjgsMi4zLTEuM2MwLTIuMy03LjEtMC44LTcuMS01LjNjMC0yLDEuOC0zLjgsNC44LTMuOGMyLDAsMy42LDAuNSw0LjgsMS44bC0xLjUsMmMtMS0xLTIuMy0xLjMtMy42LTEuM2MtMSwwLTEuOCwwLjUtMS44LDEuM2MwLDIsNy4xLDAuOCw3LjEsNS4zYzAsMi4zLTEuNSw0LjEtNS4xLDQuMUMtNTI1LjYsMTM0LjEtNTI3LjQsMTMzLjEtNTI4LjYsMTMyLjF6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01MTUuMSwxMzMuOHYtMTIuNWgyLjV2MTIuNUgtNTE1LjF6Ii8+PHBhdGggZmlsbD0iIzU0NkU3QSIgZD0iTS01MDUuNywxMzMuOHYtMTAuMmgtMy42di0yLjNoOS45djIuM2gtMy42djEwLjJILTUwNS43eiIvPjxwYXRoIGZpbGw9IiM1NDZFN0EiIGQ9Ik0tNDkyLjcsMTMzLjh2LTUuMWwtNC44LTcuNGgzLjFsMy4xLDUuMWwzLjEtNS4xaDMuMWwtNC44LDcuNHY1LjFILTQ5Mi43eiIvPjwvZz48Zz48Zz48cGF0aCBmaWxsPSIjNjZCQjZBIiBkPSJNLTQ4NS45LDQ0LjNoLTEuM2wwLjMsMS4zYzIsOS45LDAuMywyNC43LTcuNCwzMy44Yy00LjMsNS4zLTkuOSw4LjEtMTYuOCw4LjFjLTEwLjksMC0xNS0xMy0xNS41LTI3LjdjMTcuOC00LjMsMjkuOC0xNS41LDI5LjgtMjguNWMwLTkuNC0yLjgtMjQuOS0yMS40LTI0LjljLTE3LjYsMC0yNi41LDI2LjItMjguMiw0NC41Yy04LjktMC4zLTE1LjUtNC4zLTE5LjYtOC4xYzEuNS02LjQsMi4zLTEyLjIsMi4zLTE3LjZjMC03LjQtNS4xLTEwLjctOS45LTEwLjdjLTYuOSwwLTE0LDYuNi0xNCwxOS4zYzAsNy42LDIuOCwxNCw4LjcsMTguNmMtNS4xLDEyLTEzLjcsMjIuMS0xNi41LDI1LjRjLTIuMy00LjgtOS43LTIyLjQtMTItNDEuNWMyLjgtNy42LDQuMy0xNCw0LjMtMTdjMC00LjgtMy4xLTcuNi04LjEtNy42Yy02LjksMC0xNy44LDQuMy0xOC4xLDQuNmwtMC41LDAuM3YwLjhjMCwwLjMsMy4zLDE1LjUsNi42LDMyLjNjLTYuNCwxMC40LTE3LjYsMjcuNy0yMy4yLDI3LjdjLTEwLjIsMCw2LjYtNTIuMi0wLjgtNTMuOWMtMC4zLDAtMC41LDAtMC44LDAuM2MtMy42LDIuMy00My41LDI0LjQtOTYuNywyNC40YzAsMCwwLDEsMC41LDJjMC4zLDAuOCwxLDEuNSwxLDEuNWMxNSwxLjgsMzYuNC0wLjMsNTIuNy0yLjVjLTkuNCwyMC4xLTI2LDMzLjMtNDEuMiwzMy4zYy0yOC44LDAtNTAuOS0zNC45LTUwLjktMzQuOWM4LjktNy45LDIzLjQtMzMuMyw0NC44LTMzLjNjMjEuMSwwLDMwLjMsMTEuNywzMC4zLDExLjdsMi4zLTMuOGMwLDAtOS45LTM0LjYtMzcuOS0zNC42cy01Ny44LDQ1LjgtNzUuMSw1Ni41YzAsMCwyMy45LDU2LjUsNzYuMSw1Ni41YzQzLjgsMCw1NS00Miw1Ny01Mi4yYzEwLjctMS41LDE4LjEtMy4xLDE4LjEtMy4xcy0yLjgsMjEuNC0yLjgsMzAuM3M5LjksMTguMywxOC4xLDE4LjNjNi45LDAsMjAuOS0xNC4yLDMxLTMxLjZsMC41LDJjNS4zLDE5LjYsMTIsMjkuOCwxOS44LDI5LjhjNy45LDAsMjAuOS0xNi4zLDI5LjMtMzYuOWM4LjQsMy42LDE4LjMsNC42LDI0LjIsNC44YzIuMywzNS40LDMxLjgsMzYuNCwzNS40LDM2LjRjMjEuOSwwLDQwLjUtMTUuOCw0MC41LTM0LjRDLTQ3MC42LDQ0LjUtNDg1LjYsNDQuMy00ODUuOSw0NC4zeiBNLTUxMi42LDI5LjVjMCwwLTAuMywxMS43LTEzLjUsMTcuNmMxLjMtMTUuNSw1LjEtMjkuNSw3LjYtMjkuNUMtNTE1LjYsMTcuOC01MTIuNiwyMi4xLTUxMi42LDI5LjV6Ii8+PHBhdGggZmlsbD0iIzY2QkI2QSIgZD0iTS02NjUsMTUuNWMwLDAuNSwwLjMsMC44LDAuOCwxYzEwLjQsMS41LDE3LjMtMS44LDE3LjMtMTguNmMwLTE1LjgtMTYuMy0zLjMtMTkuMy0xYy0wLjMsMC4zLTAuMywwLjUtMC4zLDFDLTY2My43LDQuMS02NjQuOCwxMy02NjUsMTUuNXoiLz48L2c+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjg5LjU4NjQiIHkxPSIzNzMuMjM3OSIgeDI9Ii0yODIuODg0MiIgeTI9IjM3NS40NzE5IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xXykiIGQ9Ik0tNjIzLDQ5LjRjLTQuMSw2LjktMTAuMiwxNi4zLTE1LjUsMjIuMWMxLjMsMy4xLDIuOCw2LjksNC4zLDkuOWM0LjgtNS4zLDkuNy0xMi4yLDE0LTE5LjNMLTYyMyw0OS40eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMl8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI2OS4wNTc3IiB5MT0iMzcxLjU0NDEiIHgyPSItMjY1LjE3MDUiIHkyPSIzNzguMzgwMiIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMl8pIiBkPSJNLTU3NC43LDU0LjdjLTItMS0zLjgtMi41LTMuOC0yLjVjLTMuNiw3LjktOC40LDE1LjMtMTIuMiwyMC4xYzEuOCwyLjUsNC44LDUuOSw3LjEsOC40YzQuNi02LjQsOS40LTE0LjgsMTMtMjMuN0MtNTcwLjQsNTYuNy01NzIuNiw1Ni01NzQuNyw1NC43eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfM18iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OC42NDE2IiB5MT0iMzY4LjM4MzUiIHgyPSItMjQ5LjQ0NTkiIHkyPSIzNzUuNTMyMyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfM18pIiBkPSJNLTUyNi4zLDU5LjhjMCwwLTUuMSwxLTEwLjIsMS41cy05LjksMC4zLTkuOSwwLjNjMC44LDEwLjIsMy42LDE3LjMsNy40LDIyLjZsMTguNi0xLjVDLTUyNC4zLDc3LjYtNTI2LjEsNjktNTI2LjMsNTkuOHoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzRfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNDkuOCIgeTE9IjM4My41ODEiIHgyPSItMjQ5LjgiIHkyPSIzNzYuMzc2MyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfNF8pIiBkPSJNLTU0MS4xLDI4LjhMLTU0MS4xLDI4LjhjLTAuNSwxLjUtMS4zLDMuMy0xLjgsNS4xYzAsMC41LTAuMywwLjgtMC4zLDEuM2MtMSwzLjMtMS44LDYuNi0yLjMsOS45YzAsMC41LTAuMywwLjgtMC4zLDEuM2MtMC4zLDEuNS0wLjUsMy4xLTAuNSw0LjZjMTIsMCwyMC4xLTMuNiwyMC4xLTMuNmMwLTEuMywwLjMtMi4zLDAuMy0zLjZjMC0wLjMsMC0wLjUsMC0wLjhjMC0xLDAuMy0xLjgsMC4zLTIuOGMwLTAuMywwLTAuNSwwLTAuOGMwLjMtMi4zLDAuOC00LjYsMS02LjZMLTU0MS4xLDI4Ljh6IE0tNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNkwtNTQ2LjQsNTAuNnoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzVfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zMTMiIHkxPSIzNzEuNzcyMiIgeDI9Ii0zMTMiIHkyPSIzODAuNzA4MyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfNV8pIiBkPSJNLTcwOC4zLDcyLjhsMTEuMiw0LjhjNS4zLTcuNiw4LjctMTUuNSwxMC43LTIxLjZsMi04LjFsLTUuMywwLjhDLTY5NC41LDU4LjgtNzAxLjEsNjYuOS03MDguMyw3Mi44eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfNl8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI3Ny41MjU1IiB5MT0iMzkwLjIxMyIgeDI9Ii0yNzguNjQ3OSIgeTI9IjM4OC40MjU0IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF82XykiIGQ9Ik0tNjA3LDM2LjFjMi44LTcuNiw0LjMtMTQsNC4zLTE3YzAtMC4zLDAtMC41LDAtMC44bC02LjYsMkMtNjA5LjMsMjAuNC02MDksMjQuNy02MDcsMzYuMXoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzdfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNjIuNTgxMyIgeTE9IjM4Ni40ODI3IiB4Mj0iLTI2My4yMTUiIHkyPSIzODQuMDc0OSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfN18pIiBkPSJNLTU3MS40LDMwLjVjMCwwLTEuOCw1LjMsNS42LDEyYzEuMy01LjMsMi0xMC43LDIuMy0xNS4zTC01NzEuNCwzMC41eiIvPjwvZz48L2c+PGc+PGc+PHBhdGggZmlsbD0iIzY2QkI2QSIgZD0iTS04MDcuOCwzNDYuNmgtMC41djAuNWMwLjgsNC4zLDAsMTAuNC0zLjEsMTQuNWMtMS44LDIuMy00LjMsMy42LTcuMSwzLjZjLTQuNiwwLTYuNC01LjYtNi42LTExLjdjNy42LTEuOCwxMi43LTYuNiwxMi43LTEyLjJjMC00LjEtMS4zLTEwLjctOS4yLTEwLjdjLTcuNCwwLTExLjIsMTEuMi0xMiwxOC44Yy0zLjgsMC02LjYtMS44LTguNC0zLjZjMC44LTIuOCwxLTUuMSwxLTcuNGMwLTMuMS0yLjMtNC42LTQuMS00LjZjLTMuMSwwLTUuOSwyLjgtNS45LDguMWMwLDMuMywxLjMsNS45LDMuOCw3LjljLTIuMyw1LjEtNS45LDkuNC03LjEsMTAuN2MtMS0yLTQuMS05LjctNS4xLTE3LjZjMS4zLTMuMywxLjgtNS45LDEuOC03LjFjMC0yLTEuMy0zLjMtMy42LTMuM2MtMy4xLDAtNy42LDEuOC03LjYsMmwtMC4zLDAuM3YwLjNjMCwwLDEuMyw2LjYsMi44LDEzLjdjLTIuOCw0LjMtNy40LDExLjctOS45LDExLjdjLTQuMywwLDIuOC0yMi4xLTAuMy0yMi45aC0wLjNjLTEuNSwxLTE4LjYsMTAuNC00MSwxMC40YzAsMCwwLDAuNSwwLjMsMC44YzAuMywwLjMsMC41LDAuNSwwLjUsMC41YzYuNCwwLjgsMTUuNSwwLDIyLjQtMWMtNC4xLDguNC0xMC45LDE0LjItMTcuNiwxNC4yYy0xMi4yLDAtMjEuNi0xNC44LTIxLjYtMTQuOGMzLjgtMy4zLDkuOS0xNC4yLDE5LjEtMTQuMmM4LjksMCwxMyw0LjgsMTMsNC44bDEtMS41YzAsMC00LjMtMTQuOC0xNi0xNC44cy0yNC40LDE5LjYtMzEuOCwyMy45YzAsMCwxMC4yLDI0LjIsMzIuMywyNC4yYzE4LjYsMCwyMy40LTE3LjgsMjQuMi0yMi4xYzQuNi0wLjgsNy42LTEuMyw3LjYtMS4zcy0xLDkuMi0xLDEzYzAsMy44LDQuMSw3LjksNy42LDcuOWMzLjEsMCw4LjktNi4xLDEzLjItMTMuNWwwLjMsMC44YzIuMyw4LjQsNS4xLDEyLjcsOC40LDEyLjdzOC45LTYuOSwxMi41LTE1LjVjMy42LDEuNSw3LjksMiwxMC4yLDJjMSwxNSwxMy41LDE1LjUsMTUsMTUuNWM5LjQsMCwxNy4zLTYuNiwxNy4zLTE0LjVDLTgwMS40LDM0Ni44LTgwNy44LDM0Ni42LTgwNy44LDM0Ni42eiBNLTgxOSwzNDAuMmMwLDAsMCw1LjEtNS45LDcuNmMwLjUtNi42LDItMTIuNSwzLjMtMTIuNUMtODIwLjUsMzM1LjQtODE5LDMzNy4yLTgxOSwzNDAuMnoiLz48cGF0aCBmaWxsPSIjNjZCQjZBIiBkPSJNLTg4My44LDMzNC40YzAsMC4zLDAsMC4zLDAuMywwLjVjNC4zLDAuNSw3LjQtMC44LDcuNC03LjljMC02LjYtNi45LTEuNS04LjEtMC41YzAsMC0wLjMsMC4zLDAsMC41Qy04ODMuMywzMjkuNS04ODMuOCwzMzMuMS04ODMuOCwzMzQuNHoiLz48L2c+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF84XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzgyLjAwNzQiIHkxPSIyNTkuODQ3NSIgeDI9Ii0zNzkuMTU4NSIgeTI9IjI2MC43OTcyIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF84XykiIGQ9Ik0tODY2LDM0OC42Yy0xLjgsMi44LTQuMyw2LjktNi42LDkuNGMwLjUsMS4zLDEuMywyLjgsMS44LDQuM2MyLTIuMyw0LjEtNS4xLDYuMS04LjFMLTg2NiwzNDguNnoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzlfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNzMuMTYyNiIgeTE9IjI1OS4wNDIzIiB4Mj0iLTM3MS41MTAyIiB5Mj0iMjYxLjk0ODIiIGdyYWRpZW50VHJhbnNmb3JtPSJtYXRyaXgoMi41NDQ1IDAgMCAtMi41NDQ1IDEwMC4xMjcyIDEwMTcuODExNykiPjxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz48c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+PC9saW5lYXJHcmFkaWVudD48cGF0aCBmaWxsPSJ1cmwoI1NWR0lEXzlfKSIgZD0iTS04NDUuNCwzNTAuOWMtMC44LTAuNS0xLjUtMS0xLjUtMWMtMS41LDMuMy0zLjYsNi40LTUuMSw4LjdjMC44LDEsMiwyLjUsMy4xLDMuNmMyLTIuOCw0LjEtNi4xLDUuNi0xMC4yQy04NDMuNiwzNTEuOS04NDQuNywzNTEuNC04NDUuNCwzNTAuOXoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEwXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzY0LjY5OTYiIHkxPSIyNTcuNzUwMyIgeDI9Ii0zNjUuMDQxNCIgeTI9IjI2MC43ODkyIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xMF8pIiBkPSJNLTgyNS4xLDM1My4yYzAsMC0yLDAuNS00LjMsMC44Yy0yLDAuMy00LjMsMC00LjMsMGMwLjMsNC4zLDEuNSw3LjQsMy4xLDkuN2w3LjktMC44Qy04MjQsMzYwLjgtODI0LjgsMzU3LTgyNS4xLDM1My4yeiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTFfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNjUiIHkxPSIyNjQuMjIyMyIgeDI9Ii0zNjUiIHkyPSIyNjEuMTU5NyIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMTFfKSIgZD0iTS04MzEuMiwzMzkuOUwtODMxLjIsMzM5LjljLTAuMywwLjgtMC41LDEuNS0wLjgsMmMwLDAuMywwLDAuMy0wLjMsMC41Yy0wLjUsMS41LTAuOCwyLjgtMSw0LjNjMCwwLjMsMCwwLjMsMCwwLjVjMCwwLjgtMC4zLDEuMy0wLjMsMmM1LjEsMCw4LjctMS41LDguNy0xLjVjMC0wLjUsMC0xLDAuMy0xLjV2LTAuM2MwLTAuNSwwLTAuOCwwLjMtMXYtMC4zYzAuMy0xLDAuMy0yLDAuNS0yLjhMLTgzMS4yLDMzOS45eiBNLTgzMy41LDM0OS40TC04MzMuNSwzNDkuNEwtODMzLjUsMzQ5LjRMLTgzMy41LDM0OS40eiIvPjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTJfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zOTIiIHkxPSIyNTkuMjAyNSIgeDI9Ii0zOTIiIHkyPSIyNjMuMDAxMSIgZ3JhZGllbnRUcmFuc2Zvcm09Im1hdHJpeCgyLjU0NDUgMCAwIC0yLjU0NDUgMTAwLjEyNzIgMTAxNy44MTE3KSI+PHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPjxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz48L2xpbmVhckdyYWRpZW50PjxwYXRoIGZpbGw9InVybCgjU1ZHSURfMTJfKSIgZD0iTS05MDIuNCwzNTguOGw0LjgsMmMyLjMtMy4zLDMuNi02LjYsNC42LTkuMmwwLjgtMy42bC0yLjMsMC4zQy04OTYuNiwzNTIuNy04OTkuMSwzNTYuMi05MDIuNCwzNTguOHoiLz48bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEzXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzc2Ljg2MzciIHkxPSIyNjcuMDM1NSIgeDI9Ii0zNzcuMzQwOSIgeTI9IjI2Ni4yNzU1IiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xM18pIiBkPSJNLTg1OS4yLDM0M2MxLjMtMy4zLDEuOC01LjksMS44LTcuMXYtMC4zbC0yLjgsMC44Qy04NjAuMiwzMzYuNC04NjAuMiwzMzguMi04NTkuMiwzNDN6Ii8+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xNF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM3MC41NTQzIiB5MT0iMjY1LjQ2NDUiIHgyPSItMzcwLjgyMzYiIHkyPSIyNjQuNDQxIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xNF8pIiBkPSJNLTg0NC4xLDM0MC43YzAsMC0wLjgsMi4zLDIuMyw1LjFjMC41LTIuMywxLTQuNiwxLTYuNkwtODQ0LjEsMzQwLjd6Ii8+PC9nPjxnPjxyZWN0IHg9Ii02OTcuMyIgeT0iMjkyLjkiIGZpbGw9IiNGRkZGRkYiIHdpZHRoPSIxMDYuOSIgaGVpZ2h0PSIxMDYuOSIvPjxnPjxwYXRoIGZpbGw9IiM2NkJCNkEiIGQ9Ik0tNjQ0LjQsMzQ5LjljMC4zLDAuNSwwLjUsMC44LDAuNSwwLjhjOC43LDEsMjEuMSwwLDMwLjUtMS41Yy01LjMsMTEuNy0xNSwxOS4zLTIzLjksMTkuM2MtMTYuNSwwLTI5LjUtMjAuMS0yOS41LTIwLjFjNS4xLTQuNiwxMy43LTE5LjMsMjYtMTkuM2MxMi4yLDAsMTcuNiw2LjYsMTcuNiw2LjZsMS4zLTIuM2MwLDAtNS45LTIwLjEtMjEuOS0yMC4xYy0xNi4zLDAtMzMuMywyNi41LTQzLjUsMzIuNmMwLDAsMTMuNywzMi44LDQ0LDMyLjhjMjUuNCwwLDMxLjgtMjQuMiwzMy4xLTMwLjNjMy4zLTAuNSw2LjEtMSw4LjEtMS4zYzAuNS0xLjMsMS4zLTMuOCwwLjgtNy4xYy0xMC4yLDMuOC0yNS40LDguNC00My41LDguNEMtNjQ0LjcsMzQ4LjYtNjQ0LjcsMzQ5LjEtNjQ0LjQsMzQ5Ljl6Ii8+PGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xNV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI4MS44NSIgeTE9IjI1Ny41MTg3IiB4Mj0iLTI4MS44NSIgeTI9IjI2Mi42NTExIiBncmFkaWVudFRyYW5zZm9ybT0ibWF0cml4KDIuNTQ0NSAwIDAgLTIuNTQ0NSAxMDAuMTI3MiAxMDE3LjgxMTcpIj48c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+PHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPjwvbGluZWFyR3JhZGllbnQ+PHBhdGggZmlsbD0idXJsKCNTVkdJRF8xNV8pIiBkPSJNLTYxMC4xLDM0OC45bC0zLjEsMC4zYzAsMC4zLTAuMywwLjUtMC41LDAuOGMtMC41LDEtMSwxLjgtMS41LDIuOGMtMC4zLDAuNS0wLjUsMC44LTAuOCwxLjNjLTAuNSwxLTEuMywyLTIsMi44Yy0wLjMsMC4zLTAuMywwLjMtMC41LDAuNWMtMS44LDIuMy0zLjYsNC4zLTUuNiw1LjlsNi40LDIuOEMtNjEyLjYsMzU5LjMtNjEwLjYsMzUxLjctNjEwLjEsMzQ4Ljl6Ii8+PC9nPjwvZz48Zz48Zz48ZGVmcz48Y2lyY2xlIGlkPSJTVkdJRF8xNl8iIGN4PSItNDA3LjMiIGN5PSIzNDYuMyIgcj0iNDIuMiIvPjwvZGVmcz48Y2xpcFBhdGggaWQ9IlNWR0lEXzE3XyI+PHVzZSB4bGluazpocmVmPSIjU1ZHSURfMTZfIiAgb3ZlcmZsb3c9InZpc2libGUiLz48L2NsaXBQYXRoPjxwYXRoIGNsaXAtcGF0aD0idXJsKCNTVkdJRF8xN18pIiBmaWxsPSIjRkZGRkZGIiBkPSJNLTQwMS4xLDM0OS40YzAuMywwLjMsMC41LDAuOCwwLjUsMC44YzcuNCwxLDE4LjEsMCwyNi4yLTEuM2MtNC42LDkuOS0xMywxNi41LTIwLjQsMTYuNWMtMTQuMiwwLTI1LjItMTcuMy0yNS4yLTE3LjNjNC4zLTMuOCwxMS43LTE2LjUsMjIuMS0xNi41czE1LDUuOSwxNSw1LjlsMS4zLTEuOGMwLDAtNC44LTE3LTE4LjgtMTdzLTI4LjUsMjIuNi0zNy4yLDI4YzAsMCwxMiwyOCwzNy43LDI4YzIxLjYsMCwyNy4yLTIwLjksMjguMi0yNmMyLjgtMC41LDUuMy0wLjgsNi45LTFjMC41LTEuMywxLTMuMywwLjgtNi4xYy04LjcsMy4zLTIxLjYsNy4xLTM3LjIsNy4xQy00MDEuNCwzNDguMy00MDEuNCwzNDguOS00MDEuMSwzNDkuNHoiLz48L2c+PC9nPjwvZz48ZyBpZD0iTGF5ZXJfMiI+PHBhdGggZmlsbD0iIzg4ODg4OCIgZD0iTTQ2Ny4zLDIwOS45Yy00LjgsMjQuNC0zMC44LDEyMi42LTEzMy42LDEyMi42Yy0xMjIuNiwwLTE3OC42LTEzMi44LTE3OC42LTEzMi44YzQxLTI0LjksMTEwLjQtMTMyLjMsMTc2LjEtMTMyLjNzODguOCw4MS4yLDg4LjgsODEuMmwtNS42LDguOWMwLDAtMjEuNi0yNy4yLTcxLjItMjcuMnMtODMuNyw1OS44LTEwNC42LDc4LjRjMCwwLDUyLjIsODEuNywxMTkuMyw4MS43YzM2LjEsMCw3NS4xLTMxLjMsOTYuOS03OC40Yy0zOC4yLDUuMy04OC4zLDEwLjItMTIzLjcsNS42YzAsMC0xLjgtMS41LTIuNS0zLjNjLTEtMi4zLTEuMy00LjYtMS4zLTQuNmM3MC4yLDAsMTMwLjUtMTYuNSwxNzEuNS0zMS44QzQ4Ny43LDc3LjYsNDAyLjksMCwzMDAuMSwwYy0xMTAuNCwwLTIwMCw4OS42LTIwMCwyMDBzODkuNiwyMDAsMjAwLDIwMGMxMDguOSwwLDE5Ny41LTg3LDIwMC0xOTUuNEM0OTIuNSwyMDUuOSw0ODEsMjA3LjksNDY3LjMsMjA5Ljl6Ii8+PC9nPjwvc3ZnPg==',
		'give_cpt_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxOC4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiDQoJIHZpZXdCb3g9IjAgMCAxNTcuMSAxNTcuMiIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTU3LjEgMTU3LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiM2NkJCNkE7fQ0KCS5zdDF7ZmlsbDojNTQ2RTdBO30NCgkuc3Qye2ZpbGw6dXJsKCNTVkdJRF8xXyk7fQ0KCS5zdDN7ZmlsbDp1cmwoI1NWR0lEXzJfKTt9DQoJLnN0NHtmaWxsOnVybCgjU1ZHSURfM18pO30NCgkuc3Q1e2ZpbGw6dXJsKCNTVkdJRF80Xyk7fQ0KCS5zdDZ7ZmlsbDp1cmwoI1NWR0lEXzVfKTt9DQoJLnN0N3tmaWxsOnVybCgjU1ZHSURfNl8pO30NCgkuc3Q4e2ZpbGw6dXJsKCNTVkdJRF83Xyk7fQ0KCS5zdDl7ZmlsbDp1cmwoI1NWR0lEXzhfKTt9DQoJLnN0MTB7ZmlsbDp1cmwoI1NWR0lEXzlfKTt9DQoJLnN0MTF7ZmlsbDp1cmwoI1NWR0lEXzEwXyk7fQ0KCS5zdDEye2ZpbGw6dXJsKCNTVkdJRF8xMV8pO30NCgkuc3QxM3tmaWxsOnVybCgjU1ZHSURfMTJfKTt9DQoJLnN0MTR7ZmlsbDp1cmwoI1NWR0lEXzEzXyk7fQ0KCS5zdDE1e2ZpbGw6dXJsKCNTVkdJRF8xNF8pO30NCgkuc3QxNntmaWxsOiNGRkZGRkY7fQ0KCS5zdDE3e2ZpbGw6dXJsKCNTVkdJRF8xNV8pO30NCgkuc3QxOHtjbGlwLXBhdGg6dXJsKCNTVkdJRF8xN18pO2ZpbGw6I0ZGRkZGRjt9DQoJLnN0MTl7ZmlsbDojRjFGMkYyO30NCjwvc3R5bGU+DQo8ZyBpZD0iTGF5ZXJfMSI+DQoJPGNpcmNsZSBjbGFzcz0ic3QwIiBjeD0iLTE5OS40IiBjeT0iMTM2LjEiIHI9IjE2LjYiLz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTM0OC40LDUyLjZ2LTQuOWgxLjljMS41LDAsMi42LDEsMi42LDIuNWMwLDEuNS0xLjEsMi41LTIuNiwyLjVILTM0OC40eiBNLTM0NC45LDUwLjENCgkJCQljMC0wLjktMC41LTEuNS0xLjUtMS41aC0wLjl2My4xaDAuOUMtMzQ1LjUsNTEuNy0zNDQuOSw1MS0zNDQuOSw1MC4xeiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0zNDIuNiw1Mi42di00LjloMy41djAuOWgtMi40djFoMi40djAuOWgtMi40djEuMWgyLjR2MC45SC0zNDIuNnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzMzLjUsNTIuNnYtMy40bC0xLjQsMy40aC0wLjVsLTEuNC0zLjR2My40aC0xdi00LjloMS41bDEuMiwzbDEuMi0zaDEuNXY0LjlILTMzMy41eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0zMzEuMSw1MC4xYzAtMS41LDEuMS0yLjUsMi42LTIuNWMxLjUsMCwyLjYsMS4xLDIuNiwyLjVjMCwxLjUtMS4xLDIuNS0yLjYsMi41DQoJCQkJQy0zMzAuMSw1Mi43LTMzMS4xLDUxLjYtMzMxLjEsNTAuMXogTS0zMjcuMSw1MC4xYzAtMC45LTAuNi0xLjYtMS41LTEuNnMtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNg0KCQkJCVMtMzI3LjEsNTEtMzI3LjEsNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzI1LDUwLjFjMC0xLjUsMS4xLTIuNSwyLjYtMi41YzEuMSwwLDEuNywwLjYsMi4xLDEuMmwtMC45LDAuNGMtMC4yLTAuNC0wLjYtMC43LTEuMi0wLjcNCgkJCQljLTAuOSwwLTEuNSwwLjctMS41LDEuNmMwLDAuOSwwLjYsMS42LDEuNSwxLjZjMC41LDAsMC45LTAuMywxLjItMC43bDAuOSwwLjRjLTAuNCwwLjYtMSwxLjItMi4xLDEuMg0KCQkJCUMtMzIzLjgsNTIuNy0zMjUsNTEuNi0zMjUsNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzE2LjMsNTIuNmwtMS0xLjdoLTAuOHYxLjdoLTF2LTQuOWgyLjNjMSwwLDEuNiwwLjcsMS42LDEuNmMwLDAuOS0wLjUsMS4zLTEuMSwxLjVsMS4xLDEuOUgtMzE2LjN6DQoJCQkJIE0tMzE2LjIsNDkuMmMwLTAuNC0wLjMtMC43LTAuNy0wLjdoLTEuMXYxLjNoMS4xQy0zMTYuNiw0OS45LTMxNi4yLDQ5LjctMzE2LjIsNDkuMnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzEwLjQsNTIuNmwtMC4zLTAuOGgtMi4xbC0wLjMsMC44aC0xLjJsMS45LTQuOWgxLjNsMS45LDQuOUgtMzEwLjR6IE0tMzExLjgsNDguN2wtMC44LDIuMWgxLjUNCgkJCQlMLTMxMS44LDQ4Ljd6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTMwNy42LDUyLjZ2LTRoLTEuNHYtMC45aDMuOXYwLjloLTEuNHY0SC0zMDcuNnoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzAzLjksNTIuNnYtNC45aDF2NC45SC0zMDMuOXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMzAxLjUsNTIuNnYtMC44bDIuMy0zLjFoLTIuM3YtMC45aDMuN3YwLjhsLTIuMywzLjJoMi40djAuOUgtMzAxLjV6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI5Ni40LDUyLjZ2LTQuOWgxdjQuOUgtMjk2LjR6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI5MC41LDUyLjZsLTIuMy0zLjJ2My4yaC0xdi00LjloMS4xbDIuMywzLjF2LTMuMWgxdjQuOUgtMjkwLjV6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI4OC4yLDUwLjFjMC0xLjYsMS4yLTIuNSwyLjYtMi41YzEsMCwxLjcsMC41LDIsMS4xbC0wLjksMC41Yy0wLjItMC4zLTAuNi0wLjYtMS4yLTAuNg0KCQkJCWMtMC45LDAtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNmMwLjQsMCwwLjgtMC4yLDEtMC40di0wLjZoLTEuM3YtMC45aDIuM3YxLjljLTAuNSwwLjYtMS4yLDAuOS0yLjEsMC45DQoJCQkJQy0yODcsNTIuNy0yODguMiw1MS43LTI4OC4yLDUwLjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI3OS45LDUwLjFjMC0xLjYsMS4yLTIuNSwyLjYtMi41YzEsMCwxLjcsMC41LDIsMS4xbC0wLjksMC41Yy0wLjItMC4zLTAuNi0wLjYtMS4yLTAuNg0KCQkJCWMtMC45LDAtMS41LDAuNy0xLjUsMS42YzAsMC45LDAuNiwxLjYsMS41LDEuNmMwLjQsMCwwLjgtMC4yLDEtMC40di0wLjZoLTEuM3YtMC45aDIuM3YxLjljLTAuNSwwLjYtMS4yLDAuOS0yLjEsMC45DQoJCQkJQy0yNzguNyw1Mi43LTI3OS45LDUxLjctMjc5LjksNTAuMXoiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik0tMjczLjgsNTIuNnYtNC45aDMuNXYwLjloLTIuNHYxaDIuNHYwLjloLTIuNHYxLjFoMi40djAuOUgtMjczLjh6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI2NS42LDUyLjZsLTIuMy0zLjJ2My4yaC0xdi00LjloMS4xbDIuMywzLjF2LTMuMWgxdjQuOUgtMjY1LjZ6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI2Myw1Mi42di00LjloMy41djAuOWgtMi40djFoMi40djAuOWgtMi40djEuMWgyLjR2MC45SC0yNjN6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI1NS40LDUyLjZsLTEtMS43aC0wLjh2MS43aC0xdi00LjloMi4zYzEsMCwxLjYsMC43LDEuNiwxLjZjMCwwLjktMC41LDEuMy0xLjEsMS41bDEuMSwxLjlILTI1NS40eg0KCQkJCSBNLTI1NS4zLDQ5LjJjMC0wLjQtMC4zLTAuNy0wLjctMC43aC0xLjF2MS4zaDEuMUMtMjU1LjYsNDkuOS0yNTUuMyw0OS43LTI1NS4zLDQ5LjJ6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI1My4xLDUwLjFjMC0xLjUsMS4xLTIuNSwyLjYtMi41YzEuNSwwLDIuNiwxLjEsMi42LDIuNWMwLDEuNS0xLjEsMi41LTIuNiwyLjUNCgkJCQlDLTI1Mi4xLDUyLjctMjUzLjEsNTEuNi0yNTMuMSw1MC4xeiBNLTI0OS4xLDUwLjFjMC0wLjktMC42LTEuNi0xLjUtMS42Yy0wLjksMC0xLjUsMC43LTEuNSwxLjZjMCwwLjksMC42LDEuNiwxLjUsMS42DQoJCQkJQy0yNDkuNyw1MS43LTI0OS4xLDUxLTI0OS4xLDUwLjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTI0Ny4xLDUxLjlsMC42LTAuOGMwLjMsMC40LDAuOSwwLjcsMS42LDAuN2MwLjYsMCwwLjktMC4zLDAuOS0wLjVjMC0wLjktMi44LTAuMy0yLjgtMi4xDQoJCQkJYzAtMC44LDAuNy0xLjUsMS45LTEuNWMwLjgsMCwxLjQsMC4yLDEuOSwwLjdsLTAuNiwwLjhjLTAuNC0wLjQtMC45LTAuNS0xLjQtMC41Yy0wLjQsMC0wLjcsMC4yLTAuNywwLjVjMCwwLjgsMi44LDAuMywyLjgsMi4xDQoJCQkJYzAsMC45LTAuNiwxLjYtMiwxLjZDLTI0NS45LDUyLjctMjQ2LjYsNTIuMy0yNDcuMSw1MS45eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0yNDEuOCw1Mi42di00LjloMXY0LjlILTI0MS44eiIvPg0KCQkJPHBhdGggY2xhc3M9InN0MSIgZD0iTS0yMzguMSw1Mi42di00aC0xLjR2LTAuOWgzLjl2MC45aC0xLjR2NEgtMjM4LjF6Ii8+DQoJCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNLTIzMyw1Mi42di0ybC0xLjktMi45aDEuMmwxLjIsMmwxLjItMmgxLjJsLTEuOSwyLjl2MkgtMjMzeiIvPg0KCQk8L2c+DQoJCTxnPg0KCQkJPGc+DQoJCQkJPHBhdGggY2xhc3M9InN0MCIgZD0iTS0yMzAuMywxNy40bC0wLjUsMGwwLjEsMC41YzAuOCwzLjksMC4xLDkuNy0yLjksMTMuM2MtMS43LDIuMS0zLjksMy4yLTYuNiwzLjJjLTQuMywwLTUuOS01LjEtNi4xLTEwLjkNCgkJCQkJYzctMS43LDExLjctNi4xLDExLjctMTEuMmMwLTMuNy0xLjEtOS44LTguNC05LjhjLTYuOSwwLTEwLjQsMTAuMy0xMS4xLDE3LjVjLTMuNS0wLjEtNi4xLTEuNy03LjctMy4yYzAuNi0yLjUsMC45LTQuOCwwLjktNi45DQoJCQkJCWMwLTIuOS0yLTQuMi0zLjktNC4yYy0yLjcsMC01LjUsMi42LTUuNSw3LjZjMCwzLDEuMSw1LjUsMy40LDcuM2MtMiw0LjctNS40LDguNy02LjUsMTBjLTAuOS0xLjktMy44LTguOC00LjctMTYuMw0KCQkJCQljMS4xLTMsMS43LTUuNSwxLjctNi43YzAtMS45LTEuMi0zLTMuMi0zYy0yLjcsMC03LDEuNy03LjEsMS44bC0wLjIsMC4xbDAsMC4zYzAsMC4xLDEuMyw2LjEsMi42LDEyLjcNCgkJCQkJYy0yLjUsNC4xLTYuOSwxMC45LTkuMSwxMC45Yy00LDAsMi42LTIwLjUtMC4zLTIxLjJjLTAuMSwwLTAuMiwwLTAuMywwLjFjLTEuNCwwLjktMTcuMSw5LjYtMzgsOS42YzAsMCwwLDAuNCwwLjIsMC44DQoJCQkJCWMwLjEsMC4zLDAuNCwwLjYsMC40LDAuNmM1LjksMC43LDE0LjMtMC4xLDIwLjctMWMtMy43LDcuOS0xMC4yLDEzLjEtMTYuMiwxMy4xYy0xMS4zLDAtMjAtMTMuNy0yMC0xMy43DQoJCQkJCWMzLjUtMy4xLDkuMi0xMy4xLDE3LjYtMTMuMWM4LjMsMCwxMS45LDQuNiwxMS45LDQuNmwwLjktMS41YzAsMC0zLjktMTMuNi0xNC45LTEzLjZjLTExLDAtMjIuNywxOC0yOS41LDIyLjINCgkJCQkJYzAsMCw5LjQsMjIuMiwyOS45LDIyLjJjMTcuMiwwLDIxLjYtMTYuNSwyMi40LTIwLjVjNC4yLTAuNiw3LjEtMS4yLDcuMS0xLjJzLTEuMSw4LjQtMS4xLDExLjljMCwzLjUsMy45LDcuMiw3LjEsNy4yDQoJCQkJCWMyLjcsMCw4LjItNS42LDEyLjItMTIuNGwwLjIsMC44YzIuMSw3LjcsNC43LDExLjcsNy44LDExLjdjMy4xLDAsOC4yLTYuNCwxMS41LTE0LjVjMy4zLDEuNCw3LjIsMS44LDkuNSwxLjkNCgkJCQkJYzAuOSwxMy45LDEyLjUsMTQuMywxMy45LDE0LjNjOC42LDAsMTUuOS02LjIsMTUuOS0xMy41Qy0yMjQuMywxNy41LTIzMC4yLDE3LjQtMjMwLjMsMTcuNHogTS0yNDAuOCwxMS42YzAsMC0wLjEsNC42LTUuMyw2LjkNCgkJCQkJYzAuNS02LjEsMi0xMS42LDMtMTEuNkMtMjQyLDctMjQwLjgsOC43LTI0MC44LDExLjZ6Ii8+DQoJCQkJPHBhdGggY2xhc3M9InN0MCIgZD0iTS0zMDAuNyw2LjFjMCwwLjIsMC4xLDAuMywwLjMsMC40YzQuMSwwLjYsNi44LTAuNyw2LjgtNy4zYzAtNi4yLTYuNC0xLjMtNy42LTAuNA0KCQkJCQljLTAuMSwwLjEtMC4xLDAuMi0wLjEsMC40Qy0zMDAuMiwxLjYtMzAwLjYsNS4xLTMwMC43LDYuMXoiLz4NCgkJCTwvZz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI4OS41ODQ0IiB5MT0iMjYuNzY4IiB4Mj0iLTI4Mi44ODIzIiB5Mj0iMjQuNTM0Ij4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQkJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPg0KCQkJPC9saW5lYXJHcmFkaWVudD4NCgkJCTxwYXRoIGNsYXNzPSJzdDIiIGQ9Ik0tMjg0LjIsMTkuNGMtMS42LDIuNy00LDYuNC02LjEsOC43YzAuNSwxLjIsMS4xLDIuNywxLjcsMy45YzEuOS0yLjEsMy44LTQuOCw1LjUtNy42TC0yODQuMiwxOS40eiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8yXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjY5LjAxOTQiIHkxPSIyOC40Nzc3IiB4Mj0iLTI2NS4xMzIyIiB5Mj0iMjEuNjQxNiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3QzIiBkPSJNLTI2NS4yLDIxLjVjLTAuOC0wLjQtMS41LTEtMS41LTFjLTEuNCwzLjEtMy4zLDYtNC44LDcuOWMwLjcsMSwxLjksMi4zLDIuOCwzLjNjMS44LTIuNSwzLjctNS44LDUuMS05LjMNCgkJCQlDLTI2My41LDIyLjMtMjY0LjQsMjItMjY1LjIsMjEuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfM18iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OC42MjU0IiB5MT0iMzEuNjE0NyIgeDI9Ii0yNDkuNDI5NyIgeTI9IjI0LjQ2NTkiPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCQk8L2xpbmVhckdyYWRpZW50Pg0KCQkJPHBhdGggY2xhc3M9InN0NCIgZD0iTS0yNDYuMiwyMy41YzAsMC0yLDAuNC00LDAuNmMtMiwwLjItMy45LDAuMS0zLjksMC4xYzAuMyw0LDEuNCw2LjgsMi45LDguOWw3LjMtMC42DQoJCQkJQy0yNDUuNCwzMC41LTI0Ni4xLDI3LjEtMjQ2LjIsMjMuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfNF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI0OS43MjY3IiB5MT0iMTYuNDE5IiB4Mj0iLTI0OS43MjY3IiB5Mj0iMjMuNjIzNyI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q1IiBkPSJNLTI1MiwxMS4zTC0yNTIsMTEuM2MtMC4yLDAuNi0wLjUsMS4zLTAuNywyYzAsMC4yLTAuMSwwLjMtMC4xLDAuNWMtMC40LDEuMy0wLjcsMi42LTAuOSwzLjkNCgkJCQljMCwwLjItMC4xLDAuMy0wLjEsMC41Yy0wLjEsMC42LTAuMiwxLjItMC4yLDEuOGM0LjcsMCw3LjktMS40LDcuOS0xLjRjMC0wLjUsMC4xLTAuOSwwLjEtMS40YzAtMC4xLDAtMC4yLDAtMC4zDQoJCQkJYzAtMC40LDAuMS0wLjcsMC4xLTEuMWMwLTAuMSwwLTAuMiwwLTAuM2MwLjEtMC45LDAuMy0xLjgsMC40LTIuNkwtMjUyLDExLjN6IE0tMjU0LjEsMTkuOUMtMjU0LjEsMTkuOS0yNTQuMSwxOS45LTI1NC4xLDE5LjkNCgkJCQlMLTI1NC4xLDE5LjlDLTI1NC4xLDE5LjktMjU0LjEsMTkuOS0yNTQuMSwxOS45eiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF81XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzEzLjAyNzIiIHkxPSIyOC4yMjc4IiB4Mj0iLTMxMy4wMjcyIiB5Mj0iMTkuMjkxNyI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q2IiBkPSJNLTMxNy43LDI4LjZsNC40LDEuOWMyLjEtMywzLjQtNi4xLDQuMi04LjVsMC44LTMuMmwtMi4xLDAuM0MtMzEyLjMsMjMuMS0zMTQuOSwyNi4zLTMxNy43LDI4LjZ6Ii8+DQoJCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzZfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yNzcuNTIzOSIgeTE9IjkuNzg4IiB4Mj0iLTI3OC42NDYzIiB5Mj0iMTEuNTc1NiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q3IiBkPSJNLTI3Ny45LDE0LjJjMS4xLTMsMS43LTUuNSwxLjctNi43YzAtMC4xLDAtMC4yLDAtMC4zbC0yLjYsMC44Qy0yNzguOCw4LTI3OC43LDkuNy0yNzcuOSwxNC4yeiIvPg0KCQkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF83XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMjYyLjU1MjkiIHkxPSIxMy41MjQ4IiB4Mj0iLTI2My4xODY2IiB5Mj0iMTUuOTMyNiI+DQoJCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJCTwvbGluZWFyR3JhZGllbnQ+DQoJCQk8cGF0aCBjbGFzcz0ic3Q4IiBkPSJNLTI2My45LDEyYzAsMC0wLjcsMi4xLDIuMiw0LjdjMC41LTIuMSwwLjgtNC4yLDAuOS02TC0yNjMuOSwxMnoiLz4NCgkJPC9nPg0KCTwvZz4NCgk8Zz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNLTM1Ni44LDEzNi4ybC0wLjIsMGwwLDAuMmMwLjMsMS43LDAsNC4xLTEuMiw1LjdjLTAuNywwLjktMS43LDEuNC0yLjgsMS40Yy0xLjgsMC0yLjUtMi4yLTIuNi00LjYNCgkJCQljMy0wLjcsNS0yLjYsNS00LjhjMC0xLjYtMC41LTQuMi0zLjYtNC4yYy0yLjksMC00LjQsNC40LTQuNyw3LjRjLTEuNSwwLTIuNi0wLjctMy4zLTEuNGMwLjMtMS4xLDAuNC0yLDAuNC0yLjkNCgkJCQljMC0xLjItMC45LTEuOC0xLjYtMS44Yy0xLjIsMC0yLjMsMS4xLTIuMywzLjJjMCwxLjMsMC41LDIuMywxLjUsMy4xYy0wLjksMi0yLjMsMy43LTIuOCw0LjJjLTAuNC0wLjgtMS42LTMuOC0yLTYuOQ0KCQkJCWMwLjUtMS4zLDAuNy0yLjMsMC43LTIuOGMwLTAuOC0wLjUtMS4zLTEuNC0xLjNjLTEuMiwwLTMsMC43LTMsMC44bC0wLjEsMC4xbDAsMC4xYzAsMCwwLjUsMi42LDEuMSw1LjQNCgkJCQljLTEuMSwxLjctMi45LDQuNi0zLjksNC42Yy0xLjcsMCwxLjEtOC43LTAuMS05YzAsMC0wLjEsMC0wLjEsMGMtMC42LDAuNC03LjMsNC4xLTE2LjEsNC4xYzAsMCwwLDAuMiwwLjEsMC4zDQoJCQkJYzAuMSwwLjEsMC4yLDAuMiwwLjIsMC4yYzIuNSwwLjMsNi4xLDAsOC44LTAuNGMtMS42LDMuMy00LjMsNS42LTYuOSw1LjZjLTQuOCwwLTguNS01LjgtOC41LTUuOGMxLjUtMS4zLDMuOS01LjYsNy41LTUuNg0KCQkJCWMzLjUsMCw1LjEsMS45LDUuMSwxLjlsMC40LTAuNmMwLDAtMS43LTUuOC02LjMtNS44cy05LjYsNy43LTEyLjUsOS40YzAsMCw0LDkuNSwxMi43LDkuNWM3LjMsMCw5LjItNyw5LjUtOC43DQoJCQkJYzEuOC0wLjMsMy0wLjUsMy0wLjVzLTAuNCwzLjYtMC40LDUuMXMxLjYsMy4xLDMsMy4xYzEuMiwwLDMuNS0yLjQsNS4yLTUuM2wwLjEsMC4zYzAuOSwzLjMsMiw1LDMuMyw1YzEuMywwLDMuNS0yLjcsNC45LTYuMQ0KCQkJCWMxLjQsMC42LDMuMSwwLjgsNCwwLjhjMC40LDUuOSw1LjMsNi4xLDUuOSw2LjFjMy43LDAsNi44LTIuNiw2LjgtNS43Qy0zNTQuMywxMzYuMy0zNTYuOCwxMzYuMi0zNTYuOCwxMzYuMnogTS0zNjEuMiwxMzMuNw0KCQkJCWMwLDAsMCwyLTIuMywzYzAuMi0yLjYsMC44LTQuOSwxLjMtNC45Qy0zNjEuOCwxMzEuOC0zNjEuMiwxMzIuNS0zNjEuMiwxMzMuN3oiLz4NCgkJCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0tMzg2LjcsMTMxLjRjMCwwLjEsMCwwLjEsMC4xLDAuMmMxLjcsMC4yLDIuOS0wLjMsMi45LTMuMWMwLTIuNi0yLjctMC42LTMuMi0wLjJjMCwwLTAuMSwwLjEsMCwwLjINCgkJCQlDLTM4Ni41LDEyOS41LTM4Ni43LDEzMC45LTM4Ni43LDEzMS40eiIvPg0KCQk8L2c+DQoJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfOF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM4MS45OTkzIiB5MT0iMTQwLjE3NjkiIHgyPSItMzc5LjE1MDQiIHkyPSIxMzkuMjI3MiI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDkiIGQ9Ik0tMzc5LjcsMTM3Yy0wLjcsMS4xLTEuNywyLjctMi42LDMuN2MwLjIsMC41LDAuNSwxLjEsMC43LDEuN2MwLjgtMC45LDEuNi0yLDIuNC0zLjJMLTM3OS43LDEzN3oiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF85XyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzczLjI1NzUiIHkxPSIxNDAuOTAzNyIgeDI9Ii0zNzEuNjA1MSIgeTI9IjEzNy45OTc4Ij4NCgkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJPC9saW5lYXJHcmFkaWVudD4NCgkJPHBhdGggY2xhc3M9InN0MTAiIGQ9Ik0tMzcxLjYsMTM3LjljLTAuMy0wLjItMC42LTAuNC0wLjYtMC40Yy0wLjYsMS4zLTEuNCwyLjUtMiwzLjRjMC4zLDAuNCwwLjgsMSwxLjIsMS40DQoJCQljMC44LTEuMSwxLjYtMi40LDIuMi00Qy0zNzAuOSwxMzguMy0zNzEuMywxMzguMS0zNzEuNiwxMzcuOXoiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xMF8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM2NC41ODg0IiB5MT0iMTQyLjIzNzIiIHgyPSItMzY0LjkzMDMiIHkyPSIxMzkuMTk4MyI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDExIiBkPSJNLTM2My42LDEzOC44YzAsMC0wLjgsMC4yLTEuNywwLjNjLTAuOCwwLjEtMS43LDAtMS43LDBjMC4xLDEuNywwLjYsMi45LDEuMiwzLjhsMy4xLTAuMw0KCQkJQy0zNjMuMiwxNDEuOC0zNjMuNSwxNDAuMy0zNjMuNiwxMzguOHoiLz4NCgkJPGxpbmVhckdyYWRpZW50IGlkPSJTVkdJRF8xMV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTM2NS4wNTY2IiB5MT0iMTM1Ljc3NzciIHgyPSItMzY1LjA1NjYiIHkyPSIxMzguODQwMyI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDEyIiBkPSJNLTM2NiwxMzMuNkwtMzY2LDEzMy42Yy0wLjEsMC4zLTAuMiwwLjYtMC4zLDAuOGMwLDAuMSwwLDAuMS0wLjEsMC4yYy0wLjIsMC42LTAuMywxLjEtMC40LDEuNw0KCQkJYzAsMC4xLDAsMC4xLDAsMC4yYzAsMC4zLTAuMSwwLjUtMC4xLDAuOGMyLDAsMy40LTAuNiwzLjQtMC42YzAtMC4yLDAtMC40LDAuMS0wLjZjMCwwLDAtMC4xLDAtMC4xYzAtMC4yLDAtMC4zLDAuMS0wLjQNCgkJCWMwLDAsMC0wLjEsMC0wLjFjMC4xLTAuNCwwLjEtMC44LDAuMi0xLjFMLTM2NiwxMzMuNnogTS0zNjYuOSwxMzcuM0MtMzY2LjksMTM3LjMtMzY2LjksMTM3LjMtMzY2LjksMTM3LjNMLTM2Ni45LDEzNy4zDQoJCQlDLTM2Ni45LDEzNy4zLTM2Ni45LDEzNy4zLTM2Ni45LDEzNy4zeiIvPg0KCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEyXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzkxLjk2NDQiIHkxPSIxNDAuNzk3NSIgeDI9Ii0zOTEuOTY0NCIgeTI9IjEzNi45OTg5Ij4NCgkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMzNzhGNDMiLz4NCgkJPC9saW5lYXJHcmFkaWVudD4NCgkJPHBhdGggY2xhc3M9InN0MTMiIGQ9Ik0tMzk0LDE0MWwxLjksMC44YzAuOS0xLjMsMS40LTIuNiwxLjgtMy42bDAuMy0xLjRsLTAuOSwwLjFDLTM5MS43LDEzOC42LTM5Mi43LDE0MC0zOTQsMTQxeiIvPg0KCQk8bGluZWFyR3JhZGllbnQgaWQ9IlNWR0lEXzEzXyIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiIHgxPSItMzc2Ljg3MjYiIHkxPSIxMzIuOTU5IiB4Mj0iLTM3Ny4zNDk4IiB5Mj0iMTMzLjcxODkiPg0KCQkJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzY2QkI2QSIvPg0KCQkJPHN0b3AgIG9mZnNldD0iMSIgc3R5bGU9InN0b3AtY29sb3I6IzM3OEY0MyIvPg0KCQk8L2xpbmVhckdyYWRpZW50Pg0KCQk8cGF0aCBjbGFzcz0ic3QxNCIgZD0iTS0zNzcsMTM0LjhjMC41LTEuMywwLjctMi4zLDAuNy0yLjhjMCwwLDAtMC4xLDAtMC4xbC0xLjEsMC4zQy0zNzcuNCwxMzIuMi0zNzcuNCwxMzIuOS0zNzcsMTM0Ljh6Ii8+DQoJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTRfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0zNzAuNTA4OCIgeTE9IjEzNC41NDc1IiB4Mj0iLTM3MC43NzgxIiB5Mj0iMTM1LjU3MSI+DQoJCQk8c3RvcCAgb2Zmc2V0PSIwIiBzdHlsZT0ic3RvcC1jb2xvcjojNjZCQjZBIi8+DQoJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCTwvbGluZWFyR3JhZGllbnQ+DQoJCTxwYXRoIGNsYXNzPSJzdDE1IiBkPSJNLTM3MS4xLDEzMy45YzAsMC0wLjMsMC45LDAuOSwyYzAuMi0wLjksMC40LTEuOCwwLjQtMi42TC0zNzEuMSwxMzMuOXoiLz4NCgk8L2c+DQoJPGc+DQoJCTxyZWN0IHg9Ii0zMTMuNCIgeT0iMTE1LjEiIGNsYXNzPSJzdDE2IiB3aWR0aD0iNDIiIGhlaWdodD0iNDIiLz4NCgkJPGc+DQoJCQk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNLTI5Mi42LDEzNy41YzAuMSwwLjIsMC4yLDAuMywwLjIsMC4zYzMuNCwwLjQsOC4zLDAsMTItMC42Yy0yLjEsNC42LTUuOSw3LjYtOS40LDcuNg0KCQkJCWMtNi41LDAtMTEuNi03LjktMTEuNi03LjljMi0xLjgsNS40LTcuNiwxMC4yLTcuNnM2LjksMi42LDYuOSwyLjZsMC41LTAuOWMwLDAtMi4zLTcuOS04LjYtNy45Yy02LjQsMC0xMy4xLDEwLjQtMTcuMSwxMi44DQoJCQkJYzAsMCw1LjQsMTIuOSwxNy4zLDEyLjljMTAsMCwxMi41LTkuNSwxMy0xMS45YzEuMy0wLjIsMi40LTAuNCwzLjItMC41YzAuMi0wLjUsMC41LTEuNSwwLjMtMi44Yy00LDEuNS0xMCwzLjMtMTcuMSwzLjMNCgkJCQlDLTI5Mi43LDEzNy0yOTIuNywxMzcuMi0yOTIuNiwxMzcuNXoiLz4NCgkJCTxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMTVfIiBncmFkaWVudFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIgeDE9Ii0yODEuNzk2MiIgeTE9IjE0Mi40ODEzIiB4Mj0iLTI4MS43OTYyIiB5Mj0iMTM3LjM0ODkiPg0KCQkJCTxzdG9wICBvZmZzZXQ9IjAiIHN0eWxlPSJzdG9wLWNvbG9yOiM2NkJCNkEiLz4NCgkJCQk8c3RvcCAgb2Zmc2V0PSIxIiBzdHlsZT0ic3RvcC1jb2xvcjojMzc4RjQzIi8+DQoJCQk8L2xpbmVhckdyYWRpZW50Pg0KCQkJPHBhdGggY2xhc3M9InN0MTciIGQ9Ik0tMjc5LjEsMTM3LjFsLTEuMiwwLjFjMCwwLjEtMC4xLDAuMi0wLjIsMC4zYy0wLjIsMC40LTAuNCwwLjctMC42LDEuMWMtMC4xLDAuMi0wLjIsMC4zLTAuMywwLjUNCgkJCQljLTAuMiwwLjQtMC41LDAuOC0wLjgsMS4xYy0wLjEsMC4xLTAuMSwwLjEtMC4yLDAuMmMtMC43LDAuOS0xLjQsMS43LTIuMiwyLjNsMi41LDEuMUMtMjgwLjEsMTQxLjItMjc5LjMsMTM4LjItMjc5LjEsMTM3LjF6Ii8+DQoJCTwvZz4NCgk8L2c+DQoJPGc+DQoJCTxkZWZzPg0KCQkJPGNpcmNsZSBpZD0iU1ZHSURfMTZfIiBjeD0iLTE5OS40IiBjeT0iMTM2LjEiIHI9IjE2LjYiLz4NCgkJPC9kZWZzPg0KCQk8Y2xpcFBhdGggaWQ9IlNWR0lEXzE3XyI+DQoJCQk8dXNlIHhsaW5rOmhyZWY9IiNTVkdJRF8xNl8iICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZTsiLz4NCgkJPC9jbGlwUGF0aD4NCgkJPHBhdGggY2xhc3M9InN0MTgiIGQ9Ik0tMTk3LDEzNy4zYzAuMSwwLjEsMC4yLDAuMywwLjIsMC4zYzIuOSwwLjQsNy4xLDAsMTAuMy0wLjVjLTEuOCwzLjktNS4xLDYuNS04LDYuNWMtNS42LDAtOS45LTYuOC05LjktNi44DQoJCQljMS43LTEuNSw0LjYtNi41LDguNy02LjVzNS45LDIuMyw1LjksMi4zbDAuNS0wLjdjMCwwLTEuOS02LjctNy40LTYuN3MtMTEuMiw4LjktMTQuNiwxMWMwLDAsNC43LDExLDE0LjgsMTENCgkJCWM4LjUsMCwxMC43LTguMiwxMS4xLTEwLjJjMS4xLTAuMiwyLjEtMC4zLDIuNy0wLjRjMC4yLTAuNSwwLjQtMS4zLDAuMy0yLjRjLTMuNCwxLjMtOC41LDIuOC0xNC42LDIuOA0KCQkJQy0xOTcuMSwxMzYuOS0xOTcuMSwxMzcuMS0xOTcsMTM3LjN6Ii8+DQoJPC9nPg0KPC9nPg0KPGcgaWQ9IkxheWVyXzIiPg0KCTxwYXRoIGNsYXNzPSJzdDE5IiBkPSJNMTQ0LjMsODIuNWMtMS45LDkuNi0xMi4xLDQ4LjItNTIuNSw0OC4yYy00OC4yLDAtNzAuMi01Mi4yLTcwLjItNTIuMmMxNi4xLTkuOCw0My40LTUyLDY5LjItNTINCgkJczM0LjksMzEuOSwzNC45LDMxLjlsLTIuMiwzLjVjMCwwLTguNS0xMC43LTI4LTEwLjdTNjIuNiw3NC43LDU0LjQsODJjMCwwLDIwLjUsMzIuMSw0Ni45LDMyLjFjMTQuMiwwLDI5LjUtMTIuMywzOC4xLTMwLjgNCgkJYy0xNSwyLjEtMzQuNyw0LTQ4LjYsMi4yYzAsMC0wLjctMC42LTEtMS4zYy0wLjQtMC45LTAuNS0xLjgtMC41LTEuOGMyNy42LDAsNTEuMy02LjUsNjcuNC0xMi41QzE1Mi4zLDMwLjUsMTE5LDAsNzguNiwwDQoJCUMzNS4yLDAsMCwzNS4yLDAsNzguNmMwLDQzLjQsMzUuMiw3OC42LDc4LjYsNzguNmM0Mi44LDAsNzcuNi0zNC4yLDc4LjYtNzYuOEMxNTQuMiw4MC45LDE0OS43LDgxLjcsMTQ0LjMsODIuNXoiLz4NCjwvZz4NCjwvc3ZnPg0K',
	];

	// Return the chosen icon's SVG string
	return $svgs[ $icon ];
}

/**
 * Modify Admin Nav Menu Label
 *
 * @param object $post_type The current object to add a menu items meta box for.
 *
 * @return mixed
 * @since 1.3
 */
function modify_nav_menu_meta_box_object( $post_type ) {
	if ( isset( $post_type->name ) && $post_type->name == 'give_forms' ) {
		$post_type->labels->name = esc_html__( 'Donation Forms', 'give' );
	}

	return $post_type;
}

add_filter( 'nav_menu_meta_box_object', 'modify_nav_menu_meta_box_object' );

/**
 * Show Donation Forms Post Type in Appearance > Menus by default on fresh install.
 *
 * @return bool
 * @todo  Remove this, when WordPress Core ticket is resolved (https://core.trac.wordpress.org/ticket/16828).
 *
 * @since 1.8.14
 */
function give_donation_metabox_menu() {

	// Get Current Screen.
	$screen = get_current_screen();

	// Proceed, if current screen is navigation menus.
	if ( 'nav-menus' === $screen->id && give_is_setting_enabled( give_get_option( 'forms_singular' ) ) && ! get_user_option( 'give_is_donation_forms_menu_updated' ) ) {

		// Return false, if it fails to retrieve hidden meta box list and is not admin.
		if ( ! is_admin() || ( ! $hidden_meta_boxes = get_user_option( 'metaboxhidden_nav-menus' ) ) ) {
			return false;
		}

		// Return false, In case, we don't find 'Donation Form' in hidden meta box list.
		if ( ! in_array( 'add-post-type-give_forms', $hidden_meta_boxes, true ) ) {
			return false;
		}

		// Exclude 'Donation Form' value from hidden meta box's list.
		$hidden_meta_boxes = array_diff( $hidden_meta_boxes, [ 'add-post-type-give_forms' ] );

		// Get current user ID.
		$user = wp_get_current_user();

		update_user_option( $user->ID, 'metaboxhidden_nav-menus', $hidden_meta_boxes, true );
		update_user_option( $user->ID, 'give_is_donation_forms_menu_updated', true, true );
	}
}

add_action( 'current_screen', 'give_donation_metabox_menu' );

/**
 * Array_column backup usage
 *
 * This file is part of the array_column library.
 *
 * @since      : 1.3.0.1
 *
 * @copyright  Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license    https://opensource.org/licenses/MIT MIT
 */

if ( ! function_exists( 'array_column' ) ) {
	/**
	 * Returns the values from a single column of the input array, identified by
	 * the $columnKey.
	 *
	 * Optionally, you may provide an $indexKey to index the values in the returned
	 * array by the values from the $indexKey column in the input array.
	 *
	 * @param array      $input     A multi-dimensional array (record set) from which to pull
	 *                              a column of values.
	 * @param int|string $columnKey The column of values to return. This value may be the
	 *                              integer key of the column you wish to retrieve, or it
	 *                              may be the string key name for an associative array.
	 * @param mixed      $indexKey  (Optional.) The column to use as the index/keys for
	 *                              the returned array. This value may be the integer key
	 *                              of the column, or it may be the string key name.
	 *
	 * @return array|boolean|null
	 */
	function array_column( $input = null, $columnKey = null, $indexKey = null ) {
		// Using func_get_args() in order to check for proper number of
		// parameters and trigger errors exactly as the built-in array_column()
		// does in PHP 5.5.
		$argc   = func_num_args();
		$params = func_get_args();

		if ( $argc < 2 ) {
			trigger_error( sprintf( 'array_column() expects at least 2 parameters, %s given.', $argc ), E_USER_WARNING );

			return null;
		}

		if ( ! is_array( $params[0] ) ) {
			trigger_error( sprintf( 'array_column() expects parameter 1 to be array, %s given.', gettype( $params[0] ) ), E_USER_WARNING );

			return null;
		}

		if ( ! is_int( $params[1] ) && ! is_float( $params[1] ) && ! is_string( $params[1] ) && $params[1] !== null && ! ( is_object( $params[1] ) && method_exists( $params[1], '__toString' ) ) ) {
			trigger_error( 'array_column(): The column key should be either a string or an integer.', E_USER_WARNING );

			return false;
		}

		if ( isset( $params[2] ) && ! is_int( $params[2] ) && ! is_float( $params[2] ) && ! is_string( $params[2] ) && ! ( is_object( $params[2] ) && method_exists( $params[2], '__toString' ) ) ) {
			trigger_error( 'array_column(): The index key should be either a string or an integer.', E_USER_WARNING );

			return false;
		}

		$paramsInput     = $params[0];
		$paramsColumnKey = ( $params[1] !== null ) ? (string) $params[1] : null;

		$paramsIndexKey = null;
		if ( isset( $params[2] ) ) {
			if ( is_float( $params[2] ) || is_int( $params[2] ) ) {
				$paramsIndexKey = (int) $params[2];
			} else {
				$paramsIndexKey = (string) $params[2];
			}
		}

		$resultArray = [];

		foreach ( $paramsInput as $row ) {
			$key    = $value = null;
			$keySet = $valueSet = false;

			if ( $paramsIndexKey !== null && array_key_exists( $paramsIndexKey, $row ) ) {
				$keySet = true;
				$key    = (string) $row[ $paramsIndexKey ];
			}

			if ( $paramsColumnKey === null ) {
				$valueSet = true;
				$value    = $row;
			} elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
				$valueSet = true;
				$value    = $row[ $paramsColumnKey ];
			}

			if ( $valueSet ) {
				if ( $keySet ) {
					$resultArray[ $key ] = $value;
				} else {
					$resultArray[] = $value;
				}
			}
		}

		return $resultArray;
	}
}// End if().

/**
 * Determines the receipt visibility status.
 *
 * @param int $donation_id Donation ID.
 *
 * @return bool Whether the receipt is visible or not.
 * @since 1.3.2
 */
function give_can_view_receipt( $donation_id ) {

	global $give_receipt_args;

	$donor            = false;
	$can_view_receipt = false;

	// Bail out, if donation id doesn't exist.
	if ( empty( $donation_id ) ) {
		return $can_view_receipt;
	}

	$give_receipt_args['id'] = $donation_id;

	// Add backward compatibility.
	if ( ! is_numeric( $donation_id ) ) {
		$give_receipt_args['id'] = give_get_donation_id_by_key( $donation_id );
	}

	// Return to download receipts from admin panel.
	if ( current_user_can( 'export_give_reports' ) ) {

		/**
		 * This filter will be used to modify can view receipt response when accessed from admin.
		 *
		 * @since 2.3.1
		 */
		return apply_filters( 'give_can_admin_view_receipt', true );
	}

	if ( is_user_logged_in() || current_user_can( 'view_give_sensitive_data' ) ) {

		// Proceed only, if user is logged in or can view sensitive Give data.
		$donor = Give()->donors->get_donor_by( 'user_id', get_current_user_id() );

	} elseif ( ! is_user_logged_in() ) {

		// Check whether it is purchase session?
		// This condition is to show receipt to donor after donation.
		$purchase_session = give_get_purchase_session();

		if (
			! empty( $purchase_session )
			&& absint( $purchase_session['donation_id'] ) === absint( $donation_id )
		) {
			$donor = Give()->donors->get_donor_by( 'email', $purchase_session['user_email'] );
		}

		// Check whether it is receipt access session?
		$receipt_session    = give_get_receipt_session();
		$email_access_token = ! empty( $_COOKIE['give_nl'] ) ? give_clean( $_COOKIE['give_nl'] ) : false;

		if (
			! empty( $receipt_session ) ||
			(
				give_is_setting_enabled( give_get_option( 'email_access' ) ) &&
				! empty( $email_access_token )
			)
		) {
			$donor = ! empty( $email_access_token )
				? Give()->donors->get_donor_by_token( $email_access_token )
				: false;
		}
	}

	// If donor object exists, compare the donation ids of donor with the donation receipt donor tries to access.
	if ( is_object( $donor ) ) {
		$is_donor_donated = in_array( (int) $donation_id, array_map( 'absint', explode( ',', $donor->payment_ids ) ), true );
		$can_view_receipt = $is_donor_donated ? true : $can_view_receipt;

		if ( ! $is_donor_donated ) {
			Give()->session->set( 'donor_donation_mismatch', true );
		}
	}

	return (bool) apply_filters( 'give_can_view_receipt', $can_view_receipt, $donation_id );

}

/**
 * Fallback for cal_days_in_month
 *
 * Fallback in case the calendar extension is not loaded in PHP; Only supports Gregorian calendar
 */
if ( ! function_exists( 'cal_days_in_month' ) ) {
	/**
	 * cal_days_in_month
	 *
	 * @param int $calendar
	 * @param int $month
	 * @param int $year
	 *
	 * @return bool|string
	 */
	function cal_days_in_month( $calendar, $month, $year ) {
		return date( 't', mktime( 0, 0, 0, $month, 1, $year ) );
	}
}

/**
 * Get plugin info including status, type, and license validation.
 *
 * @return array Plugin info plus status, type, and license validation if
 *               available.
 * @since 1.8.0
 *
 * @todo  update this function to query give addon and additional
 *
 * This is an enhanced version of get_plugins() that returns the status
 * (`active` or `inactive`) of all plugins, type of plugin (`add-on` or `other`
 * and license validation for Give add-ons (`true` or `false`). Does not include
 * MU plugins.
 */
function give_get_plugins( $args = [] ) {
	$plugins             = get_plugins();
	$active_plugin_paths = (array) get_option( 'active_plugins', [] );

	if ( is_multisite() ) {
		$network_activated_plugin_paths = array_keys( get_site_option( 'active_sitewide_plugins', [] ) );
		$active_plugin_paths            = array_merge( $active_plugin_paths, $network_activated_plugin_paths );
	}

	foreach ( $plugins as $plugin_path => $plugin_data ) {
		// Is plugin active?
		if ( in_array( $plugin_path, $active_plugin_paths ) ) {
			$plugins[ $plugin_path ]['Status'] = 'active';
		} else {
			$plugins[ $plugin_path ]['Status'] = 'inactive';
		}

		$dirname                         = strtolower( dirname( $plugin_path ) );
		$plugins[ $plugin_path ]['Dir']  = $dirname;
		$plugins[ $plugin_path ]['Path'] = $plugin_path;

		// A third party add-on may contain more then one author like sofort, so it is better to compare array.
		$author = false !== strpos( $plugin_data['Author'], ',' )
			? array_map( 'trim', explode( ',', $plugin_data['Author'] ) )
			: [ $plugin_data['Author'] ];

		// Is the plugin a Give add-on?
		if (
			false !== strpos( $dirname, 'give-' )
			&& (
				false !== strpos( $plugin_data['PluginURI'], 'givewp.com' )
				|| array_intersect( $author, [ 'WordImpress', 'GiveWP' ] )
			)
		) {
			// Plugin is a Give-addon.
			$plugins[ $plugin_path ]['Type'] = 'add-on';

			$license_active = Give_License::get_license_by_plugin_dirname( $dirname );

			// Does a valid license exist?
			$plugins[ $plugin_path ]['License'] = $license_active && 'valid' === $license_active['license'];

		} else {
			// Plugin is not a Give add-on.
			$plugins[ $plugin_path ]['Type'] = 'other';
		}
	}

	if ( ! empty( $args['only_add_on'] ) ) {
		$plugins = array_filter(
			$plugins,
			static function( $plugin ) {
				return 'add-on' === $plugin['Type'];
			}
		);
	}

	if ( ! empty( $args['only_premium_add_ons'] ) ) {
		$premiumAddonsListManger = give( PremiumAddonsListManager::class );

		foreach ( $plugins as $key => $plugin ) {
			if ( 'add-on' !== $plugin['Type'] ) {
				unset( $plugins[ $key ] );
			}

			if ( ! $premiumAddonsListManger->isPremiumAddons( $plugin['PluginURI'] ) ) {
				unset( $plugins[ $key ] );
			}
		}
	}

	return $plugins;
}

/**
 * Check if terms enabled or not for form.
 *
 * @param $form_id
 *
 * @return bool
 * @since 1.8
 */
function give_is_terms_enabled( $form_id ) {
	$form_option = give_get_meta( $form_id, '_give_terms_option', true );

	if ( give_is_setting_enabled( $form_option, 'global' ) && give_is_setting_enabled( give_get_option( 'terms' ) ) ) {
		return true;

	} elseif ( give_is_setting_enabled( $form_option ) ) {
		return true;

	} else {
		return false;
	}
}

/**
 * Delete donation stats cache.
 *
 * @param string|array $date_range Date for stats.
 *                                 Date value should be in today, yesterday, this_week, last_week, this_month,
 *                                 last_month, this_quarter, last_quarter, this_year, last_year. For date value other,
 *                                 all cache will be removed.
 *
 * @param array        $args
 *
 * @return WP_Error|bool
 * @since 1.8.7
 *
 * @todo  Resolve stats cache key naming issue. Currently it is difficult to regenerate cache key.
 */
function give_delete_donation_stats( $date_range = '', $args = [] ) {

	// Delete all cache.
	$status = Give_Cache::delete( Give_Cache::get_options_like( 'give_stats' ) );

	/**
	 * Fire the action when donation stats delete.
	 *
	 * @param string|array $date_range
	 * @param array        $args
	 *
	 * @since 1.8.7
	 */
	do_action( 'give_delete_donation_stats', $status, $date_range, $args );

	return $status;
}

/**
 * Check if admin creating new donation form or not.
 *
 * @return bool
 * @since 2.0
 */
function give_is_add_new_form_page() {
	$status = false;

	if ( false !== strpos( $_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms' ) ) {
		$status = true;
	}

	return $status;
}

/**
 * Get Form/Payment meta.
 *
 * Note: This function will help you to get meta for payment and form.
 *       If you want to get meta for donors then use get_meta of Give_Donor and
 *       If you want to get meta for logs then use get_meta of Give_Logging->logmeta_db.
 *
 * @param int    $id
 * @param string $meta_key
 * @param bool   $single
 * @param bool   $default
 * @param string $meta_type
 *
 * @return mixed
 * @since 1.8.8
 */
function give_get_meta( $id, $meta_key = '', $single = false, $default = false, $meta_type = '' ) {
	switch ( $meta_type ) {
		case 'donation':
			$meta_value = Give()->payment_meta->get_meta( $id, $meta_key, $single );
			break;

		case 'form':
			$meta_value = Give()->form_meta->get_meta( $id, $meta_key, $single );
			break;

		case 'donor':
			$meta_value = Give()->donor_meta->get_meta( $id, $meta_key, $single );
			break;

		default:
			$meta_value = get_post_meta( $id, $meta_key, $single );
	}

	/**
	 * Filter the meta value
	 *
	 * @since 1.8.8
	 */
	$meta_value = apply_filters( 'give_get_meta', $meta_value, $id, $meta_key, $default, $meta_type );

	if ( ( empty( $meta_key ) || empty( $meta_value ) ) && $default ) {
		$meta_value = $default;
	}

	return $meta_value;
}

/**
 * Update Form/Payment meta.
 *
 * @param int    $id
 * @param string $meta_key
 * @param mixed  $meta_value
 * @param mixed  $prev_value
 * @param string $meta_type
 *
 * @return mixed
 * @since 1.8.8
 */
function give_update_meta( $id, $meta_key, $meta_value, $prev_value = '', $meta_type = '' ) {
	switch ( $meta_type ) {
		case 'donation':
			$status = Give()->payment_meta->update_meta( $id, $meta_key, $meta_value, $prev_value );
			break;

		case 'form':
			$status = Give()->form_meta->update_meta( $id, $meta_key, $meta_value, $prev_value );
			break;

		case 'donor':
			$status = Give()->donor_meta->update_meta( $id, $meta_key, $meta_value, $prev_value );
			break;

		default:
			$status = update_post_meta( $id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Filter the meta value update status
	 *
	 * @since 1.8.8
	 */
	return apply_filters( 'give_update_meta', $status, $id, $meta_key, $meta_value, $meta_type );
}

/**
 * Delete Form/Payment meta.
 *
 * @param int    $id
 * @param string $meta_key
 * @param string $meta_value
 * @param string $meta_type
 *
 * @return mixed
 * @since 1.8.8
 */
function give_delete_meta( $id, $meta_key, $meta_value = '', $meta_type = '' ) {
	switch ( $meta_type ) {
		case 'donation':
			$status = Give()->payment_meta->delete_meta( $id, $meta_key, $meta_value );
			break;

		case 'form':
			$status = Give()->form_meta->delete_meta( $id, $meta_key, $meta_value );
			break;

		case 'donor':
			$status = Give()->donor_meta->delete_meta( $id, $meta_key, $meta_value );
			break;

		default:
			$status = delete_post_meta( $id, $meta_key, $meta_value );
	}

	/**
	 * Filter the meta value delete status
	 *
	 * @since 1.8.8
	 */
	return apply_filters( 'give_delete_meta', $status, $id, $meta_key, $meta_value, $meta_type );
}

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @param string $upgrade_action The upgrade action to check completion for
 *
 * @return bool                   If the action has been added to the completed actions array
 * @since  1.0
 */
function give_has_upgrade_completed( $upgrade_action = '' ) {
	// Bailout.
	if ( empty( $upgrade_action ) ) {
		return false;
	}

	// Fresh install?
	// If fresh install then all upgrades will be consider as completed.
	$is_fresh_install = ! Give_Cache_Setting::get_option( 'give_version' );
	if ( $is_fresh_install ) {
		return true;
	}

	$completed_upgrades = give_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * For use when doing 'stepped' upgrade routines, to see if we need to start somewhere in the middle
 *
 * @return mixed   When nothing to resume returns false, otherwise starts the upgrade where it left off
 * @since 1.8
 */
function give_maybe_resume_upgrade() {
	$doing_upgrade = get_option( 'give_doing_upgrade', false );
	if ( empty( $doing_upgrade ) ) {
		return false;
	}

	return $doing_upgrade;
}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @param string $upgrade_action The action to add to the completed upgrades array
 *
 * @return bool                   If the function was successfully added
 * @since  1.0
 */
function give_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = give_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques.
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	/**
	 * Fire the action when any upgrade set to complete.
	 *
	 * @since 1.8.12
	 */
	do_action( 'give_set_upgrade_completed', $upgrade_action, $completed_upgrades );

	return update_option( 'give_completed_upgrades', $completed_upgrades, false );
}

/**
 * Get's the array of completed upgrade actions
 *
 * @return array The array of completed upgrades
 * @since  1.0
 */
function give_get_completed_upgrades() {
	return (array) Give_Cache_Setting::get_option( 'give_completed_upgrades' );
}

/**
 * In 2.0 we updated table for log, payment and form.
 *
 * Note: internal purpose only.
 *
 * @param string $type Context for table
 *
 * @return null|array
 * @since 2.0
 * @global wpdb  $wpdb
 */
function __give_v20_bc_table_details( $type ) {
	global $wpdb;
	$table = [];

	// Bailout.
	if ( empty( $type ) ) {
		return null;
	}

	switch ( $type ) {
		case 'form':
			$table['name']         = $wpdb->formmeta;
			$table['column']['id'] = 'form_id';

			break;

		case 'payment':
			$table['name']         = $wpdb->donationmeta;
			$table['column']['id'] = Give()->payment_meta->get_meta_type() . '_id';
	}

	// Backward compatibility.
	if ( ! give_has_upgrade_completed( 'v20_move_metadata_into_new_table' ) ) {
		$table['name']         = $wpdb->postmeta;
		$table['column']['id'] = 'post_id';
	}

	return $table;
}

/**
 * Remove the Give transaction pages from WP search results.
 *
 * @param WP_Query $query
 *
 * @since 1.8.13
 */
function give_remove_pages_from_search( $query ) {

	if ( ! $query->is_admin && $query->is_search && $query->is_main_query() ) {

		$transaction_failed = give_get_option( 'failure_page', 0 );
		$success_page       = give_get_option( 'success_page', 0 );

		$args = apply_filters(
			'give_remove_pages_from_search',
			[
				$transaction_failed,
				$success_page,
			],
			$query
		);
		$query->set( 'post__not_in', $args );
	}
}

add_action( 'pre_get_posts', 'give_remove_pages_from_search', 10, 1 );

/**
 * Inserts a new key/value before a key in the array.
 *
 * @param string       $key       The key to insert before.
 * @param array        $array     An array to insert in to.
 * @param string       $new_key   The key to insert.
 * @param array|string $new_value An value to insert.
 *
 * @return array The new array if the key exists, the passed array otherwise.
 *
 * @since 1.8.13
 *
 * @see   array_insert_before()
 */
function give_array_insert_before( $key, array &$array, $new_key, $new_value ) {
	if ( array_key_exists( $key, $array ) ) {
		$new = [];
		foreach ( $array as $k => $value ) {
			if ( $k === $key ) {
				$new[ $new_key ] = $new_value;
			}
			$new[ $k ] = $value;
		}

		return $new;
	}

	return $array;
}

/**
 * Inserts a new key/value after a key in the array.
 *
 * @param string       $key       The key to insert after.
 * @param array        $array     An array to insert in to.
 * @param string       $new_key   The key to insert.
 * @param array|string $new_value An value to insert.
 *
 * @return array The new array if the key exists, the passed array otherwise.
 *
 * @since 1.8.13
 *
 * @see   array_insert_before()
 */
function give_array_insert_after( $key, array &$array, $new_key, $new_value ) {
	if ( array_key_exists( $key, $array ) ) {
		$new = [];
		foreach ( $array as $k => $value ) {
			$new[ $k ] = $value;
			if ( $k === $key ) {
				$new[ $new_key ] = $new_value;
			}
		}

		return $new;
	}

	return $array;
}

/**
 * Pluck a certain field out of each object in a list.
 *
 * This has the same functionality and prototype of
 * array_column() (PHP 5.5) but also supports objects.
 *
 * @param array      $list      List of objects or arrays
 * @param int|string $field     Field from the object to place instead of the entire object
 * @param int|string $index_key Optional. Field from the object to use as keys for the new array.
 *                              Default null.
 *
 * @return array Array of found values. If `$index_key` is set, an array of found values with keys
 *               corresponding to `$index_key`. If `$index_key` is null, array keys from the original
 *               `$list` will be preserved in the results.
 * @since 1.8.13
 */
function give_list_pluck( $list, $field, $index_key = null ) {

	if ( ! $index_key ) {
		/**
		 * This is simple. Could at some point wrap array_column()
		 * if we knew we had an array of arrays.
		 */
		foreach ( $list as $key => $value ) {
			if ( is_object( $value ) ) {
				if ( isset( $value->$field ) ) {
					$list[ $key ] = $value->$field;
				}
			} else {
				if ( isset( $value[ $field ] ) ) {
					$list[ $key ] = $value[ $field ];
				}
			}
		}

		return $list;
	}

	/*
	 * When index_key is not set for a particular item, push the value
	 * to the end of the stack. This is how array_column() behaves.
	 */
	$newlist = [];
	foreach ( $list as $value ) {
		if ( is_object( $value ) ) {
			if ( isset( $value->$index_key ) ) {
				$newlist[ $value->$index_key ] = $value->$field;
			} else {
				$newlist[] = $value->$field;
			}
		} else {
			if ( isset( $value[ $index_key ] ) ) {
				$newlist[ $value[ $index_key ] ] = $value[ $field ];
			} else {
				$newlist[] = $value[ $field ];
			}
		}
	}

	$list = $newlist;

	return $list;
}

/**
 * Add meta data field to a donor.
 *
 * @param int    $donor_id   Donor ID.
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
 * @param bool   $unique     Optional. Whether the same key should not be added.
 *                           Default false.
 *
 * @return int|false Meta ID on success, false on failure.
 * @since 1.8.13
 */
function add_donor_meta( $donor_id, $meta_key, $meta_value, $unique = false ) {
	return add_metadata( 'give_customer', $donor_id, $meta_key, $meta_value, $unique );
}

/**
 * Remove metadata matching criteria from a Donor meta.
 *
 * You can match based on the key, or key and value. Removing based on key and
 * value, will keep from removing duplicate metadata with the same key. It also
 * allows removing all metadata matching key, if needed.
 *
 * @param int    $donor_id   Donor ID
 * @param string $meta_key   Metadata name.
 * @param mixed  $meta_value Optional. Metadata value.
 *
 * @return bool True on success, false on failure.
 * @since 1.8.13
 */
function delete_donor_meta( $donor_id, $meta_key, $meta_value = '' ) {
	return delete_metadata( 'give_customer', $donor_id, $meta_key, $meta_value );
}

/**
 * Retrieve donor meta field for a donor meta table.
 *
 * @param int    $donor_id Donor ID.
 * @param string $key      Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool   $single   Whether to return a single value.
 *
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 * @since 1.8.13
 */
function get_donor_meta( $donor_id, $key = '', $single = false ) {
	return get_metadata( 'give_customer', $donor_id, $key, $single );
}

/**
 * Update customer meta field based on Donor ID.
 *
 * If the meta field for the donor does not exist, it will be added.
 *
 * @param int    $donor_id   Donor ID.
 * @param string $meta_key   Metadata key.
 * @param mixed  $meta_value Metadata value.
 * @param mixed  $prev_value Optional. Previous value to check before removing.
 *
 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
 * @since 1.8.13
 */
function update_donor_meta( $donor_id, $meta_key, $meta_value, $prev_value = '' ) {
	return update_metadata( 'give_customer', $donor_id, $meta_key, $meta_value, $prev_value );
}


/**
 * Give recalculate income and donation of the donation from ID
 *
 * @param int $form_id Form id of which recalculation needs to be done.
 *
 * @return void
 * @since 1.8.13
 */
function give_recount_form_income_donation( $form_id = 0 ) {
	// Check if form id is not empty.
	if ( ! empty( $form_id ) ) {
		/**
		 * Filter to modify payment status.
		 *
		 * @since 1.8.13
		 */
		$accepted_statuses = apply_filters( 'give_recount_accepted_statuses', [ 'publish' ] );

		/**
		 * Filter to modify args of payment query before recalculating the form total
		 *
		 * @since 1.8.13
		 */
		$args = apply_filters(
			'give_recount_form_stats_args',
			[
				'give_forms' => $form_id,
				'status'     => $accepted_statuses,
				'number'     => - 1,
				'fields'     => 'ids',
			]
		);

		$totals = [
			'sales'    => 0,
			'earnings' => 0,
		];

		$payments = new Give_Payments_Query( $args );
		$payments = $payments->get_payments();

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				// Ensure acceptable status only.
				if ( ! in_array( $payment->post_status, $accepted_statuses ) ) {
					continue;
				}

				// Ensure only payments for this form are counted.
				if ( $payment->form_id != $form_id ) {
					continue;
				}

				$totals['sales'] ++;
				$totals['earnings'] += $payment->total;

			}
		}
		give_update_meta( $form_id, '_give_form_sales', $totals['sales'] );
		give_update_meta( $form_id, '_give_form_earnings', give_sanitize_amount_for_db( $totals['earnings'] ) );
	}// End if().
}


/**
 * Get attribute string
 *
 * @param array $attributes
 * @param array $default_attributes
 *
 * @return string
 * @since 1.8.17
 */
function give_get_attribute_str( $attributes, $default_attributes = [] ) {
	$attribute_str = '';

	if ( isset( $attributes['attributes'] ) ) {
		$attributes = $attributes['attributes'];
	}

	if ( ! empty( $default_attributes ) ) {
		$attributes = wp_parse_args( $attributes, $default_attributes );
	}

	if ( empty( $attributes ) ) {
		return $attribute_str;
	}

	foreach ( $attributes as $tag => $value ) {
		if ( 'value' == $tag ) {
			$value = esc_attr( $value );
		}

		$attribute_str .= " {$tag}=\"{$value}\"";
	}

	return trim( $attribute_str );
}

/**
 * Get the upload dir path
 *
 * @return string $wp_upload_dir;
 * @since 1.8.17
 */
function give_get_wp_upload_dir() {
	$wp_upload_dir = wp_upload_dir();

	return ( ! empty( $wp_upload_dir['path'] ) ? $wp_upload_dir['path'] : false );
}

/**
 * Get the data from uploaded JSON file
 *
 * @param string $file_name filename of the json file that is being uploaded
 *
 * @return string|bool $file_contents File content
 * @since 1.8.17
 */
function give_get_core_settings_json( $file_name ) {
	$upload_dir = give_get_wp_upload_dir();
	$file_path  = $upload_dir . '/' . $file_name;

	if ( is_wp_error( $file_path ) || empty( $file_path ) ) {
		Give_Admin_Settings::add_error( 'give-import-csv', __( 'Please upload or provide a valid JSON file.', 'give' ) );
	}

	$file_contents = file_get_contents( $file_path );

	return $file_contents;
}

/**
 * Get number of donation to show when user is not login.
 *
 * @return int $country The two letter country code for the site's base country
 * @since 1.8.17
 */
function give_get_limit_display_donations() {
	return give_get_option( 'limit_display_donations', 1 );
}

/**
 * Add footer to the table when donor is view the donation history page with out login
 *
 * @since 1.8.17
 */
function give_donation_history_table_end() {
	$email = Give()->session->get( 'give_email' );
	?>
	<tfoot>
		<tr>
			<td colspan="9999">
				<div class="give-security-wrap">
					<div class="give-security-column give-security-description-wrap">
						<?php
						echo sprintf( __( 'For security reasons, please confirm your email address (%s) to view your complete donation history.', 'give' ), $email );
						?>
					</div>
					<div class="give-security-column give-security-button-wrap">
						<a href="#" data-email="<?php echo $email; ?>" id="give-confirm-email-btn"
						   class="give-confirm-email-btn give-btn">
							<?php _e( 'Confirm Email', 'give' ); ?>
						</a>
						<span><?php _e( 'Email Sent!', 'give' ); ?></span>
					</div>
				</div>
			</td>
		</tr>
	</tfoot>
	<?php
}


/**
 * Wrapper for _doing_it_wrong.
 *
 * @param string $function
 * @param string $message
 * @param string $version deprecated
 *
 * @return void
 * @since  1.8.18
 * @since  2.5.13 Refactor function
 */
function give_doing_it_wrong( $function, $message, $version = null ) {
	/**
	 * Fires while calling function incorrectly.
	 *
	 * Allow you to hook to incorrect function call.
	 *
	 * @param string $function    The function that was called.
	 * @param string $replacement Optional. The function that should have been called.
	 * @param string $version     The plugin version that deprecated the function.
	 *
	 * @since 2.5.13
	 */
	do_action( 'give_doing_it_wrong', $function, $message, $version );

	$show_errors = current_user_can( 'manage_options' );

	// Allow plugin to filter the output error trigger.
	if ( WP_DEBUG && apply_filters( 'give_doing_it_wrong_trigger_error', $show_errors ) ) {
		trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s', 'give' ), $function, $message ) );
		trigger_error( print_r( wp_debug_backtrace_summary(), 1 ) );// Limited to previous 1028 characters, but since we only need to move back 1 in stack that should be fine.
	}
}


/**
 * Remove limit from running php script complete.
 *
 * @since 1.8.18
 */
function give_ignore_user_abort() {
	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		set_time_limit( 0 );
	}
}

/**
 * Get post type count.
 *
 * @param string $post_type
 * @param array  $args
 *
 * @return int
 * @since 2.0.2
 */
function give_get_total_post_type_count( $post_type = '', $args = [] ) {
	global $wpdb;
	$where = '';

	if ( ! $post_type ) {
		return 0;
	}

	// Bulit where query
	if ( ! empty( $post_type ) ) {
		$where .= ' WHERE';

		if ( is_array( $post_type ) ) {
			$where .= " post_type='" . implode( "' OR post_type='", $post_type ) . "'";
		} else {
			$where .= " post_type='{$post_type}'";
		}
	}

	$result = $wpdb->get_var( "SELECT count(ID) FROM {$wpdb->posts}{$where}" );

	return absint( $result );
}

/**
 * Define a constant if it is not already defined.
 *
 * @param string $name  Constant name.
 * @param string $value Value.
 *
 * @credit WooCommerce
 * @since  2.0.5
 */
function give_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
}

/**
 * Decode time short tag in string
 *
 * @param string $string
 * @param int    $timestamp
 *
 * @return string
 * @since 2.1.0
 */
function give_time_do_tags( $string, $timestamp = 0 ) {
	$current_time = ! empty( $timestamp ) ? $timestamp : current_time( 'timestamp' );

	$formatted_string = str_replace(
		[
			'{D}',
			'{DD}',
			'{M}',
			'{MM}',
			'{YY}',
			'{YYYY}',
			'{H}',
			'{HH}',
			'{N}',
			'{S}',
		],
		[
			date( 'j', $current_time ),
			date( 'd', $current_time ),
			date( 'n', $current_time ),
			date( 'm', $current_time ),
			date( 'Y', $current_time ),
			date( 'Y', $current_time ),
			date( 'G', $current_time ),
			date( 'H', $current_time ),
			date( 's', $current_time ),
		],
		$string
	);

	/**
	 * Filter the parsed string.
	 *
	 * @since 2.1.0
	 */
	return apply_filters( 'give_time_do_tags', $formatted_string, $string, $timestamp );
}


/**
 * Check if Company field enabled or not for form or globally.
 *
 * @param $form_id
 *
 * @return bool
 * @since 2.1
 */
function give_is_company_field_enabled( $form_id ) {
	$form_setting_val   = give_get_meta( $form_id, '_give_company_field', true );
	$global_setting_val = give_get_option( 'company_field' );

	if ( ! empty( $form_setting_val ) ) {
		if ( give_is_setting_enabled( $form_setting_val, [ 'required', 'optional' ] ) ) {
			return true;
		} elseif ( 'global' === $form_setting_val && give_is_setting_enabled(
			$global_setting_val,
			[
				'required',
				'optional',
			]
		) ) {
			return true;
		} else {
			return false;
		}
	} elseif ( give_is_setting_enabled( $global_setting_val, [ 'required', 'optional' ] ) ) {
		return true;

	} else {
		return false;
	}
}

/**
 * Check if Last Name field is required
 *
 * @param $form_id
 *
 * @return bool
 * @since 2.15.0
 */
function give_is_last_name_required( $form_id ) {
	$form_setting_val   = give_get_meta( $form_id, '_give_last_name_field_required', true );
	$global_setting_val = give_get_option( 'last_name_field_required' );

	if ( ! empty( $form_setting_val ) ) {
		if ( 'required' === $form_setting_val ) {
			return true;
		}

		return 'global' === $form_setting_val && 'required' === $global_setting_val;
	}

	return 'required' === $global_setting_val;
}

/**
 * Check if anonymous donation field enabled or not for form or globally.
 *
 * @param $form_id
 *
 * @return bool
 * @since 2.1
 */
function give_is_anonymous_donation_field_enabled( $form_id ) {
	$form_setting_val   = give_get_meta( $form_id, '_give_anonymous_donation', true, 'global' );
	$global_setting_val = give_get_option( 'anonymous_donation', 'disabled' );

	if ( ! empty( $form_setting_val ) ) {
		if ( give_is_setting_enabled( $form_setting_val ) ) {
			return true;
		} elseif ( 'global' === $form_setting_val && give_is_setting_enabled( $global_setting_val ) ) {
			return true;
		} else {
			return false;
		}
	} elseif ( give_is_setting_enabled( $global_setting_val ) ) {
		return true;
	}

	return false;
}

/**
 * Check if donor comment field enabled or not for form or globally.
 *
 * @param $form_id
 *
 * @return bool
 * @since 2.1
 */
function give_is_donor_comment_field_enabled( $form_id ) {
	$form_setting_val   = give_get_meta( $form_id, '_give_donor_comment', true, 'global' );
	$global_setting_val = give_get_option( 'donor_comment', 'disabled' );

	if ( ! empty( $form_setting_val ) ) {
		if ( give_is_setting_enabled( $form_setting_val ) ) {
			return true;
		} elseif ( 'global' === $form_setting_val && give_is_setting_enabled( $global_setting_val ) ) {
			return true;
		} else {
			return false;
		}
	} elseif ( give_is_setting_enabled( $global_setting_val ) ) {
		return true;
	}

	return false;

}

/**
 * Get add-on user meta value information
 * Note: only for internal use.
 *
 * @param string $banner_addon_name Give add-on name.
 *
 * @return array
 * @since 2.1.0
 */
function __give_get_active_by_user_meta( $banner_addon_name ) {
	global $wpdb;

	// Get the option key.
	$option_name = Give_Addon_Activation_Banner::get_banner_user_meta_key( $banner_addon_name );
	$data        = [];

	if ( empty( $GLOBALS['give_addon_activated_by_user'] ) ) {
		$GLOBALS['give_addon_activated_by_user'] = [];

		// Get the meta of activation banner by user.
		$activation_banners = $wpdb->get_results(
			"
					SELECT option_name, option_value
					FROM {$wpdb->options}
					WHERE option_name LIKE '%_active_by_user%'
					AND option_name LIKE '%give_addon%'
					",
			ARRAY_A
		);

		if ( ! empty( $activation_banners ) ) {
			$GLOBALS['give_addon_activated_by_user'] = array_combine(
				wp_list_pluck( $activation_banners, 'option_name' ),
				wp_list_pluck( $activation_banners, 'option_value' )
			);
		}
	}

	if ( array_key_exists( $option_name, $GLOBALS['give_addon_activated_by_user'] ) ) {
		$data = maybe_unserialize( $GLOBALS['give_addon_activated_by_user'][ $option_name ] );
	}

	return $data;
}

/**
 * Get time interval for which nonce is valid
 *
 * @return int
 * @since 2.1.3
 */
function give_get_nonce_life() {
	/**
	 * Filters the lifespan of nonces in seconds.
	 *
	 * @see wp-inlucdes/pluggable.php:wp_nonce_tick
	 */
	return (int) apply_filters( 'nonce_life', DAY_IN_SECONDS );
}

/**
 * Get nonce field without id
 *
 * @param string $action
 * @param string $name
 * @param bool   $referer
 *
 * @return string
 * @since 2.1.3
 */
function give_get_nonce_field( $action, $name, $referer = false ) {
	return str_replace(
		"id=\"{$name}\"",
		'',
		wp_nonce_field( $action, $name, $referer, false )
	);
}

/**
 * Display/Return a formatted goal for a donation form
 *
 * @param int|Give_Donate_Form $form Form ID or Form Object.
 *
 * @return array
 * @since 2.1
 */
function give_goal_progress_stats( $form ) {

	if ( ! $form instanceof Give_Donate_Form ) {
		$form = new Give_Donate_Form( $form );
	}

	$goal_format = give_get_form_goal_format( $form->ID );

	/**
	 * Filter the form.
	 *
	 * @since 1.8.8
	 */
	$total_goal = apply_filters( 'give_goal_amount_target_output', round( give_maybe_sanitize_amount( $form->goal ), 2 ), $form->ID, $form );

	switch ( $goal_format ) {
		case 'donation':
			/**
			 * Filter the form donations.
			 *
			 * @since 2.1
			 */
			$actual = apply_filters( 'give_goal_donations_raised_output', $form->sales, $form->ID, $form );
			break;
		case 'donors':
			/**
			 * Filter to modify total number if donor for the donation form.
			 *
			 * @param int              $donors  Total number of donors that donated to the form.
			 * @param int              $form_id Donation Form ID.
			 * @param Give_Donate_Form $form    instances of Give_Donate_Form.
			 *
			 * @return int $donors Total number of donors that donated to the form.
			 * @since 2.1.3
			 */
			$actual = apply_filters( 'give_goal_donors_target_output', give_get_form_donor_count( $form->ID ), $form->ID, $form );
			break;
		default:
			/**
			 * Filter the form income.
			 *
			 * @since 1.8.8
			 */
			$actual = apply_filters( 'give_goal_amount_raised_output', $form->earnings, $form->ID, $form );
			break;
	}

	$progress = $total_goal ? round( ( $actual / $total_goal ) * 100, 2 ) : 0;

	$stats_array = [
		'raw_actual' => $actual,
		'raw_goal'   => $total_goal,
	];

	/**
	 * Filter the goal progress output
	 *
	 * @since 1.8.8
	 */
	$progress = apply_filters( 'give_goal_amount_funded_percentage_output', $progress, $form->ID, $form );

	// Define Actual Goal based on the goal format.
	switch ( $goal_format ) {
		case 'percentage':
			$actual     = "{$progress}%";
			$total_goal = '';
			break;

		case 'amount' === $goal_format:
			$actual     = give_currency_filter( give_format_amount( $actual ) );
			$total_goal = give_currency_filter( give_format_amount( $total_goal ) );
			break;

		default:
			$actual     = give_format_amount( $actual, [ 'decimal' => false ] );
			$total_goal = give_format_amount( $total_goal, [ 'decimal' => false ] );
			break;
	}

	$stats_array = array_merge(
		[
			'progress' => $progress,
			'actual'   => $actual,
			'goal'     => $total_goal,
			'format'   => $goal_format,
		],
		$stats_array
	);

	/**
	 * Filter the goal stats
	 *
	 * @since 2.1
	 */
	return apply_filters( 'give_goal_progress_stats', $stats_array );
}

/**
 * Get the admin messages key to show the notices.
 *
 * @return array $message admin message key.
 * @since 2.1.4
 */
function give_get_admin_messages_key() {
	$messages = empty( $_GET['give-messages'] ) ? [] : give_clean( $_GET['give-messages'] );

	// backward compatibility.
	if ( ! empty( $_GET['give-message'] ) ) {
		$messages[] = give_clean( $_GET['give-message'] );
	}

	/**
	 * Filter to modify the admin messages key.
	 *
	 * @param array $message admin message key.
	 *
	 * @return array $message admin message key.
	 * @since 2.1.4
	 */
	return (array) apply_filters( 'give_get_admin_messages_key', $messages );
}

/**
 * Get User Agent String.
 *
 * @return array|string
 * @since 2.1.4
 */
function give_get_user_agent() {

	// Get User Agent.
	$user_agent = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? give_clean( $_SERVER['HTTP_USER_AGENT'] ) : ''; // WPCS: input var ok.

	return $user_agent;
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @param string  $name   Name of the cookie being set.
 * @param string  $value  Value of the cookie.
 * @param integer $expire Expiry of the cookie.
 * @param bool    $secure Whether the cookie should be served only over https.
 *
 * @since 2.2.0
 */
function give_setcookie( $name, $value, $expire = 0, $secure = false ) {
	if ( ! headers_sent() ) {
		setcookie(
			$name,
			$value,
			$expire,
			COOKIEPATH ? COOKIEPATH : '/',
			COOKIE_DOMAIN,
			$secure,
			apply_filters( 'give_cookie_httponly', false, $name, $value, $expire, $secure )
		);
	}
}

/**
 * Get formatted billing address.
 *
 * @param array $address
 *
 * @return string Formatted address.
 * @since 2.2.0
 */
function give_get_formatted_address( $address = [] ) {
	$formatted_address = '';

	/**
	 * Address format.
	 *
	 * @since 2.2.0
	 */
	$address_format = apply_filters( 'give_address_format_template', "{street_address}\n{city}, {state} {postal_code}\n{country}" );
	preg_match_all( '/{([A-z0-9\-\_\ ]+)}/s', $address_format, $matches );

	if ( ! empty( $matches ) && ! empty( $address ) ) {
		$address_values = [];

		foreach ( $matches[1] as $address_tag ) {
			$address_values[ $address_tag ] = '';

			if ( isset( $address[ $address_tag ] ) ) {
				$address_values[ $address_tag ] = $address[ $address_tag ];
			}
		}

		$formatted_address = str_ireplace( $matches[0], $address_values, $address_format );
	}

	/**
	 * Give get formatted address.
	 *
	 * @param string $formatted_address Formatted address.
	 * @param string $address_format    Format of the address.
	 *
	 * @since 2.2.0
	 */
	$formatted_address = apply_filters( 'give_get_formatted_address', $formatted_address, $address_format, $address );

	return $formatted_address;
}

/**
 * Get safe url for assets
 * Note: this function will return url without http protocol
 *
 * @param string $url URL
 *
 * @return string
 * @since 2.2.0
 */
function give_get_safe_asset_url( $url ) {

	// Bailout, if empty URL passed.
	if ( empty( $url ) ) {
		return $url;
	}

	$schema        = parse_url( $url, PHP_URL_SCHEME );
	$schema_length = strlen( $schema ) + 1;
	$url           = substr( $url, $schema_length );

	/**
	 * Fire the filter
	 *
	 * @since 2.2.0
	 */
	return apply_filters( 'give_get_safe_asset_url', $url );
}

/**
 * Give get formatted date.
 * Note: This function does not work well with localize translated  date strings
 *
 * @param string $date           Date.
 * @param string $format         Date Format.
 * @param string $current_format Current date Format.
 * @param bool   $localize
 *
 * @return string
 * @since 2.3.0
 */
function give_get_formatted_date( $date, $format = 'Y-m-d', $current_format = '', $localize = false ) {
	$current_format = empty( $current_format ) ? give_date_format() : $current_format;
	$date_obj       = DateTime::createFromFormat( $current_format, $date );
	$formatted_date = '';

	if ( $date_obj instanceof DateTime ) {
		$formatted_date = $localize ?
			date_i18n( $format, $date_obj->getTimestamp() ) :
			$date_obj->format( $format );
	}

	/**
	 * Give get formatted date.
	 *
	 * @param string $formatted_date Formatted date.
	 * @param array
	 *
	 * @since 2.3.0
	 */
	return apply_filters( 'give_get_formatted_date', $formatted_date, [ $date, $format, $current_format ] );
}

/**
 * This function will be used to fetch the donation receipt link.
 *
 * @param int $donation_id Donation ID.
 *
 * @return string
 * @since 2.3.1
 */
function give_get_receipt_link( $donation_id ) {

	return sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( give_get_receipt_url( $donation_id ) ),
		esc_html__( 'View the receipt in your browser &raquo;', 'give' )
	);

}

/**
 * Get receipt_url
 *
 * @param int $donation_id Donation ID.
 *
 * @return string
 * @since 2.0
 */
function give_get_receipt_url( $donation_id ) {

	$receipt_url = esc_url_raw(
		add_query_arg(
			[
				'donation_id' => $donation_id,
			],
			give_get_history_page_uri()
		)
	);

	return $receipt_url;
}

/**
 * Get "View in browser" Receipt Link for email.
 *
 * @param int $donation_id Donation ID.
 *
 * @return string
 * @since 2.4.1
 */
function give_get_view_receipt_link( $donation_id ) {

	return sprintf(
		'<a href="%1$s">%2$s</a>',
		give_get_view_receipt_url( $donation_id ),
		esc_html__( 'View the receipt in your browser &raquo;', 'give' )
	);

}

/**
 * Get "View in browser" Receipt URL for email.
 *
 * @param int $donation_id Donation ID.
 *
 * @return string
 * @since 2.4.1
 */
function give_get_view_receipt_url( $donation_id ) {

	$receipt_url = esc_url_raw(
		add_query_arg(
			[
				'action'     => 'view_in_browser',
				'_give_hash' => give_get_payment_key( $donation_id ),
			],
			give_get_history_page_uri()
		)
	);

	return $receipt_url;
}

/**
 * This function is used to display donation receipt content based on the parameters.
 *
 * @param $args
 *
 * @return bool|mixed
 * @since 2.4.1
 */
function give_display_donation_receipt( $args ) {

	global $give_receipt_args;

	$give_receipt_args = $args;

	ob_start();

	$get_data     = give_clean( filter_input_array( INPUT_GET ) );
	$donation_id  = ! empty( $get_data['donation_id'] ) ? $get_data['donation_id'] : false;
	$receipt_type = ! empty( $get_data['receipt_type'] ) ? $get_data['receipt_type'] : false;

	$give_receipt_args['id'] = $donation_id;

	if ( 'view_in_browser' !== $receipt_type ) {

		$email_access    = give_get_option( 'email_access' );
		$is_email_access = give_is_setting_enabled( $email_access ) && ! Give()->email_access->token_exists;

		// No donation id found & Email Access is Turned on.
		if ( ! $donation_id ) {

			if ( $is_email_access ) {
				give_get_template_part( 'email-login-form' );
			} else {
				echo Give_Notices::print_frontend_notice( $args['error'], false, 'error' );
			}

			return ob_get_clean();
		}

		// Donation id provided, but user is logged out. Offer them the ability to login and view the receipt.
		if ( ! ( $user_can_view = give_can_view_receipt( $donation_id ) ) ) {

			if ( true === Give()->session->get( 'donor_donation_mismatch' ) ) {

				/**
				 * This filter will be used to modify the donor mismatch text for front end error notice.
				 *
				 * @since 2.3.1
				 */
				$donor_mismatch_text = apply_filters( 'give_receipt_donor_mismatch_notice_text', __( 'You are trying to access invalid donation receipt. Please try again.', 'give' ) );

				echo Give_Notices::print_frontend_notice(
					$donor_mismatch_text,
					false,
					'error'
				);

			} elseif ( $is_email_access ) {

				give_get_template_part( 'email-login-form' );

			} else {

				global $give_login_redirect;

				$give_login_redirect = give_get_current_page_url();

				Give_Notices::print_frontend_notice(
					apply_filters(
						'give_must_be_logged_in_error_message',
						__( 'You must be logged in to view this donation receipt.', 'give' )
					)
				);

				give_get_template_part( 'shortcode', 'login' );
			}

			return ob_get_clean();
		}

		/**
		 * Check if the user has permission to view the receipt.
		 *
		 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
		 * or if user is logged out and donation was made as a guest, the donation session is checked for
		 * or if user is logged in and the user can view sensitive shop data.
		 */
		if ( ! apply_filters( 'give_user_can_view_receipt', $user_can_view, $args ) ) {
			return Give_Notices::print_frontend_notice( $args['error'], false, 'error' );
		}
	} else {
		$donation_id             = give_get_donation_id_by_key( $get_data['donation_id'] );
		$give_receipt_args['id'] = $donation_id;
	}

	give_get_template_part( 'shortcode', 'receipt' );

	return ob_get_clean();
}


/**
 * Get plugin add-on readme.txt path
 * Note: only for internal use
 *
 * @param      $plugin_slug
 * @param bool        $by_plugin_name
 *
 * @return mixed|void
 * @since 2.5.0
 */
function give_get_addon_readme_url( $plugin_slug, $by_plugin_name = false ) {

	if ( $by_plugin_name ) {
		$plugin_slug = Give_License::get_short_name( $plugin_slug );
	}

	$website_url = trailingslashit( Give_License::get_website_url() );

	/**
	 * Filter the addon readme.txt url
	 *
	 * @since 2.1.4
	 */
	$url = apply_filters(
		'give_addon_readme_file_url',
		"{$website_url}downloads/plugins/{$plugin_slug}/readme.txt",
		$plugin_slug,
		$by_plugin_name
	);

	return $url;
}

/**
 * Refresh all givewp license.
 *
 * @since 2.27.0 delete update_plugins transient instead of invalidate it
 * @since  2.5.0
 *
 * @param  bool  $wp_check_updates
 *
 * @access public
 * @return array|WP_Error
 */
function give_refresh_licenses( $wp_check_updates = true ) {
	$give_licenses = get_option( 'give_licenses', [] );
	$give_addons   = give_get_plugins( [ 'only_premium_add_ons' => true ] );

	if ( ! $give_licenses && ! $give_addons ) {
		return [];
	}

	$license_keys = $give_licenses ? implode( ',', array_keys( $give_licenses ) ) : '';

	$unlicensed_give_addon = $give_addons
		? array_values(
			array_diff(
				array_map(
					function ( $plugin_name ) {
						return trim( str_replace( 'Give - ', '', $plugin_name ) );
					},
					wp_list_pluck( $give_addons, 'Name', true )
				),
				wp_list_pluck( $give_licenses, 'item_name', true )
			)
		)
		: [];

	$tmp = Give_License::request_license_api(
		[
			'edd_action' => 'check_licenses',
			'licenses'   => $license_keys,
			'unlicensed' => implode( ',', $unlicensed_give_addon ),
		],
		true
	);

	if ( ! $tmp || is_wp_error( $tmp ) ) {
		return [];
	}

	// Prevent fatal error on WP 4.9.10
	// Because wp_list_pluck accept only array or array of array in that version.
	// @see https://github.com/impress-org/give/issues/4176
	$tmp = json_decode( json_encode( $tmp ), true );

	// Remove unlicensed add-on from response.
	$tmp_unlicensed = [];
	foreach ( $tmp as $key => $data ) {
		if ( empty( $data ) ) {
			unset( $tmp[ "{$key}" ] );
			continue;
		}

		if ( empty( $data['check_license'] ) ) {
			$tmp_unlicensed[ $key ] = $data;
			unset( $tmp[ "{$key}" ] );
		}
	}

	$check_licenses = wp_list_pluck( $tmp, 'check_license' );

	/* @var stdClass $data */
	foreach ( $check_licenses as $key => $data ) {
		if ( is_wp_error( $data ) ) {
			continue;
		}

		if ( ! $data['success'] ) {
			unset( $give_licenses[ $key ] );
			continue;
		}

		$give_licenses[ $key ] = $data;
	}

	$tmp_update_plugins = array_merge(
		array_filter( wp_list_pluck( $tmp, 'get_version' ) ),
		array_filter( wp_list_pluck( $tmp, 'get_versions' ) )
	);

	if ( $tmp_unlicensed ) {
		$tmp_update_plugins = array_merge( $tmp_update_plugins, $tmp_unlicensed );
	}

	update_option( 'give_licenses', $give_licenses, 'no' );
	update_option( 'give_get_versions', $tmp_update_plugins, 'no' );

	$refresh         = Give_License::refresh_license_status();
	$refresh['time'] = time();

	update_option( 'give_licenses_refreshed_last_checked', $refresh, 'no' );

	// Tell WordPress to look for updates.
	if ( $wp_check_updates ) {
		delete_site_transient('update_plugins');
	}

	return [
		'give_licenses'     => $give_licenses,
		'give_get_versions' => $tmp_update_plugins,
	];
}

/**
 * Check add-ons updates
 * Note: only for internal use
 *
 * @param stdClass $_transient_data Plugin updates information
 *
 * @return stdClass
 * @since 2.5.0
 */
function give_check_addon_updates( $_transient_data ) {
	if ( ! is_object( $_transient_data ) ) {
		$_transient_data = new stdClass();
	}

	$update_plugins = get_option( 'give_get_versions', [] );
	$check_licenses = get_option( 'give_licenses', [] );

	if ( ! $update_plugins ) {
		$data = give_refresh_licenses( false );

		if (
			empty( $data['give_get_versions'] )
			|| is_wp_error( $data )
		) {
			return $_transient_data;
		}

		$update_plugins = $data['give_get_versions'];
	}

	foreach ( $update_plugins as $key => $data ) {
		$plugins = ! empty( $check_licenses[ $key ] )
			? ( ! empty( $check_licenses[ $key ]['is_all_access_pass'] ) ? $data : [ $data ] )
			: [ $data ];

		foreach ( $plugins as $plugin ) {
			// This value will be empty if any error occurred when verifying version of add-on.
			if ( empty( $plugin['new_version'] ) ) {
				continue;
			}

			$plugin     = array_map( 'maybe_unserialize', $plugin );
			$tmp_plugin = Give_License::get_plugin_by_slug( $plugin['slug'] );

			if ( ! $tmp_plugin ) {
				continue;
			}

			$plugin['plugin'] = $tmp_plugin['Path'];

			if ( - 1 !== version_compare( $tmp_plugin['Version'], $plugin['new_version'] ) ) {
				$_transient_data->no_update[ $tmp_plugin['Path'] ] = (object) $plugin;
			} else {
				$_transient_data->response[ $tmp_plugin['Path'] ] = (object) $plugin;
			}

			$_transient_data->checked[ $tmp_plugin['Path'] ] = $tmp_plugin['Version'];
		}
	}

	$_transient_data->last_checked = time();

	return $_transient_data;
}

/**
 * Get page by title
 *
 * @since 2.26.0
 *
 * @param string $page_title
 * @param string $output
 * @param string $post_type
 *
 * @return null|WP_Post
 */
function give_get_page_by_title(string $page_title, string $output = OBJECT, string $post_type = 'page')
{
    $args = [
        'title' => $page_title,
        'post_type' => $post_type,
        'post_status' => get_post_stati(),
        'posts_per_page' => 1,
        'update_post_term_cache' => false,
        'update_post_meta_cache' => false,
        'no_found_rows' => true,
        'orderby' => 'post_date ID',
        'order' => 'ASC',
    ];
    $query = new WP_Query($args);
    $pages = $query->posts;

    if (empty($pages)) {
        return null;
    }

    return get_post($pages[0], $output);
}
