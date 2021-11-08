<?php
/**
 * Currency Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.17
 */

/**
 * Get the set currency
 *
 * @since 1.0
 * @since 1.8.15 Upgrade function to handle dynamic currency
 *
 * @param int          $donation_or_form_id Donation or Form ID
 * @param array|object $args                Additional data
 *
 * @return string The currency code
 */
function give_get_currency( $donation_or_form_id = null, $args = [] ) {

	// Get currency from donation
	if ( is_numeric( $donation_or_form_id ) && 'give_payment' === get_post_type( $donation_or_form_id ) ) {
		$currency = give_get_meta( $donation_or_form_id, '_give_payment_currency', true );

		if ( empty( $currency ) ) {
			$currency = give_get_option( 'currency', 'USD' );
		}
	} else {
		$currency = give_get_option( 'currency', 'USD' );
	}

	/**
	 * Filter the currency on basis of donation, form id, or additional data.
	 *
	 * @since 1.0
	 */
	return apply_filters( 'give_currency', $currency, $donation_or_form_id, $args );
}

/**
 * Get the set currency position
 *
 * @since 1.3.6
 *
 * @return string The currency code
 */
function give_get_currency_position() {

	$currency_pos = give_get_option( 'currency_position', 'before' );

	return apply_filters( 'give_currency_position', $currency_pos );
}

/**
 * Get Currencies List
 *
 * @since 1.8.17
 *
 * @return array $currencies A list of the available currencies
 */
function give_get_currencies_list() {
	$currencies = Give_Cache_Setting::get_option( 'currencies' );

	/**
	 * Filter the currencies
	 * Note: you can register new currency by using this filter
	 * array(
	 *     'admin_label' => '',  // required
	 *     'symbol'      => '',  // required
	 *     'setting'     => ''   // required
	 *     ....
	 * )
	 *
	 * @since 1.8.15
	 * @deprecated 2.10.4 Use give_register_currency filter hook to register new currency.
	 *                   Example code to register new currency:
	 *
	 *                   add_filter( 'give_register_currency', 'give_add_costarican_currency', 10, 1 );
	 *                   function give_add_costarican_currency( $currencies ) {
	 *                        $currencies['VND'] = array(
	 *                            'admin_label' => __( 'Vietnamese đồng (₫)', 'give' ),
	 *                            'symbol'      => '&#8363;',
	 *                            'setting'     => array(
	 *                                'currency_position'   => 'after',
	 *                                'thousands_separator' => '.',
	 *                                'decimal_separator'   => ',',
	 *                                'number_decimals'     => 2,
	 *                            )
	 *                       );
	 *
	 *                       return $currencies;
	 *                  }
	 *
	 * @param  array  $currencies
	 */
	return (array) apply_filters( 'give_currencies', $currencies );
}

/**
 * Get Currencies
 *
 * @since 1.0
 *
 * @param string $info Specify currency info
 *
 * @return array $currencies A list of the available currencies
 */
function give_get_currencies( $info = 'admin_label' ) {

	$currencies = give_get_currencies_list();

	// Backward compatibility: handle old way of currency registration.
	// Backward compatibility: Return desired result.
	if ( ! empty( $currencies ) ) {
		foreach ( $currencies as $currency_code => $currency_setting ) {
			if ( is_string( $currency_setting ) ) {
				$currencies[ $currency_code ] = [
					'admin_label' => $currency_setting,
				];
			}

			$currencies[ $currency_code ] = wp_parse_args(
				$currencies[ $currency_code ],
				[
					'admin_label' => '',
					'symbol'      => $currency_code,
					'setting'     => [],
				]
			);
		}

		if ( ! empty( $info ) && is_string( $info ) && 'all' !== $info ) {
			$currencies = wp_list_pluck( $currencies, $info );
		}
	}

	return $currencies;
}


/**
 * Get all currency symbols
 *
 * @since 1.8.14
 *
 * @param bool $decode_currencies
 *
 * @return array
 */
