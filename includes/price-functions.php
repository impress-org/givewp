<?php
/**
 * Price Functions
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, Pippin WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks to see if a Give form has variable prices enabled.
 *
 * @since 1.0
 *
 * @param int $form_id ID number of the form to check
 *
 * @return bool true if has variable prices, false otherwise
 */
function give_has_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->has_variable_prices();
}


/**
 * Retrieves the variable prices for a form
 *
 * @since 1.0
 *
 * @param int $form_id ID of the Give form
 *
 * @return array Variable prices
 */
function give_get_variable_prices( $form_id = 0 ) {

	if ( empty( $form_id ) ) {
		return false;
	}

	$form = new Give_Donate_Form( $form_id );

	return $form->prices;

}
