<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     Give
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Currency Formatting Settings for each donation.
 *
 * @param int|string $id_or_currency_code Donation ID or Currency code.
 *
 * @since 1.8.15
 *
 * @return mixed
 */
function give_get_currency_formatting_settings( $id_or_currency_code = null ) {
	$give_options = give_get_settings();
	$setting      = array();

	if ( ! empty( $id_or_currency_code ) ) {
		$currencies = give_get_currencies( 'all' );

		// Set default formatting setting only if currency not set as global currency.
		if (
			is_string( $id_or_currency_code ) &&
			! empty( $give_options['currency'] ) &&
			$id_or_currency_code !== $give_options['currency'] &&
			array_key_exists( $id_or_currency_code, $currencies )
		) {
			$setting = $currencies[ $id_or_currency_code ]['setting'];
		} elseif ( is_numeric( $id_or_currency_code ) && 'give_payment' === get_post_type( $id_or_currency_code ) ) {
			$currency = give_get_meta( $id_or_currency_code, '_give_payment_currency', true );

			if (
				! empty( $currency ) &&
				$give_options['currency'] !== $currency
			) {
				$setting = $currencies[ $currency ]['setting'];
			}
		}
	}

	if ( empty( $setting ) ) {
		// Set thousand separator.
		$thousand_separator = isset( $give_options['thousands_separator'] ) ? $give_options['thousands_separator'] : ',';
		$thousand_separator = empty( $thousand_separator ) ? ' ' : $thousand_separator;

		// Set decimal separator.
		$default_decimal_separators = array(
			'.' => ',',
			',' => '.',
		);

		$default_decimal_separator = in_array( $thousand_separator, $default_decimal_separators ) ?
			$default_decimal_separators[ $thousand_separator ] :
			'.';

		$decimal_separator = ! empty( $give_options['decimal_separator'] ) ? $give_options['decimal_separator'] : $default_decimal_separator;

		$setting = array(
			'currency_position'   => give_get_option( 'currency_position', 'before' ),
			'thousands_separator' => $thousand_separator,
			'decimal_separator'   => $decimal_separator,
			'number_decimals'     => give_get_option( 'number_decimals', 0 ),
		);
	}

	/**
	 * Filter the currency formatting setting.
	 *
	 * @since 1.8.15
	 */
	return apply_filters( 'give_get_currency_formatting_settings', $setting, $id_or_currency_code );
}

/**
 * Get decimal count
 *
 * @since 1.6
 *
 * @param int|string $id_or_currency_code
 *
 * @return mixed
 */
function give_get_price_decimals( $id_or_currency_code = null ) {
	// Set currency on basis of donation id.
	if ( empty( $id_or_currency_code ) ) {
		$id_or_currency_code = give_get_currency();
	}

	$number_of_decimals = 0;

	if ( ! give_is_zero_based_currency( $id_or_currency_code ) ) {
		$setting            = give_get_currency_formatting_settings( $id_or_currency_code );
		$number_of_decimals = $setting['number_decimals'];
	}

	/**
	 * Filter the number of decimals
	 *
	 * @since 1.6
	 */
	return apply_filters( 'give_sanitize_amount_decimals', $number_of_decimals, $id_or_currency_code );
}

/**
 * Get thousand separator
 *
 * @param int|string $id_or_currency_code
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_thousand_separator( $id_or_currency_code = null ) {
	$setting = give_get_currency_formatting_settings( $id_or_currency_code );

	/**
	 * Filter the thousand separator
	 *
	 * @since 1.6
	 */
	return apply_filters( 'give_get_price_thousand_separator', $setting['thousands_separator'], $id_or_currency_code );
}

/**
 * Get decimal separator
 *
 * @param string $id_or_currency_code
 *
 * @since 1.6
 *
 * @return mixed
 */
function give_get_price_decimal_separator( $id_or_currency_code = null ) {
	$setting = give_get_currency_formatting_settings( $id_or_currency_code );

	/**
	 * Filter the thousand separator
	 *
	 * @since 1.6
	 */
	return apply_filters( 'give_get_price_decimal_separator', $setting['decimal_separator'], $id_or_currency_code );
}

/**
 * Check if amount sanitized
 * Note: only for internal purpose
 *
 * Current this function only check if number is DB sanitize.
 *
 * @param string $amount
 *
 * @return bool
 * @since 2.4.5
 */
