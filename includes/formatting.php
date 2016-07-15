<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     Give
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get thousand separator
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_thousand_separator() {
    return give_get_option( 'thousands_separator', ',' );
}

/**
 * Get decimal separator
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_decimal_separator() {
    return give_get_option( 'decimal_separator', '.' );
}

/**
 * Sanitize Amount
 *
 * @description: Returns a sanitized amount by stripping out thousands separators.
 *
 * @since      1.0
 *
 * @param string $amount Price amount to format
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount( $amount ) {
	$is_negative   = false;
	$thousands_sep = give_get_option( 'thousands_separator', ',' );
	$decimal_sep   = give_get_option( 'decimal_separator', '.' );

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( ( $thousands_sep == '.' || $thousands_sep == ' ' ) && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif ( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	if ( $amount < 0 ) {
		$is_negative = true;
	}

	$amount   = preg_replace( '/[^0-9\.]/', '', $amount );
	$decimals = apply_filters( 'give_sanitize_amount_decimals', 2, $amount );
	$amount   = number_format( (double) $amount, $decimals, '.', '' );

	if ( $is_negative ) {
		$amount *= - 1;
	}

	return apply_filters( 'give_sanitize_amount', $amount );
}

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.0
 *
 * @param string      $amount   Price amount to format
 * @param bool|string $decimals Whether or not to use decimals. Useful when set to false for non-currency numbers.
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function give_format_amount( $amount, $decimals = true ) {

	$thousands_sep = give_get_option( 'thousands_separator', ',' );
	$decimal_sep   = give_get_option( 'decimal_separator', '.' );

	// Format the amount
	if ( $decimal_sep == ',' && false !== ( $sep_found = strpos( $amount, $decimal_sep ) ) ) {
		$whole  = substr( $amount, 0, $sep_found );
		$part   = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator)
	if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ',', '', $amount );
	}

	// Strip . from the amount (if set as the thousands separator) AND , set to decimal separator
	if ( $thousands_sep == '.' && $decimal_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount      = explode( '.', $amount );
		$array_count = count( $amount );
		if ( $decimals == true ) {
			unset( $amount[ $array_count - 1 ] );
		}
		$amount = implode( '', $amount );
	}

	// Strip ' ' from the amount (if set as the thousands separator)
	if ( $thousands_sep == ' ' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ' ', '', $amount );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}

	$decimals = apply_filters( 'give_format_amount_decimals', $decimals ? 2 : 0, $amount );

	$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'give_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
}


/**
 * Format decimal numbers ready for DB storage.
 *
 * Sanitize, remove locale formatting, and optionally round + trim off zeros.
 *
 * @param  float|string $number     Expects either a float or a string with a decimal separator only (no thousands)
 * @param  bool         $trim_zeros From end of string
 *
 * @return string
 */
function give_format_decimal( $number, $trim_zeros = false ) {
    $thousand_separator = give_get_price_thousand_separator();

    // Remove thousand amount formatting if amount has.
    // This condition use to add backward compatibility to version before 1.6, because before version 1.6 we were saving formatted amount to db.
    if( false !== strpos( $number, $thousand_separator ) ) {
        $number = str_replace( $thousand_separator, '', $number );
    }

    $locale   = localeconv();
    $decimals = array( give_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

    // Remove locale from string
    if ( ! is_float( $number ) ) {
        $number = str_replace( $decimals, '.', $number );
    }

    $decimals = apply_filters( 'give_format_amount_decimals', $decimals ? 2 : 0, $number );
    $number = number_format( floatval( $number ), $decimals, '.', '' );

    return $number;
}

/**
 * Format Multi-level Amount
 *
 * @description Loops through CMB2 repeater field and updates amount field using give_format_amount()
 *
 * @param $field_args
 * @param $field
 *
 * @return bool
 */
function give_format_admin_multilevel_amount( $field_args, $field ) {

	if ( empty( $field->value ) ) {
		return false;
	}

	$field->value = give_format_decimal( $field->value );
}

/**
 * Formats the currency display
 *
 * @since 1.0
 *
 * @param string $price
 * @param string $currency
 *
 * @return mixed|string|void
 */
function give_currency_filter( $price = '', $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	$position = give_get_option( 'currency_position', 'before' );

	$negative = $price < 0;

	if ( $negative ) {
		$price = substr( $price, 1 ); // Remove proceeding "-" -
	}

	$symbol = give_currency_symbol( $currency );

	if ( $position == 'before' ):
		switch ( $currency ):
			case 'GBP' :
			case 'BRL' :
			case 'EUR' :
			case 'USD' :
			case 'AUD' :
			case 'CAD' :
			case 'HKD' :
			case 'MXN' :
			case 'NZD' :
			case 'SGD' :
			case 'JPY' :
			case 'THB' :
			case 'INR' :
			case 'RIAL' :
			case 'TRY' :
			case 'RUB' :
			case 'SEK' :
			case 'PLN' :
			case 'PHP' :
			case 'TWD' :
			case 'MYR' :
			case 'CZK' :
			case 'DKK' :
			case 'HUF' :
			case 'ILS' :
			case 'MAD' :
			case 'KRW' :
			case 'ZAR' :
				$formatted = $symbol . $price;
				break;
			case 'NOK' :
				$formatted = $symbol . ' ' . $price;
				break;
			default :
				$formatted = $currency . ' ' . $price;
				break;
		endswitch;
		$formatted = apply_filters( 'give_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $price );
	else :
		switch ( $currency ) :
			case 'GBP' :
			case 'BRL' :
			case 'EUR' :
			case 'USD' :
			case 'AUD' :
			case 'CAD' :
			case 'HKD' :
			case 'MXN' :
			case 'SGD' :
			case 'JPY' :
			case 'THB' :
			case 'INR' :
			case 'RIAL' :
			case 'TRY' :
			case 'RUB' :
			case 'SEK' :
			case 'PLN' :
			case 'PHP' :
			case 'TWD' :
			case 'CZK' :
			case 'DKK' :
			case 'HUF' :
			case 'MYR' :
			case 'ILS' :
			case 'MAD' :
			case 'KRW' :
			case 'ZAR' :
				$formatted = $price . $symbol;
				break;
			default :
				$formatted = $price . ' ' . $currency;
				break;
		endswitch;
		$formatted = apply_filters( 'give_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $price );
	endif;

	if ( $negative ) {
		// Prepend the mins sign before the currency sign
		$formatted = '-' . $formatted;
	}

	return $formatted;
}

/**
 * Set the number of decimal places per currency
 *
 * @since 1.0
 *
 * @param int $decimals Number of decimal places
 *
 * @return int $decimals
 */
function give_currency_decimal_filter( $decimals = 2 ) {

	$currency = give_get_currency();

	switch ( $currency ) {
		case 'RIAL' :
		case 'JPY' :
		case 'TWD' :
		case 'HUF' :

			$decimals = 0;
			break;
	}

	return apply_filters( 'give_currency_decimal_count', $decimals, $currency );
}

add_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );
add_filter( 'give_format_amount_decimals', 'give_currency_decimal_filter' );

/**
 * Sanitize thousand separator
 *
 * @since 1.6
 *
 * @param string $value
 * @param array  $field_args
 * @param object $field
 *
 * @return mixed
 */
function give_sanitize_thousand_separator( $value, $field_args, $field ){
    return $value;
}