function give_currency_symbols( $decode_currencies = false ) {
	$currencies = give_get_currencies( 'symbol' );

	if ( $decode_currencies ) {
		array_walk(
			$currencies,
			function ( &$currency_symbol ) {
				$currency_symbol = html_entity_decode( $currency_symbol, ENT_COMPAT, 'UTF-8' );
			}
		);
	}

	/**
	 * Filter the currency symbols
	 *
	 * @since 1.8.14
	 *
	 * @param array $currencies
	 */
	return apply_filters( 'give_currency_symbols', $currencies );
}


/**
 * Give Currency Symbol
 *
 * Given a currency determine the symbol to use. If no currency given, site default is used. If no symbol is determine,
 * the currency string is returned.
 *
 * @since      1.0
 *
 * @param  string $currency        The currency string.
 * @param  bool   $decode_currency Option to HTML decode the currency symbol.
 *
 * @return string           The symbol to use for the currency
 */
function give_currency_symbol( $currency = '', $decode_currency = false ) {

	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	$currencies = give_currency_symbols( $decode_currency );
	$symbol     = array_key_exists( $currency, $currencies ) ? $currencies[ $currency ] : $currency;

	/**
	 * Filter the currency symbol
	 *
	 * @since 1.0
	 *
	 * @param string $symbol
	 * @param string $currency
	 */
	return apply_filters( 'give_currency_symbol', $symbol, $currency );
}


/**
 * Get currency name.
 *
 * @since 1.8.8
 *
 * @param string $currency_code
 *
 * @return string
 */
function give_get_currency_name( $currency_code ) {
	$currency_name  = '';
	$currency_names = give_get_currencies();

	if ( $currency_code && array_key_exists( $currency_code, $currency_names ) ) {
		$currency_name = explode( '(', $currency_names[ $currency_code ] );
		$currency_name = trim( current( $currency_name ) );
	}

	/**
	 * Filter the currency name
	 *
	 * @since 1.8.8
	 *
	 * @param string $currency_name
	 * @param string $currency_code
	 */
	return apply_filters( 'give_currency_name', $currency_name, $currency_code );
}

/**
 * Formats the currency displayed.
 *
 * @since 1.0
 *
 * @param string $price The donation amount.
 * @param array  $args  It accepts 'currency_code', 'decode_currency' and 'form_id'.
 *
 * @return mixed|string
 */
function give_currency_filter( $price = '', $args = [] ) {

	// Get functions arguments.
	$func_args = func_get_args();

	// Backward compatibility: modify second param to array
	if ( isset( $func_args[1] ) && is_string( $func_args[1] ) ) {
		$args = [
			'currency_code'   => isset( $func_args[1] ) ? $func_args[1] : '',
			'decode_currency' => isset( $func_args[2] ) ? $func_args[2] : false,
			'form_id'         => isset( $func_args[3] ) ? $func_args[3] : '',
		];

		give_doing_it_wrong( __FUNCTION__, 'Pass second argument as Array.' );
	}

	// Set default values.
	$args = wp_parse_args(
		$args,
		[
			'currency_code'   => '',
			'decode_currency' => false,
			'form_id'         => '',
		]
	);

	if ( empty( $args['currency_code'] ) || ! array_key_exists( (string) $args['currency_code'], give_get_currencies() ) ) {
		$args['currency_code'] = give_get_currency( $args['form_id'] );
	}

	$args['position'] = give_get_option( 'currency_position', 'before' );

	/**
	 * @since 2.16.0 Check for a numeric value before comparing to zero.
	 * @link https://www.php.net/manual/en/migration80.incompatible.php
	 */
	$negative = is_numeric( $price ) && $price < 0;

	if ( $negative ) {
		// Remove proceeding "-".
		$price = substr( $price, 1 );
	}

	$args['symbol'] = give_currency_symbol( $args['currency_code'], $args['decode_currency'] );

	switch ( $args['currency_code'] ) :
		case 'GBP':
		case 'BRL':
		case 'EUR':
		case 'USD':
		case 'AUD':
		case 'CAD':
		case 'HKD':
		case 'MXN':
		case 'NZD':
		case 'SGD':
		case 'JPY':
		case 'THB':
		case 'INR':
		case 'IDR':
		case 'IRR':
		case 'TRY':
		case 'RUB':
		case 'SEK':
		case 'PLN':
		case 'PHP':
		case 'TWD':
		case 'MYR':
		case 'CZK':
		case 'DKK':
		case 'HUF':
		case 'ILS':
		case 'MAD':
		case 'KRW':
		case 'ZAR':
			$formatted = ( 'before' === $args['position'] ? $args['symbol'] . $price : $price . $args['symbol'] );
			break;
		case 'NOK':
			$formatted = ( 'before' === $args['position'] ? $args['symbol'] . ' ' . $price : $price . ' ' . $args['symbol'] );
			break;
		default:
			$formatted = ( 'before' === $args['position'] ? $args['symbol'] . ' ' . $price : $price . ' ' . $args['symbol'] );
			break;
	endswitch;

	/**
	 * Filter formatted amount
	 *
	 * @since 1.8.17
	 */
	$formatted = apply_filters( 'give_currency_filter', $formatted, $args, $price );

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
	$formatted = apply_filters(
		'give_' . strtolower( $args['currency_code'] ) . "_currency_filter_{$args['position']}",
		$formatted,
		$args['currency_code'],
		$price,
		$args
	);

	if ( $negative ) {
		// Prepend the minus sign before the currency sign.
		$formatted = '-' . $formatted;
	}

	return $formatted;
}