function give_is_amount_sanitized( $amount ) {
	$is_sanitize = false;

	if ( false === strpos( $amount, '.' ) ) {
		return $is_sanitize;
	}

	$number_parts = explode( '.', $amount );

	// Handle thousand separator as '.'
	// Handle sanitize database values.
	$is_sanitize = ( 2 === count( $number_parts ) &&
					 is_numeric( $number_parts[0] ) &&
					 is_numeric( $number_parts[1] ) &&
					 in_array( strlen( $number_parts[1] ), array( 6, 10 ) ) );

	return $is_sanitize;
}

/**
 * Sanitize Amount before saving to database
 *
 * @since      1.8.12
 *
 * @param  int|float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  array|bool       $args   It accepts 'number_decimals', 'trim_zeros', 'currency'.
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount_for_db( $number, $args = array() ) {
	$args['number_decimals'] = 6;

	if (
		( isset( $args['currency'] ) && 'BTC' === $args['currency'] )
		|| 'BTC' === give_get_currency()
	) {
		$args['number_decimals'] = 10;
	}

	return give_maybe_sanitize_amount( $number, $args );
}

/**
 * Sanitize Amount before saving to database
 *
 * @since      1.8.12
 *
 * @param  int|float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  array|bool       $args   It accepts 'number_decimals', 'trim_zeros', 'currency'.
 *
 * @return string $amount Newly sanitized amount
 */
function give_maybe_sanitize_amount( $number, $args = array() ) {
	// Bailout.
	if ( empty( $number ) || ( ! is_numeric( $number ) && ! is_string( $number ) ) ) {
		return $number;
	}

	$func_args = func_get_args();

	// Backward compatibility.
	if ( isset( $func_args[1] ) && ( is_bool( $func_args[1] ) || is_numeric( $func_args[1] ) ) ) {
		$args = array(
			'number_decimals' => $func_args[1],
			'trim_zeros'      => isset( $func_args[2] ) ? $func_args[2] : false,
		);
	}

	$args = wp_parse_args(
		$args,
		array(
			'number_decimals' => false,
			'trim_zeros'      => false,
			'currency'        => give_get_currency(),
		)
	);

	$thousand_separator = give_get_price_thousand_separator( $args['currency'] );
	$decimal_separator  = give_get_price_decimal_separator( $args['currency'] );
	$number_decimals    = is_bool( $args['number_decimals'] ) ?
		give_get_price_decimals( $args['currency'] ) :
		$args['number_decimals'];

	// Explode number by . decimal separator.
	$number_parts = explode( '.', $number );

	// Remove currency symbols from number if any.
	$number = trim( str_replace( give_currency_symbols( true ), '', $number ) );

	if (
		// Non formatted number.
		false === strpos( $number, $thousand_separator )
		&& false === strpos( $number, $decimal_separator )
	) {
		return number_format( $number, $number_decimals, '.', '' );
	} elseif (
		// Decimal formatted number.
		// If number of decimal place set to non zero and
		// number only contains `.` as separator, precision set to less then or equal to number of decimal
		// then number will be consider as decimal formatted which means number is already sanitized.
		$number_decimals
		&& '.' === $thousand_separator
		&& false !== strpos( $number, $thousand_separator )
		&& false === strpos( $number, $decimal_separator )
		&& 2 === count( $number_parts )
		&& ( $number_decimals >= strlen( $number_parts[1] ) )
	) {
		return number_format( $number, $number_decimals, '.', '' );
	}

	if ( give_is_amount_sanitized( $number ) ) {
		// Sanitize database value.
		return number_format( $number, $number_decimals, '.', '' );

	} elseif (
		'.' === $thousand_separator &&
		false !== strpos( $number, $thousand_separator )
	) {
		// Fix point thousand separator value.
		$number = str_replace( '.', '', $number );
	}

	return give_sanitize_amount( $number, $args );
}

