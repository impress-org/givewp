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
	global $give_options;

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