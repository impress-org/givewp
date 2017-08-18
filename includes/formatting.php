<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     Give
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
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
	return apply_filters( 'give_sanitize_amount_decimals', give_get_option( 'number_decimals', 0 ) );
}

/**
 * Get thousand separator
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_thousand_separator() {
	$give_options       = give_get_settings();
	$thousand_separator = isset( $give_options['thousands_separator'] ) ? $give_options['thousands_separator'] : ',';
	$thousand_separator = empty( $thousand_separator ) ? ' ' : $thousand_separator;

	return $thousand_separator;
}

/**
 * Get decimal separator
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_decimal_separator() {
	$default_decimal_separators = array(
		'.' => ',',
		',' => '.',
	);

	$thousand_separator = give_get_price_thousand_separator();
	$default_decimal_separator = in_array( $thousand_separator, $default_decimal_separators ) ?
		$default_decimal_separators[$thousand_separator] :
		'.';

	$decimal_separator = give_get_option( 'decimal_separator', $default_decimal_separator );

	return $decimal_separator;
}


/**
 * Sanitize Amount before saving to database
 *
 * @since      1.8.12
 *
 * @param  int|float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount_for_db( $number ) {
	return give_maybe_sanitize_amount( $number, 6 );
}

/**
 * Sanitize Amount before saving to database
 *
 * @since      1.8.12
 *
 * @param  int|float|string $number     Expects either a float or a string with a decimal separator only (no thousands)
 * @param  int|bool         $dp         Number of decimals
 * @param  bool             $trim_zeros From end of string
 *
 * @return string $amount Newly sanitized amount
 */