/**
 * Sanitize Amount
 *
 * Note: Do not this function to sanitize amount instead use give_maybe_sanitize_amount function.
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since      1.0
 *
 * @param  int|float|string $number Expects either a float or a string with a decimal separator only (no thousands)
 * @param  array|bool       $args   It accepts 'number_decimals', 'trim_zeros', 'currency'.
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount( $number, $args = array() ) {

	// Bailout.
	if ( empty( $number ) || ( ! is_numeric( $number ) && ! is_string( $number ) ) ) {
		return $number;
	}

	// Get function arguments.
	$func_args = func_get_args();

	// Backward compatibility.
	if ( isset( $func_args[1] ) && ( is_bool( $func_args[1] ) || is_numeric( $func_args[1] ) ) ) {
		$args = array(
			'number_decimals' => $func_args[1],
			'trim_zeros'      => isset( $func_args[2] ) ? $func_args[2] : false,
		);
	}

	$args = wp_parse_args(
		$args,
		array(
			'number_decimals' => false,
			'trim_zeros'      => false,
			'currency'        => give_get_currency(),
		)
	);

	// Remove slash from amount.
	// If thousand or decimal separator is set to ' then in $_POST or $_GET param we will get an escaped number.
	// To prevent notices and warning remove slash from amount/number.
	$number = wp_unslash( $number );

	$thousand_separator = give_get_price_thousand_separator( $args['currency'] );

	$locale   = localeconv();
	$decimals = array(
		give_get_price_decimal_separator( $args['currency'] ),
		$locale['decimal_point'],
		$locale['mon_decimal_point'],
	);

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
	$default_dp = give_get_price_decimals( $args['currency'] );

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
	if ( false !== $args['number_decimals'] ) {
		$dp     = intval( is_bool( $args['number_decimals'] ) ? $default_dp : $args['number_decimals'] );
		$dp     = apply_filters( 'give_sanitize_amount_decimals', $dp, $number );
		$number = number_format( floatval( $number ), $dp, '.', '' );
	}

	// Trim zeros.
	if ( $args['trim_zeros'] && strstr( $number, '.' ) ) {
		$number = rtrim( rtrim( $number, '0' ), '.' );
	}

	/**
	 * Filter the sanitize amount
	 *
	 * @since 1.0
	 */
	return apply_filters( 'give_sanitize_amount', $number );
}

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.0
 *
 * @param string $amount Price amount to format
 * @param array  $args   Array of arguments.
 *
 * @return string $amount   Newly formatted amount or Price Not Available
 */
