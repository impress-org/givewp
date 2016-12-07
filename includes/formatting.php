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
 * @param  int|float|string $number     Expects either a float or a string with a decimal separator only (no thousands)
 * @param  int|bool         $dp         Number of decimals
 * @param  bool             $trim_zeros From end of string
 *
 *
 * @return string $amount Newly sanitized amount
 */
function give_sanitize_amount( $number, $dp = false, $trim_zeros = false ) {

	// Bailout.
	if ( empty( $number ) ) {
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
		$dp     = intval( empty( $dp ) ? $default_dp : $dp );
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
 * @param bool   $decimals Whether or not to use decimals. Useful when set to false for non-currency numbers.
 *
 * @return string $amount   Newly formatted amount or Price Not Available
 */
function give_format_amount( $amount, $decimals = true ) {
	$thousands_sep = give_get_option( 'thousands_separator', ',' );
	$decimal_sep   = give_get_option( 'decimal_separator', '.' );

	if ( empty( $amount ) ) {
		$amount = 0;
	} else {
		// Sanitize amount before formatting.
		$amount = give_sanitize_amount( $amount );
	}

	$decimals = $decimals ? give_get_price_decimals() : 0;

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
 * @use   give_get_price_thousand_separator Get thousand separator.
 *
 * @param string $amount formatted amount number.
 *
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

	// Human format amount (default).
	$human_format_amount = $amount;

	// Calculate large number formatted amount.
	if ( 4 < $amount_count_parts ) {
		$human_format_amount = sprintf( esc_html__( '%s trillion', 'give' ), round( ( $sanitize_amount / 1000000000000 ), 2 ) );
	} elseif ( 3 < $amount_count_parts ) {
		$human_format_amount = sprintf( esc_html__( '%s billion', 'give' ), round( ( $sanitize_amount / 1000000000 ), 2 ) );
	} elseif ( 2 < $amount_count_parts ) {
		$human_format_amount = sprintf( esc_html__( '%s million', 'give' ), round( ( $sanitize_amount / 1000000 ), 2 ) );
	}

	return apply_filters( 'give_human_format_large_amount', $human_format_amount, $amount, $sanitize_amount );
}

/**
 * Returns a nicely formatted amount with custom decimal separator.
 *
 * @since 1.0
 *
 * @param int|float|string $amount Formatted or sanitized price
 * @param int|bool         $dp     number of decimals
 *
 * @return string $amount Newly formatted amount or Price Not Available
 */
function give_format_decimal( $amount, $dp = false ) {
	$decimal_separator = give_get_price_decimal_separator();
	$formatted_amount  = give_sanitize_amount( $amount, $dp );

	if ( false !== strpos( $formatted_amount, '.' ) ) {
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
 * @return mixed|string
 */
function give_currency_filter( $price = '', $currency = '' ) {

	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	$position = give_get_option( 'currency_position', 'before' );

	$negative = $price < 0;

	if ( $negative ) {
		// Remove proceeding "-".
		$price = substr( $price, 1 );
	}

	$symbol = give_currency_symbol( $currency );

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
	 *
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
 **
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
 * Sanitize thousand separator
 *
 * @since   1.6
 * @used-by Give_Plugin_Settings::give_settings()
 *
 * @param   string $value
 * @param   array  $field_args
 * @param   object $field
 *
 * @return mixed
 */
function give_sanitize_thousand_separator( $value, $field_args, $field ) {
	return stripslashes( $value );
}


/**
 * Sanitize number of decimals
 *
 * @since   1.6
 * @used-by Give_Plugin_Settings::give_settings()
 *
 * @param   string $value
 * @param   array  $field_args
 * @param   object $field
 *
 * @return  mixed
 */
function give_sanitize_number_decimals( $value, $field_args, $field ) {
	return absint( $value );
}

/**
 * Sanitize price file value
 *
 * @since   1.6
 * @used-by give_single_forms_cmb2_metaboxes()
 *
 * @param   string $value
 * @param   array  $field_args
 * @param   object $field
 *
 * @return  mixed
 */
function give_sanitize_price_field_value( $value, $field_args, $field ) {
	return give_sanitize_amount( $value );
}


/**
 * Manually render amount field.
 *
 * @since  1.7
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 *
 * @return void
 */
function give_cmb_amount_field_render_row_cb( $field_args, $field ) {

	// Get args.
	$id                = $field->args( 'id' );
	$label             = $field->args( 'name' );
	$name              = $field->args( '_name' );
	$description       = $field->args( 'description' );
	$attributes        = $field->args( 'attributes' );
	$attributes_string = '';
	$row_class         = $field->row_classes();

	// Get attributes.
	if ( ! empty( $attributes ) ) {
		foreach ( $attributes as $attribute_name => $attribute_val ) {
			$attributes_string[] = "$attribute_name=\"$attribute_val\"";
		}

		$attributes_string = implode( ' ', $attributes_string );
	}

	// Get row class.
	if ( ! empty( $row_class ) && is_array( $row_class ) ) {
		$row_class = implode( ' ', $row_class );
	}
	?>
	<div class="cmb-row <?php echo $row_class; ?>">
		<div class="cmb-th">
			<label for="<?php echo $id; ?>"><?php echo $label; ?></label>
		</div>
		<div class="cmb-td">
			<?php echo( give_get_option( 'currency_position' ) == 'before' ? '<span class="give-money-symbol give-money-symbol-before">' . give_currency_symbol() . '</span>' : '' ); ?>
			<input id="<?php echo $id; ?>" type="text" name="<?php echo $name; ?>" <?php echo $attributes_string ?>/>
			<?php echo( give_get_option( 'currency_position' ) == 'after' ? '<span class="give-money-symbol give-money-symbol-after">' . give_currency_symbol() . '</span>' : '' ); ?>

			<span class="cmb2-metabox-description"><?php echo $description; ?></span>
		</div>
	</div>
	<?php
}


/**
 * Get date format string on basis of given context.
 *
 *
 * @since 1.7
 *
 * @param  string $date_context    Date format context name.
 *
 * @return string                  Date format string
 */
function give_date_format ( $date_context = '' ) {
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
	$date_format = get_option('date_format');


	// Update date format if we have non empty date format context array and non empty date format string for that context.
	if( $date_context &&  ! empty( $date_format_contexts ) && array_key_exists( $date_context, $date_format_contexts ) ) {
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
 *
 * @param  string $action     Cache key prefix.
 * @param array   $query_args Query array.
 *
 * @return string
 */
function give_get_cache_key( $action, $query_args ) {
	// Bailout.
	if( ! is_array( $query_args ) || empty( $query_args ) ) {
		return '';
	}

	return "give_cache_{$action}_" . substr( md5( serialize( $query_args ) ), 0, 15 );
}