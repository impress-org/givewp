<?php
/**
 * Misc Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is Test Mode
 *
 * @since 1.0
 * @global $give_options
 * @return bool $ret True if return mode is enabled, false otherwise
 */
function give_is_test_mode() {
	global $give_options;

	$ret = ! empty( $give_options['test_mode'] );

	return (bool) apply_filters( 'give_is_test_mode', $ret );
}

/**
 * Get the set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function give_get_currency() {
	global $give_options;
	$currency = isset( $give_options['currency'] ) ? $give_options['currency'] : 'USD';

	return apply_filters( 'give_currency', $currency );
}


/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function give_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'edd' ),
		'EUR'  => __( 'Euros (&euro;)', 'edd' ),
		'GBP'  => __( 'Pounds Sterling (&pound;)', 'edd' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'edd' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'edd' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'edd' ),
		'CZK'  => __( 'Czech Koruna', 'edd' ),
		'DKK'  => __( 'Danish Krone', 'edd' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'edd' ),
		'HUF'  => __( 'Hungarian Forint', 'edd' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'edd' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'edd' ),
		'MYR'  => __( 'Malaysian Ringgits', 'edd' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'edd' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'edd' ),
		'NOK'  => __( 'Norwegian Krone', 'edd' ),
		'PHP'  => __( 'Philippine Pesos', 'edd' ),
		'PLN'  => __( 'Polish Zloty', 'edd' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'edd' ),
		'SEK'  => __( 'Swedish Krona', 'edd' ),
		'CHF'  => __( 'Swiss Franc', 'edd' ),
		'TWD'  => __( 'Taiwan New Dollars', 'edd' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'edd' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'edd' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'edd' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'edd' ),
		'RUB'  => __( 'Russian Rubles', 'edd' )
	);

	return apply_filters( 'give_currencies', $currencies );
}


/**
 * Given a currency determine the symbol to use. If no currency given, site default is used.
 * If no symbol is determine, the currency string is returned.
 *
 * @since  1.0
 *
 * @param  string $currency The currency string
 *
 * @return string           The symbol to use for the currency
 */
function give_currency_symbol( $currency = '' ) {
	global $give_options;

	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	switch ( $currency ) :
		case "GBP" :
			$symbol = '&pound;';
			break;
		case "BRL" :
			$symbol = 'R&#36;';
			break;
		case "EUR" :
			$symbol = '&euro;';
			break;
		case "USD" :
		case "AUD" :
		case "CAD" :
		case "HKD" :
		case "MXN" :
		case "SGD" :
			$symbol = '&#36;';
			break;
		case "JPY" :
			$symbol = '&yen;';
			break;
		default :
			$symbol = $currency;
			break;
	endswitch;

	return apply_filters( 'give_currency_symbol', $symbol, $currency );
}


/**
 * Get the current page URL
 *
 * @since 1.0
 * @global $post
 * @return string $page_url Current page URL
 */
function give_get_current_page_url() {
	global $post;

	if ( is_front_page() ) :
		$page_url = home_url();
	else :
		$page_url = 'http';

		if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
			$page_url .= "s";
		}

		$page_url .= "://";

		if ( isset( $_SERVER["SERVER_PORT"] ) && $_SERVER["SERVER_PORT"] != "80" ) {
			$page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
	endif;

	return apply_filters( 'give_get_current_page_url', esc_url( $page_url ) );
}


/**
 * Verify credit card numbers live?
 *
 * @since 1.0
 * @global $give_options
 * @return bool $ret True is verify credit cards is live
 */
function give_is_cc_verify_enabled() {

	$ret = true;

	/*
	 * Enable if use a single gateway other than PayPal or Manual. We have to assume it accepts credit cards
	 * Enable if using more than one gateway if they aren't both PayPal and manual, again assuming credit card usage
	 */
	$gateways = give_get_enabled_payment_gateways();

	if ( count( $gateways ) == 1 && ! isset( $gateways['paypal'] ) && ! isset( $gateways['manual'] ) ) {
		$ret = true;
	} else if ( count( $gateways ) == 1 ) {
		$ret = false;
	} else if ( count( $gateways ) == 2 && isset( $gateways['paypal'] ) && isset( $gateways['manual'] ) ) {
		$ret = false;
	}

	return (bool) apply_filters( 'give_verify_credit_cards', $ret );
}


