<?php
/**
 * Forms Admin Functions
 *
 * @package     Give
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Retrieves a price from from low to high of a variable priced form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the form
 *
 * @return string $range A fully formatted price range
 */
function give_price_range( $form_id = 0 ) {
	$low   = give_get_lowest_price_option( $form_id );
	$high  = give_get_highest_price_option( $form_id );
	$range = '<span class="give_price_range_low">' . give_currency_filter( give_format_amount( $low ) ) . '</span>';
	$range .= '<span class="give_price_range_sep">&nbsp;&ndash;&nbsp;</span>';
	$range .= '<span class="give_price_range_high">' . give_currency_filter( give_format_amount( $high ) ) . '</span>';

	return apply_filters( 'give_price_range', $range, $form_id, $low, $high );
}


/**
 * Retrieves cheapest price option of a variable priced form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the form
 *
 * @return float Amount of the lowest price
 */
function give_get_lowest_price_option( $form_id = 0 ) {
	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( ! give_has_variable_prices( $form_id ) ) {
		return give_get_form_price( $form_id );
	}

	$prices = give_get_variable_prices( $form_id );

	$low = 0.00;

	if ( ! empty( $prices ) ) {

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['_give_amount'] ) ) {
				continue;
			}

			if ( ! isset( $min ) ) {
				$min = $price['_give_amount'];
			} else {
				$min = min( $min, $price['_give_amount'] );
			}

			if ( $price['_give_amount'] == $min ) {
				$min_id = $key;
			}
		}

		$low = $prices[ $min_id ]['_give_amount'];

	}

	return give_sanitize_amount( $low );
}

/**
 * Retrieves most expensive price option of a variable priced form
 *
 * @since 1.4.4
 *
 * @param int $form_id ID of the form
 *
 * @return float Amount of the highest price
 */
function give_get_highest_price_option( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( ! give_has_variable_prices( $form_id ) ) {
		return give_get_form_price( $form_id );
	}

	$prices = give_get_variable_prices( $form_id );

	$high = 0.00;

	if ( ! empty( $prices ) ) {

		$max = 0;

		foreach ( $prices as $key => $price ) {

			if ( empty( $price['_give_amount'] ) ) {
				continue;
			}

			$max = max( $max, $price['_give_amount'] );

			if ( $price['_give_amount'] == $max ) {
				$max_id = $key;
			}
		}

		$high = $prices[ $max_id ]['_give_amount'];
	}

	return give_sanitize_amount( $high );
}

/**
 * Returns the price of a form, but only for non-variable priced forms.
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to retrieve a price for
 *
 * @return mixed string|int Price of the form
 */
function give_get_form_price( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->price;
}


/**
 * Displays a formatted price for a donation form
 *
 * @since 1.0
 *
 * @param int  $form_id  ID of the form price to show
 * @param bool $echo     Whether to echo or return the results
 * @param int  $price_id Optional price id for variable pricing
 *
 * @return void
 */
function give_price( $form_id = 0, $echo = true, $price_id = false ) {

	if ( empty( $form_id ) ) {
		$form_id = get_the_ID();
	}

	if ( give_has_variable_prices( $form_id ) ) {

		$prices = give_get_variable_prices( $form_id );

		if ( false !== $price_id ) {

			//loop through multi-prices to see which is default
			foreach ( $prices as $price ) {
				//this is the default price
				if ( isset( $price['_give_default'] ) && $price['_give_default'] === 'default' ) {
					$price = (float) $price['_give_amount'];
				};
			}

		} else {

			$price = give_get_lowest_price_option( $form_id );
		}

		$price = give_sanitize_amount( $price );

	} else {

		$price = give_get_form_price( $form_id );

	}

	$price           = apply_filters( 'give_form_price', give_sanitize_amount( $price ), $form_id );
	$formatted_price = '<span class="give_price" id="give_price_' . $form_id . '">' . $price . '</span>';
	$formatted_price = apply_filters( 'give_form_price_after_html', $formatted_price, $form_id, $price );

	if ( $echo ) {
		echo $formatted_price;
	} else {
		return $formatted_price;
	}
}

add_filter( 'give_form_price', 'give_format_amount', 10 );
add_filter( 'give_form_price', 'give_currency_filter', 20 );


/**
 * Retrieves the amount of a variable price option
 *
 * @since 1.0
 *
 * @param int $form_id  ID of the form
 * @param int $price_id ID of the price option
 * @param     int       @payment_id ID of the payment
 *
 * @return float $amount Amount of the price option
 */
function give_get_price_option_amount( $form_id = 0, $price_id = 0 ) {
	$prices = give_get_variable_prices( $form_id );
	$amount = 0.00;

	foreach ( $prices as $price ) {
		if ( isset( $price['_give_id']['level_id'] ) && $price['_give_id']['level_id'] === $price_id ) {
			$amount = $price['_give_amount'];
		};
	}

	return apply_filters( 'give_get_price_option_amount', give_sanitize_amount( $amount ), $form_id, $price_id );
}