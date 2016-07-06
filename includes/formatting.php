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
 * Returns a nicely formatted human readable amount.
 * Note: This function only support large number formatting from million to trillion
 *
 * @since 1.5.2
 *
 * @param string $amount formatted amount number.
 * @return float|string  formatted amount number with large number names.
 */
function give_human_format_amount( $amount ) {

    // Get thousand separator.
    $thousands_sep = give_get_option( 'thousands_separator', ',' );

    // Unformatted amount.
    $unformatted_amount = str_replace( $thousands_sep, '', $amount );

    // Explode amount to calculate name of large numbers.
	$amount_array = explode( $thousands_sep, $amount );

    // Calculate amount parts count.
    $amount_count_parts = count( $amount_array );

    // Calculate large number formatted amount.
    if ( 4 < $amount_count_parts ){
        return sprintf( __( '%s trillion', 'give' ), round( ( $unformatted_amount / 1000000000000 ), 2 ) );
    } elseif ( 3 < $amount_count_parts ){
        return sprintf( __( '%s billion', 'give' ), round( ( $unformatted_amount / 1000000000 ), 2 ));
    } elseif ( 2 < $amount_count_parts  ) {
        return sprintf( __( '%s million', 'give' ), round( ( $unformatted_amount / 1000000), 2 ) );
    }

    return $amount;
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

	$field->value = give_format_amount( $field->value );

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