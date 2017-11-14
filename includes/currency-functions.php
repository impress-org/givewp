<?php
/**
 * Currency Functions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2017, WordImpress
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
function give_get_currency( $donation_or_form_id = null, $args = array() ) {

	// Get currency from donation
	if ( is_numeric( $donation_or_form_id ) && 'give_payment' === get_post_type( $donation_or_form_id ) ) {
		$donation_meta = give_get_meta( $donation_or_form_id, '_give_payment_meta', true );

		if ( ! empty( $donation_meta['currency'] ) ) {
			$currency = $donation_meta['currency'];
		} else {
			$currency = give_get_option( 'currency', 'USD' );
		}
	} else {
		$currency = give_get_option( 'currency', 'USD' );
	}

	/**
	 * Filter the currency on basis of donation or form id or addtional data.
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
	$currencies = array(
		'USD' => array(
			'admin_label' => __( 'US Dollars ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'EUR' => array(
			'admin_label' => __( 'Euros (€)', 'give' ),
			'symbol'      => '&euro;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GBP' => array(
			'admin_label' => __( 'Pounds Sterling (£)', 'give' ),
			'symbol'      => '&pound;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'AUD' => array(
			'admin_label' => __( 'Australian Dollars ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'BRL' => array(
			'admin_label' => __( 'Brazilian Real (R$)', 'give' ),
			'symbol'      => '&#82;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'CAD' => array(
			'admin_label' => __( 'Canadian Dollars ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'CZK' => array(
			'admin_label' => __( 'Czech Koruna (Kč)', 'give' ),
			'symbol'      => '&#75;&#269;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'DKK' => array(
			'admin_label' => __( 'Danish Krone (kr.)', 'give' ),
			'symbol'      => '&nbsp;kr.&nbsp;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'HKD' => array(
			'admin_label' => __( 'Hong Kong Dollar ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'HUF' => array(
			'admin_label' => __( 'Hungarian Forint (Ft)', 'give' ),
			'symbol'      => '&#70;&#116;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'ILS' => array(
			'admin_label' => __( 'Israeli Shekel (₪)', 'give' ),
			'symbol'      => '&#8362;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'JPY' => array(
			'admin_label' => __( 'Japanese Yen (¥)', 'give' ),
			'symbol'      => '&yen;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 0,
			),
		),
		'MYR' => array(
			'admin_label' => __( 'Malaysian Ringgits (RM)', 'give' ),
			'symbol'      => '&#82;&#77;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'MXN' => array(
			'admin_label' => __( 'Mexican Peso ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'MAD' => array(
			'admin_label' => __( 'Moroccan Dirham (&#x2e;&#x62f;&#x2e;&#x645;)', 'give' ),
			'symbol'      => '&#x2e;&#x62f;&#x2e;&#x645;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'NZD' => array(
			'admin_label' => __( 'New Zealand Dollar ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'NOK' => array(
			'admin_label' => __( 'Norwegian Krone (Kr.)', 'give' ),
			'symbol'      => '&#107;&#114;.',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'PHP' => array(
			'admin_label' => __( 'Philippine Pesos (₱)', 'give' ),
			'symbol'      => '&#8369;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'PLN' => array(
			'admin_label' => __( 'Polish Zloty (zł)', 'give' ),
			'symbol'      => '&#122;&#322;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ' ',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'SGD' => array(
			'admin_label' => __( 'Singapore Dollar ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'KRW' => array(
			'admin_label' => __( 'South Korean Won (₩)', 'give' ),
			'symbol'      => '&#8361;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 0,
			),
		),
		'ZAR' => array(
			'admin_label' => __( 'South African Rand (R)', 'give' ),
			'symbol'      => '&#82;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ' ',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'SEK' => array(
			'admin_label' => __( 'Swedish Krona (kr)', 'give' ),
			'symbol'      => '&nbsp;kr.&nbsp;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ' ',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CHF' => array(
			'admin_label' => __( 'Swiss Franc (CHF)', 'give' ),
			'symbol'      => 'CHF',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'TWD' => array(
			'admin_label' => __( 'Taiwan New Dollars (NT$)', 'give' ),
			'symbol'      => '&#78;&#84;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '\'',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'THB' => array(
			'admin_label' => __( 'Thai Baht (฿)', 'give' ),
			'symbol'      => '&#3647;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'INR' => array(
			'admin_label' => __( 'Indian Rupee (₹)', 'give' ),
			'symbol'      => '&#8377;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'TRY' => array(
			'admin_label' => __( 'Turkish Lira (₺)', 'give' ),
			'symbol'      => '&#8378;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'IRR' => array(
			'admin_label' => __( 'Iranian Rial (﷼)', 'give' ),
			'symbol'      => '&#xfdfc;',
			'setting'     => array(
				'currency_position'   => 'after',
				'thousands_separator' => ',',
				'decimal_separator'   => '.',
				'number_decimals'     => 2,
			),
		),
		'RUB' => array(
			'admin_label' => __( 'Russian Rubles (руб)', 'give' ),
			'symbol'      => '&#8381;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AED'  => array(
			'admin_label' => __( 'United Arab Emirates dirham (د.إ)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AFN'  => array(
			'admin_label' => __( 'Afghan afghani (؋)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'ALL'  => array(
			'admin_label' => __( 'Albanian lek (L)', 'give' ),
			'symbol'      => '&#76;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AMD'  => array(
			'admin_label' => __( 'Armenian dram (֏)', 'give' ),
			'symbol'      => '&#1423;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'ANG'  => array(
			'admin_label' => __( 'Netherlands Antillean guilder (ƒ)', 'give' ),
			'symbol'      => '&#402;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AOA'  => array(
			'admin_label' => __( 'Angolan kwanza (Kz)', 'give' ),
			'symbol'      => '&#75;&#122;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'ARS'  => array(
			'admin_label' => __( 'Argentine peso ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AWG'  => array(
			'admin_label' => __( 'Aruban florin (ƒ)', 'give' ),
			'symbol'      => '&#402;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'AZN'  => array(
			'admin_label' => __( 'Azerbaijani manat (ман)', 'give' ),
			'symbol'      => '&#77;&#97;&#72;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BAM'  => array(
			'admin_label' => __( 'Bosnia and Herzegovina convertible mark (KM)', 'give' ),
			'symbol'      => '&#75;&#77;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BBD'  => array(
			'admin_label' => __( 'Barbadian dollar (Bds$)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BDT'  => array(
			'admin_label' => __( 'Bangladeshi taka (৳)', 'give' ),
			'symbol'      => '&#2547;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BGN'  => array(
			'admin_label' => __( 'Bulgarian lev (лв)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BHD'  => array(
			'admin_label' => __( 'Bahraini dinar (.د.ب)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BIF'  => array(
			'admin_label' => __( 'Burundian franc (FBu)', 'give' ),
			'symbol'      => '&#70;&#66;&#117;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BMD'  => array(
			'admin_label' => __( 'Bermudian dollar (BD$)', 'give' ),
			'symbol'      => '&#66;&#68;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BND'  => array(
			'admin_label' => __( 'Brunei dollar (B$)', 'give' ),
			'symbol'      => '&#66;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BOB'  => array(
			'admin_label' => __( 'Bolivian boliviano (Bs.)', 'give' ),
			'symbol'      => '&#66;&#115;&#46;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BSD'  => array(
			'admin_label' => __( 'Bahamian dollar (B$)', 'give' ),
			'symbol'      => '&#66;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BTC'  => array(
			'admin_label' => __( 'Bitcoin (Ƀ)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BTN'  => array(
			'admin_label' => __( 'Bhutanese ngultrum (Nu.)', 'give' ),
			'symbol'      => '&#78;&#117;&#46;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BWP'  => array(
			'admin_label' => __( 'Botswana pula (P)', 'give' ),
			'symbol'      => '&#80;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BYR'  => array(
			'admin_label' => __( 'Belarusian ruble (Br)', 'give' ),
			'symbol'      => '&#66;&#114;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'BZD'  => array(
			'admin_label' => __( 'Belizean dollar (BZ$)', 'give' ),
			'symbol'      => '&#66;&#90;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CDF'  => array(
			'admin_label' => __( 'Congolese franc (FC)', 'give' ),
			'symbol'      => '&#70;&#67;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CLP'  => array(
			'admin_label' => __( 'Chilean peso ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CNY'  => array(
			'admin_label' => __( 'Chinese yuan (¥)', 'give' ),
			'symbol'      => '&yen;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'COP'  => array(
			'admin_label' => __( 'Colombian peso ($)', 'give' ),
			'symbol'      => '&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CRC'  => array(
			'admin_label' => __( 'Costa Rican colón (₡)', 'give' ),
			'symbol'      => '&#8353;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CUC'  => array(
			'admin_label' => __( 'Cuban convertible peso (₱)', 'give' ),
			'symbol'      => '&#8369;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CUP'  => array(
			'admin_label' => __( 'Cuban convertible peso (₱)', 'give' ),
			'symbol'      => '&#8369;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'CVE'  => array(
			'admin_label' => __( 'Cape Verdean escudo (Ecs)', 'give' ),
			'symbol'      => '&#69;&#99;&#115;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'DJP'  => array(
			'admin_label' => __( 'Djiboutian franc (Fdj)', 'give' ),
			'symbol'      => '&#70;&#100;&#106;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'DOP'  => array(
			'admin_label' => __( 'Dominican peso (RD$)', 'give' ),
			'symbol'      => '&#82;&#68;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'DZD'  => array(
			'admin_label' => __( 'Algerian dinar (د.ج)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'EGP'  => array(
			'admin_label' => __( 'Egyptian pound (E£)', 'give' ),
			'symbol'      => '&#69;&pound;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'ERN'  => array(
			'admin_label' => __( 'Eritrean nakfa (Nfa)', 'give' ),
			'symbol'      => '&#78;&#102;&#97;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'ETB'  => array(
			'admin_label' => __( 'Ethiopian birr (Br)', 'give' ),
			'symbol'      => '&#66;&#114;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'FJD'  => array(
			'admin_label' => __( 'Fijian dollar (FJ$)', 'give' ),
			'symbol'      => '&#70;&#74;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'FKP'  => array(
			'admin_label' => __( 'Falkland Islands pound (FK£)', 'give' ),
			'symbol'      => '&#70;&#75;&pound;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GEL'  => array(
			'admin_label' => __( 'Georgian lari (ლ)', 'give' ),
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GGP'  => array(
			'admin_label' => __( 'Guernsey pound (£)', 'give' ),
			'symbol'      => '&pound;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GHS'  => array(
			'admin_label' => __( 'Ghana cedi (₵)', 'give' ),
			'symbol'      => '&#8373;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GIP'  => array(
			'admin_label' => __( 'Gibraltar pound (	£)', 'give' ),
			'symbol'      => '&pound;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GMD'  => array(
			'admin_label' => __( 'Gambian dalasi (D)', 'give' ),
			'symbol'      => '&#68;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GNF'  => array(
			'admin_label' => __( 'Guinean franc (FG)', 'give' ),
			'symbol'      => '&#70;&#71;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GTQ'  => array(
			'admin_label' => __( 'Guatemalan quetzal (Q)', 'give' ),
			'symbol'      => '&#81;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
		'GYD'  => array(
			'admin_label' => __( 'Guyanese dollar (GY$)', 'give' ),
			'symbol'      => '&#71;&#89;&#36;',
			'setting'     => array(
				'currency_position'   => 'before',
				'thousands_separator' => '.',
				'decimal_separator'   => ',',
				'number_decimals'     => 2,
			),
		),
	);

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
	 *
	 * @param array $currencies
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
				$currencies[ $currency_code ] = array(
					'admin_label' => $currency_setting,
				);
			}

			$currencies[ $currency_code ] = wp_parse_args(
				$currencies[ $currency_code ],
				array(
					'admin_label' => '',
					'symbol'      => $currency_code,
					'setting'     => array(),
				)
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
		array_walk( $currencies, function ( &$currency_symbol ) {
			$currency_symbol = html_entity_decode( $currency_symbol, ENT_COMPAT, 'UTF-8' );
		} );
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
function give_currency_filter( $price = '', $args = array() ) {

	// Get functions arguments.
	$func_args = func_get_args();

	// Backward compatibility: modify second param to array
	if ( isset( $func_args[1] ) && is_string( $func_args[1] ) ) {
		$args = array(
			'currency_code'   => isset( $func_args[1] ) ? $func_args[1] : '',
			'decode_currency' => isset( $func_args[2] ) ? $func_args[2] : false,
			'form_id'         => isset( $func_args[3] ) ? $func_args[3] : '',
		);
	}

	// Set default values.
	$args = wp_parse_args(
		$args,
		array(
			'currency_code'   => '',
			'decode_currency' => false,
			'form_id'         => '',
		)
	);

	if ( empty( $args['currency_code'] ) || ! array_key_exists( (string) $args['currency_code'], give_get_currencies() ) ) {
		$args['currency_code'] = give_get_currency( $args['form_id'] );
	}

	$args['position'] = give_get_option( 'currency_position', 'before' );

	$negative = $price < 0;

	if ( $negative ) {
		// Remove proceeding "-".
		$price = substr( $price, 1 );
	}

	$args['symbol'] = give_currency_symbol( $args['currency_code'], $args['decode_currency'] );

	switch ( $args['currency_code'] ) :
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
		case 'IRR' :
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
 * Zero Decimal based Currency.
 *
 * @since 1.8.14
 * @see   https://github.com/WordImpress/Give/issues/2191
 *
 * @param string $currency Currency code
 *
 * @return bool
 */
function give_is_zero_based_currency( $currency = '' ) {
	$zero_based_currency = array(
		'PYG', // Paraguayan Guarani.
		'GNF', // Guinean Franc.
		'RWF', // Rwandan Franc.
		'JPY', // Japanese Yen.
		'BIF', // Burundian Franc.
		'KRW', // South Korean Won.
		'MGA', // Malagasy Ariary.
		'XAF', // Central African Cfa Franc.
		'XPF', // Cfp Franc.
		'CLP', // Chilean Peso.
		'KMF', // Comorian Franc.
		'DJF', // Djiboutian Franc.
		'VUV', // Vanuatu Vatu.
		'VND', // Vietnamese Dong.
		'XOF', // West African Cfa Franc.
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
		array(
			'IRR',
			'RIAL',
		)
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