/**
 * Checks if users can only give when logged in
 *
 * @since 1.0
 * @global $give_options
 * @return bool $ret Whether or not the logged_in_only setting is set
 */
function give_logged_in_only() {

	global $give_options;

	$ret = ! empty( $give_options['logged_in_only'] );

	return (bool) apply_filters( 'give_logged_in_only', $ret );

}


/**
 * Retrieve timezone
 *
 * @since 1.0
 * @return string $timezone The timezone ID
 */
function give_get_timezone_id() {

	// if site timezone string exists, return it
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// get UTC offset, if it isn't set return UTC
	if ( ! ( $utc_offset = 3600 * get_option( 'gmt_offset', 0 ) ) ) {
		return 'UTC';
	}

	// attempt to guess the timezone string from the UTC offset
	$timezone = timezone_name_from_abbr( '', $utc_offset );

	// last try, guess timezone string manually
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

	// fallback
	return 'UTC';
}


/**
 * Get User IP
 *
 * Returns the IP address of the current visitor
 *
 * @since 1.0
 * @return string $ip User's IP address
 */
function give_get_ip() {

	$ip = '127.0.0.1';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	return apply_filters( 'give_get_ip', $ip );
}


/**
 * Store Purchase Data in Sessions
 *
 * Used for storing info about purchase
 *
 * @since 1.1.5
 *
 * @param $purchase_data
 *
 * @uses  Give()->session->set()
 */
function give_set_purchase_session( $purchase_data = array() ) {
	Give()->session->set( 'give_purchase', $purchase_data );
}

/**
 * Retrieve Purchase Data from Session
 *
 * Used for retrieving info about purchase
 * after completing a purchase
 *
 * @since 1.1.5
 * @uses  Give()->session->get()
 * @return mixed array | false
 */
function give_get_purchase_session() {
	return Give()->session->get( 'give_purchase' );
}

/**
 * Get Purchase Summary
 *
 * Retrieves the purchase summary.
 *
 * @since       1.0
 *
 * @param      $purchase_data
 * @param bool $email
 *
 * @return string
 */
function give_get_purchase_summary( $purchase_data, $email = true ) {
	$summary = '';

	if ( $email ) {
		$summary .= $purchase_data['user_email'] . ' - ';
	}

	$summary .= get_the_title( $purchase_data['post_data']['give-form-id'] );

	return $summary;
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.0
 * @return mixed string $host if detected, false otherwise
 */
function give_get_host() {
	$host = false;

	if ( defined( 'WPE_APIKEY' ) ) {
		$host = 'WP Engine';
	} elseif ( defined( 'PAGELYBIN' ) ) {
		$host = 'Pagely';
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
	} elseif ( strpos( $_SERVER['SERVER_NAME'], 'Flywheel' ) !== false ) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}


/**
 * Check site host
 *
 * @since 1.0
 *
 * @param $host The host to check
 *
 * @return bool true if host matches, false if not
 */
function give_is_host( $host = false ) {

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
		}
	}

	return $return;
}


/**
 * Give Get Admin ID
 *
 * Helper function to return the ID of the post for admin usage
 *
 * @return string $post_id
 */
function give_get_admin_post_id() {
	$post_id = isset( $_GET['post'] ) ? $_GET['post'] : null;
	if ( ! $post_id && isset( $_POST['post_id'] ) ) {
		$post_id = $_POST['post_id'];
	}

	return $post_id;
}


/**
 * Checks if Guest checkout is enabled
 *
 * @since 1.0
 * @global $give_options
 * @return bool $ret True if guest checkout is enabled, false otherwise
 */
function give_no_guest_checkout() {
	global $give_options;

	$ret = ! empty ( $give_options['logged_in_only'] );

	return (bool) apply_filters( 'give_no_guest_checkout', $ret );
}


/**
 * Get PHP Arg Separator Output
 *
 * @since 1.0
 * @return string Arg separator output
 */
function give_get_php_arg_separator_output() {
	return ini_get( 'arg_separator.output' );
}


/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param unknown $n
 * @return string Short month name
 */
function give_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}