function give_format_amount( $amount, $args = array() ) {
	// Backward compatibility.
	if ( is_bool( $args ) ) {
		$args = array(
			'decimal' => $args,
		);
	}

	$default_args = array(
		'decimal'     => true,
		'sanitize'    => true,
		'donation_id' => 0,
		'currency'    => '',
	);

	$args = wp_parse_args( $args, $default_args );

	// Set Currency based on donation id, if required.
	if ( $args['donation_id'] && empty( $args['currency'] ) ) {
		$args['currency'] = give_get_meta( $args['donation_id'], '_give_payment_currency', true );
	}

	$formatted     = 0;
	$currency      = ! empty( $args['currency'] ) ? $args['currency'] : give_get_currency( $args['donation_id'] );
	$thousands_sep = give_get_price_thousand_separator( $currency );
	$decimal_sep   = give_get_price_decimal_separator( $currency );
	$decimals      = ! empty( $args['decimal'] ) ? give_get_price_decimals( $currency ) : 0;

	if ( ! empty( $amount ) ) {
		// Sanitize amount before formatting.
		$amount = ! empty( $args['sanitize'] ) ?
			give_maybe_sanitize_amount(
				$amount,
				array(
					'number_decimals' => $decimals,
					'currency'        => $currency,
				)
			) :
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
							$decimal_amount = substr( "{$decimal_sep}0000000000", 0, ( $decimals + 1 ) );
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

	/**
	 * Filter the formatted amount
	 *
	 * @since 1.0
	 */
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
 * @return string  formatted amount number with large number names.
 */
function give_human_format_large_amount( $amount, $args = array() ) {

	// Set default currency;
	if ( empty( $args['currency'] ) ) {
		$args['currency'] = give_get_currency();
	}

	// Get thousand separator.
	$thousands_sep = give_get_price_thousand_separator( $args['currency'] );

	// Sanitize amount for calculation purpose.
	$sanitize_amount = give_maybe_sanitize_amount(
		$amount,
		array(
			'currency' => $args['currency'],
		)
	);

	// Bailout.
	if ( ! floatval( $sanitize_amount ) ) {
		return '0';
	};

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
 * @param array $args        {
 *
 * @type int|float|string $amount      Formatted or sanitized price. (optional if donation id set)
 * @type int              $donation_id donation amount (optional if set amount, but provide it for better result if formatting decimal amount of donation).
 * @type string           $currency    donation amount (optional if set donation id). Provide either amount or donation id
 * @type int|bool         $dp          number of decimals
 * @type bool             $sanitize    Whether or not sanitize number
 * }
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function give_format_decimal( $args ) {
	// Backward compatibility.
	if ( ! is_array( $args ) ) {
		$func_args = func_get_args();
		$args      = array(
			'amount'   => $func_args[0],
			'dp'       => isset( $func_args[1] ) ? $func_args[1] : false,
			'sanitize' => isset( $func_args[2] ) ? $func_args[2] : true,
		);
	}

	$args = wp_parse_args(
		$args,
		array(
			'amount'      => '',
			'donation_id' => 0,
			'currency'    => '',
			'dp'          => false,
			'sanitize'    => false,
		)
	);

	if ( ! empty( $args['donation_id'] ) ) {

		// Set currency if not already done.
		if ( empty( $args['currency'] ) ) {
			$args['currency'] = give_get_payment_currency_code( $args['donation_id'] );
		}

		// Set amount if not already done.
		if ( empty( $args['amount'] ) ) {
			$args['amount'] = give_donation_amount( $args['donation_id'] );
		}
	}

	$decimal_separator = give_get_price_decimal_separator();
	$formatted_amount  = $args['sanitize'] ?
		give_maybe_sanitize_amount(
			$args['amount'],
			array(
				'number_decimals' => $args['dp'],
				'currency'        => $args['currency'],
			)
		) :
		number_format( $args['amount'], ( is_bool( $args['dp'] ) ? give_get_price_decimals( $args['currency'] ) : $args['dp'] ), '.', '' );

	if ( false !== strpos( $formatted_amount, '.' ) ) {
		$formatted_amount = str_replace( '.', $decimal_separator, $formatted_amount );
	}

	return apply_filters( 'give_format_decimal', $formatted_amount, $args['amount'], $decimal_separator, $args );
}

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
 * @since      1.7
 * @deprecated 1.8.7 You can access this function from Give_Cache.
 *
 * @param  string $action     Cache key prefix.
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
	}

	return is_scalar( $var ) ? sanitize_text_field( wp_unslash( $var ) ) : $var;
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
 * @param string $nonce       Nonce Hash.
 * @param int    $action      Nonce verification action.
 * @param array  $wp_die_args Nonce fail arguments.
 *
 * @return bool
 */
function give_validate_nonce( $nonce, $action = - 1, $wp_die_args = array() ) {

	// Verify nonce.
	$verify_nonce = wp_verify_nonce( $nonce, $action );

	// On ajax request send nonce verification status.
	if ( wp_doing_ajax() ) {
		return $verify_nonce;
	}

	if ( ! $verify_nonce ) {
		$wp_die_args = wp_parse_args(
			$wp_die_args,
			array(
				'message' => __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ),
				'title'   => __( 'Error', 'give' ),
				'args'    => array(
					'response' => 403,
				),
			)
		);

		wp_die(
			$wp_die_args['message'],
			$wp_die_args['title'],
			$wp_die_args['args']
		);
	}

	return true;
}

/**
 * Verify nonce while processing donation form.
 *
 * @since 2.0
 *
 * @param string $nonce   Nonce value.
 * @param int    $form_id Donation Form ID.
 *
 * @return bool
 */
function give_verify_donation_form_nonce( $nonce, $form_id ) {

	// Form nonce action.
	$nonce_action = "give_donation_form_nonce_{$form_id}";

	// Nonce validation.
	$verify_nonce = give_validate_nonce( $nonce, $nonce_action );

	if ( ! $verify_nonce ) {
		give_set_error( 'donation_form_nonce', __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ) );
	}

	return $verify_nonce;
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
 * @param mixed (optional)  $default        default value: false
 * @param string (optional) $array_key_name default value: false
 *
 * @return mixed
 */
function give_check_variable( $variable, $conditional = '', $default = false, $array_key_name = '' ) {
	// Get value from array if array key non empty.
	if ( empty( $array_key_name ) ) {
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
