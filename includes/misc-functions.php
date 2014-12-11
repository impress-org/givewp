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