/**
 * This function is used to fetch list of zero based currencies.
 *
 * @since 2.3.0
 *
 * @return array
 */
function give_get_zero_based_currencies() {

	$zero_based_currencies = [
		'JPY', // Japanese Yen.
		'KRW', // South Korean Won.
		'CLP', // Chilean peso.
		'ISK', // Icelandic króna.
		'BIF', // Burundian franc.
		'DJF', // Djiboutian franc.
		'GNF', // Guinean franc.
		'KHR', // Cambodian riel.
		'KPW', // North Korean won.
		'LAK', // Lao kip.
		'LKR', // Sri Lankan rupee.
		'MGA', // Malagasy ariary.
		'MZN', // Mozambican metical.
		'VUV', // Vanuatu vatu.
	];

	/**
	 * This filter hook can be used to update the list of zero based currencies.
	 *
	 * @since 2.3.0
	 */
	return apply_filters( 'give_get_zero_based_currencies', $zero_based_currencies );
}

/**
 * Zero Decimal based Currency.
 *
 * @since 1.8.14
 * @since 2.2.0 Modified list.
 * @see   https://github.com/impress-org/give/issues/2191
 *
 * @param string $currency Currency code
 *
 * @return bool
 */
function give_is_zero_based_currency( $currency = '' ) {

	$zero_based_currency = give_get_zero_based_currencies();

	// Set default currency.
	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	// Check for Zero Based Currency.
	if ( in_array( $currency, $zero_based_currency ) ) {
		return true;
	}

	return false;
}


/**
 * Check if currency support right to left direction or not.
 *
 * @param string $currency
 *
 * @return bool
 */
function give_is_right_to_left_supported_currency( $currency = '' ) {
	$zero_based_currency = apply_filters(
		'give_right_to_left_supported_currency',
		[
			'IRR',
			'RIAL',
			'MAD',
			'AED',
			'BHD',
			'KWD',
			'OMR',
			'SAR',
			'TND', // https://en.wikipedia.org/wiki/Tunisian_dinar
			'QAR', // https://en.wikipedia.org/wiki/Qatari_riyal
			'LYD', // https://en.wikipedia.org/wiki/Libyan_dinar
			'LBP', // https://en.wikipedia.org/wiki/Lebanese_pound
			'IRT', // https://en.wikipedia.org/wiki/Iranian_toman
			'IQD', // https://en.wikipedia.org/wiki/Iraqi_dinar
			'DZD', // https://en.wikipedia.org/wiki/Algerian_dinar
			'AFN', // https://en.wikipedia.org/wiki/Afghan_afghani
		]
	);

	// Set default currency.
	if ( empty( $currency ) ) {
		$currency = give_get_currency();
	}

	// Check for Zero Based Currency.
	if ( in_array( $currency, $zero_based_currency ) ) {
		return true;
	}

	return false;
}