function give_maybe_sanitize_amount( $number, $dp = false, $trim_zeros = false ) {
	$thousand_separator = give_get_price_thousand_separator();
	$decimal_separator  = give_get_price_decimal_separator();

	// Bailout.
	if( empty( $number ) || ( ! is_numeric( $number ) && ! is_string( $number ) ) ) {
		return $number;
	}elseif (
		( false == strpos( $number, $thousand_separator ) ) &&
		( false === strpos( $number, $decimal_separator ) )
	) {
		return number_format( $number, ( is_bool( $dp ) ? give_get_price_decimals() : $dp ), '.', '' );
	}

	// Handle thousand separator as '.'
	// Handle sanitize database values.
	$number_parts = explode( '.', $number );
	$is_db_sanitize_val = ( 2 === count( $number_parts ) &&
	                        is_numeric( $number_parts[0] ) &&
	                        is_numeric( $number_parts[1] ) &&
	                        ( 6 === strlen( $number_parts[1] ) ) );

	if( $is_db_sanitize_val ) {
		// Sanitize database value.
		return number_format( $number, ( is_bool( $dp ) ? give_get_price_decimals() : $dp ), '.', '' );

	} elseif (
		'.' === $thousand_separator &&
		false !== strpos( $number, $thousand_separator )
	){
		// Fix point thousand separator value.
		$number = str_replace( '.', '', $number );
	}

	return give_sanitize_amount( $number, $dp, $trim_zeros );
}

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since      1.0
 *
 * @param  int|float|string $number     Expects either a float or a string with a decimal separator only (no thousands)
 * @param  int|bool         $dp         Number of decimals
 * @param  bool             $trim_zeros From end of string
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount( $number, $dp = false, $trim_zeros = false ) {

	// Bailout.
	if ( empty( $number ) || ( ! is_numeric( $number ) && ! is_string( $number ) ) ) {
		return $number;
	}

	// Remove slash from amount.
	// If thousand or decimal separator is set to ' then in $_POST or $_GET param we will get an escaped number.
	// To prevent notices and warning remove slash from amount/number.
	$number = wp_unslash( $number );

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
	if ( ! in_array( $thousand_separator, $decimals ) && ( false !== strpos( $number, $thousand_separator ) ) ) {
		$number = str_replace( $thousand_separator, '', $number );
	} elseif ( in_array( $thousand_separator, $decimals ) ) {
		$number = preg_replace( '/\.(?=.*\.)/', '', $number );
	}

	// Remove non numeric entity before decimal separator.
	$number     = preg_replace( '/[^0-9\.]/', '', $number );
	$default_dp = give_get_price_decimals();

	// Reset negative amount to zero.
	if ( 0 > $number ) {
		$number = number_format( 0, $default_dp, '.' );
	}

	// If number does not have decimal then add number of decimals to it.
	if (
		false === strpos( $number, '.' )
		|| ( $default_dp > strlen( substr( $number, strpos( $number, '.' ) + 1 ) ) )
	) {
		$number = number_format( $number, $default_dp, '.', '' );
	}

	// Format number by custom number of decimals.
	if ( false !== $dp ) {
		$dp     = intval( is_bool( $dp ) ? $default_dp : $dp );
		$dp     = apply_filters( 'give_sanitize_amount_decimals', $dp, $number );
		$number = number_format( floatval( $number ), $dp, '.', '' );
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
 * @param string $amount   Price amount to format
 * @param array  $args     Array of arguments.
 *
 * @return string $amount   Newly formatted amount or Price Not Available
 */
function give_format_amount( $amount, $args = array() ) {
	// Backward compatibility.
	if( is_bool( $args ) ) {
		$args = array( 'decimal' => $args );
	}

	$default_args = array(
		'decimal'  => true,
		'sanitize' => true,
		'currency' => give_get_currency(),
	);

	$args = wp_parse_args( $args, $default_args );

	$formatted     = 0;
	$thousands_sep = give_get_price_thousand_separator();
	$decimal_sep   = give_get_price_decimal_separator();
	$decimals      = ! empty( $args['decimal'] ) ? give_get_price_decimals() : 0;
	$currency      = $args['currency'];

	if ( ! empty( $amount ) ) {
		// Sanitize amount before formatting.
		$amount = ! empty( $args['sanitize'] ) ?
			give_maybe_sanitize_amount( $amount, $decimals ) :
			number_format( $amount, $decimals, '.', '' );

		switch ( $currency ) {
			case 'INR':
				$decimal_amount = '';

				// Extract decimals from amount
				if ( ( $pos = strpos( $amount, '.' ) ) !== false ) {
					if ( ! empty( $decimals ) ) {
						$decimal_amount = substr( round( substr( $amount, $pos ), $decimals ), 1 );
						$amount         = substr( $amount, 0, $pos );

						if ( ! $decimal_amount ) {
							$decimal_amount = substr( '.0000000000', 0, ( $decimals + 1 ) );
						} elseif ( ( $decimals + 1 ) > strlen( $decimal_amount ) ) {
							$decimal_amount = substr( "{$decimal_amount}000000000", 0, ( $decimals + 1 ) );
						}

					} else {
						$amount = number_format( $amount, $decimals, $decimal_sep, '' );
					}
				}

				// Extract last 3 from amount
				$result = substr( $amount, - 3 );
				$amount = substr( $amount, 0, - 3 );

				// Apply digits 2 by 2
				while ( strlen( $amount ) > 0 ) {
					$result = substr( $amount, - 2 ) . $thousands_sep . $result;
					$amount = substr( $amount, 0, - 2 );
				}

				$formatted = $result . $decimal_amount;
				break;

			default:
				$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );
		}
	}

	return apply_filters( 'give_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep, $currency, $args );
}


/**
 * Get human readable amount.
 *
 * Note: This function only support large number formatting from million to trillion
 *
 * @since 1.6
 *
 * @use   give_get_price_thousand_separator Get thousand separator.
 *
 * @param string $amount formatted amount number.
 * @param array  $args   Array of arguments.
 *
 * @return float|string  formatted amount number with large number names.
 */
function give_human_format_large_amount( $amount, $args = array() ) {
	$default_args = array(
		'currency' => give_get_currency(),
	);

	$args = wp_parse_args( $args, $default_args );

	// Get thousand separator.
	$thousands_sep = give_get_price_thousand_separator();

	// Sanitize amount.
	$sanitize_amount = give_maybe_sanitize_amount( $amount );

	// Explode amount to calculate name of large numbers.
	$amount_array = explode( $thousands_sep, $amount );

	// Calculate amount parts count.
	$amount_count_parts = count( $amount_array );

	// Human format amount (default).
	$human_format_amount = $amount;

	switch ( $args['currency'] ) {
		case 'INR':
			// Calculate large number formatted amount.
			if ( 4 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s arab', 'give' ), round( ( $sanitize_amount / 1000000000 ), 2 ) );
			} elseif ( 3 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s crore', 'give' ), round( ( $sanitize_amount / 10000000 ), 2 ) );
			} elseif ( 2 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s lakh', 'give' ), round( ( $sanitize_amount / 100000 ), 2 ) );
			}
			break;
		default:
			// Calculate large number formatted amount.
			if ( 4 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s trillion', 'give' ), round( ( $sanitize_amount / 1000000000000 ), 2 ) );
			} elseif ( 3 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s billion', 'give' ), round( ( $sanitize_amount / 1000000000 ), 2 ) );
			} elseif ( 2 < $amount_count_parts ) {
				$human_format_amount = sprintf( esc_html__( '%s million', 'give' ), round( ( $sanitize_amount / 1000000 ), 2 ) );
			}
	}

	return apply_filters( 'give_human_format_large_amount', $human_format_amount, $amount, $sanitize_amount );
}

/**
 * Returns a nicely formatted amount with custom decimal separator.
 *
 * @since 1.0
 *
 * @param int|float|string $amount   Formatted or sanitized price
 * @param int|bool         $dp       number of decimals
 * @param bool             $sanitize Whether or not sanitize number
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function give_format_decimal( $amount, $dp = false, $sanitize = true ) {
	$decimal_separator = give_get_price_decimal_separator();
	$formatted_amount  = $sanitize ?
		give_maybe_sanitize_amount( $amount, $dp ) :
		number_format( $amount, ( is_bool( $dp ) ? give_get_price_decimals() : $dp ), '.', '' );

	if ( false !== strpos( $formatted_amount, '.' ) ) {
		$formatted_amount = str_replace( '.', $decimal_separator, $formatted_amount );
	}

	return apply_filters( 'give_format_decimal', $formatted_amount, $amount, $decimal_separator );
}

/**
 * Formats the currency displayed.
 *
 * @since 1.0
 *
 * @param string $price The donation amount.
 * @param string $currency The currency code.
 * @param bool   $decode_currency Whether to decode the currency HTML format or not.
 *
 * @return mixed|string
 */
function give_currency_filter( $price = '', $currency = '', $decode_currency = false ) {

	if ( empty( $currency ) || ! array_key_exists( (string) $currency, give_get_currencies() ) ) {
		$currency = give_get_currency();
	}

	$position = give_get_option( 'currency_position', 'before' );

	$negative = $price < 0;

	if ( $negative ) {
		// Remove proceeding "-".
		$price = substr( $price, 1 );
	}

	$symbol = give_currency_symbol( $currency, $decode_currency );

	switch ( $currency ) :
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
			$formatted = ( 'before' === $position ? $symbol . $price : $price . $symbol );
			break;
		case 'NOK' :
			$formatted = ( 'before' === $position ? $symbol . ' ' . $price : $price . ' ' . $symbol );
			break;
		default :
			$formatted = ( 'before' === $position ? $currency . ' ' . $price : $price . ' ' . $currency );
			break;
	endswitch;

	/**
	 * Filter formatted amount with currency
	 *
	 * Filter name depends upon current value of currency and currency position.
	 * For example :
	 *           if currency is USD and currency position is before then
	 *           filter name will be give_usd_currency_filter_before
	 *
	 *           and if currency is USD and currency position is after then
	 *           filter name will be give_usd_currency_filter_after
	 */
	$formatted = apply_filters( 'give_' . strtolower( $currency ) . "_currency_filter_{$position}", $formatted, $currency, $price );

	if ( $negative ) {
		// Prepend the minus sign before the currency sign.
		$formatted = '-' . $formatted;
	}

	return $formatted;
}

/**
 * Set the number of decimal places per currency
 *
 * @since 1.0
 * @since 1.6 $decimals parameter removed from function params
 * *
 * @return int $decimals
 */
function give_currency_decimal_filter() {

	remove_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );

	// Set default number of decimals.
	$decimals = give_get_price_decimals();

	add_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );

	// Get number of decimals with backward compatibility ( version < 1.6 )
	if ( 1 <= func_num_args() ) {
		$decimals = ( false === func_get_arg( 0 ) ? $decimals : absint( func_get_arg( 0 ) ) );
	}

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
 * Get date format string on basis of given context.
 *
 * @since 1.7
 *
 * @param  string $date_context Date format context name.
 *
 * @return string                  Date format string
 */
function give_date_format( $date_context = '' ) {
	/**
	 * Filter the date context
	 *
	 * You can add your own date context or use already exist context.
	 * For example:
	 *    add_filter( 'give_date_format_contexts', 'add_new_date_contexts' );
	 *    function add_new_date_contexts( $date_format_contexts ) {
	 *        // You can add single context like this $date_format_contexts['checkout'] = 'F j, Y';
	 *        // Instead add multiple date context at once.
	 *        $new_date_format_contexts = array(
	 *            'checkout' => 'F j, Y',
	 *            'report'   => 'Y-m-d',
	 *            'email'    => 'm/d/Y',
	 *        );
	 *
	 *       // Merge date contexts array only if you are adding multiple date contexts at once otherwise return  $date_format_contexts.
	 *       return array_merge( $new_date_format_contexts, $date_format_contexts );
	 *
	 *    }
	 */
	$date_format_contexts = apply_filters( 'give_date_format_contexts', array() );

	// Set date format to default date format.
	$date_format = get_option( 'date_format' );

	// Update date format if we have non empty date format context array and non empty date format string for that context.
	if ( $date_context && ! empty( $date_format_contexts ) && array_key_exists( $date_context, $date_format_contexts ) ) {
		$date_format = ! empty( $date_format_contexts[ $date_context ] )
			? $date_format_contexts[ $date_context ]
			: $date_format;
	}

	return apply_filters( 'give_date_format', $date_format );
}

/**
 * Get cache key.
 *
 * @since  1.7
 * @deprecated 1.8.7 You can access this function from Give_Cache.
 *
 * @param  string $action Cache key prefix.
 * @param array  $query_args Query array.
 *
 * @return string
 */
function give_get_cache_key( $action, $query_args ) {
	return Give_Cache::get_key( $action, $query_args );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 *
 * @since  1.8
 *
 * @param  string|array $var
 *
 * @return string|array
 */
function give_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'give_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * Transforms php.ini notation for numbers (like '2M') to an integer.
 *
 * @since 1.8
 *
 * @param $size
 *
 * @return int
 */
function give_let_to_num( $size ) {
	$l   = substr( $size, - 1 );
	$ret = substr( $size, 0, - 1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
		case 'T':
			$ret *= 1024;
		case 'G':
			$ret *= 1024;
		case 'M':
			$ret *= 1024;
		case 'K':
			$ret *= 1024;
	}

	return $ret;
}

/**
 * Verify nonce.
 *
 * @since 1.8
 *
 * @param        $nonce
 * @param int   $action
 * @param array $wp_die_args
 */
function give_validate_nonce( $nonce, $action = - 1, $wp_die_args = array() ) {

	$default_wp_die_args = array(
		'message' => esc_html__( 'Nonce verification has failed.', 'give' ),
		'title'   => esc_html__( 'Error', 'give' ),
		'args'    => array(
			'response' => 403,
		),
	);

	$wp_die_args = wp_parse_args( $wp_die_args, $default_wp_die_args );

	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		wp_die(
			$wp_die_args['message'],
			$wp_die_args['title'],
			$wp_die_args['args']
		);
	}
}

/**
 * Check variable and get default or valid value.
 *
 * Helper function to check if a variable is set, empty, etc.
 *
 * @since 1.8
 *
 * @param                   $variable
 * @param string (optional) $conditional    default value: isset
 * @param bool (optional)   $default        default value: false
 * @param string (optional) $array_key_name default value: false
 *
 * @return mixed
 */
function give_check_variable( $variable, $conditional = '', $default = false, $array_key_name = '' ) {
	// Get value from array if array key non empty.
	if( empty( $array_key_name ) ) {
		switch ( $conditional ) {
			case 'isset_empty':
				$variable = ( isset( $variable ) && ! empty( $variable ) ) ? $variable : $default;
				break;

			case 'empty':
				$variable = ! empty( $variable ) ? $variable : $default;
				break;

			case 'null':
				$variable = ! is_null( $variable ) ? $variable : $default;
				break;

			default:
				$variable = isset( $variable ) ? $variable : $default;
		}
	} else {
		$isset = array_key_exists( $array_key_name, $variable );

		switch ( $conditional ) {
			case 'isset_empty':
				$variable = ( $isset && ! empty( $variable[ $array_key_name ] ) ) ? $variable[ $array_key_name ] : $default;
				break;

			case 'empty':
				$variable = ! empty( $variable[ $array_key_name ] ) ? $variable[ $array_key_name ] : $default;
				break;

			case 'null':
				$variable = $isset && ! is_null( $variable[ $array_key_name ] ) ? $variable[ $array_key_name ] : $default;
				break;

			default:
				$variable = $isset && isset( $variable[ $array_key_name ] ) ? $variable[ $array_key_name ] : $default;
		}
	}

	return $variable;

}
