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
 * Get decimal count
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_decimals() {
    return apply_filters( 'give_sanitize_amount_decimals', 2 );
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
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since      1.0
 *
 * @param  float|string $number     Expects either a float or a string with a decimal separator only (no thousands)
 * @param  bool         $trim_zeros From end of string
 *
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount( $number, $trim_zeros = false ) {
    $thousand_separator = give_get_price_thousand_separator();

    $locale   = localeconv();
    $decimals = array( give_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'] );

    // Remove locale from string
    if ( ! is_float( $number ) ) {
        $number = str_replace( $decimals, '.', $number );
    }

    // Remove thousand amount formatting if amount has.
    // This condition use to add backward compatibility to version before 1.6, because before version 1.6 we were saving formatted amount to db.
    // Do not replace thousand separator from price if it is same as decimal separator, because it will be already replace by above code.
    if(  ! in_array( $thousand_separator, $decimals ) && ( false !== strpos( $number, $thousand_separator ) ) ) {
        $number = str_replace( $thousand_separator, '', $number );
    }

    // Remove non numeric entity before decimal separator.
    $number   = preg_replace( '/[^0-9\.]/', '', $number );

    $decimals = give_get_price_decimals();
    $decimals = apply_filters( 'give_sanitize_amount_decimals', $decimals, $number );

    $number = number_format( floatval( $number ), $decimals, '.', '' );

    // Reset negative amount to zero.
	if ( 0 > $number ) {
		$number = number_format( 0, 2, '.' );
	}

    // Trim zeros.
    if ( $trim_zeros && strstr( $number, '.' ) ) {
        $number = rtrim( rtrim( $number, '0' ), '.' );
    }

	return apply_filters( 'give_sanitize_amount', $number );
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

	$decimals = give_get_price_decimals();

	$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'give_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
}


/**
 * Get human readable amount.
 *
 * Note: This function only support large number formatting from million to trillion
 *
 * @since 1.6
 *
 * @use  give_get_price_thousand_separator Get thousand separator.
 *
 * @param string $amount formatted amount number.
 * @return float|string  formatted amount number with large number names.
 */
function give_human_format_large_amount( $amount ) {

    // Get thousand separator.
    $thousands_sep = give_get_price_thousand_separator();

    // Sanitize amount.
    $sanitize_amount = give_sanitize_amount( $amount );

    // Explode amount to calculate name of large numbers.
	$amount_array = explode( $thousands_sep, $amount );

    // Calculate amount parts count.
    $amount_count_parts = count( $amount_array );

    // Calculate large number formatted amount.
    if ( 4 < $amount_count_parts ){
        $sanitize_amount =  sprintf( esc_html__( '%s trillion', 'give' ), round( ( $sanitize_amount / 1000000000000 ), 2 ) );
    } elseif ( 3 < $amount_count_parts ){
        $sanitize_amount =  sprintf( esc_html__( '%s billion', 'give' ), round( ( $sanitize_amount / 1000000000 ), 2 ));
    } elseif ( 2 < $amount_count_parts  ) {
        $sanitize_amount =  sprintf( esc_html__( '%s million', 'give' ), round( ( $sanitize_amount / 1000000), 2 ) );
    } else{
        $sanitize_amount = give_format_amount( $amount );
    }

    return apply_filters( 'give_human_format_large_amount', $sanitize_amount, $amount );
}

/**
 * Returns a nicely formatted amount with custom decimal separator.
 *
 * @since 1.0
 *
 * @param string      $amount   Formatted or sanitized price
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function give_format_decimal( $amount ){
    $decimal_separator = give_get_price_decimal_separator();
    $formatted_amount  = give_sanitize_amount( $amount );

    if( false !== strpos( $formatted_amount, '.' ) ) {
        $formatted_amount = str_replace( '.', $decimal_separator, $formatted_amount );
    }

    return apply_filters( 'give_format_decimal', $formatted_amount, $amount, $decimal_separator );
}


/**
 * Format Multi-level Amount
 *
 * Loops through CMB2 repeater field and updates amount field using give_format_amount()
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