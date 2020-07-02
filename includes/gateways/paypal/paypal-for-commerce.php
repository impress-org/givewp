<?php
/**
 * PayPal for Commerce
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2020, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Get a PayPal REST access token.
 *
 * Required to make API calls.
 *
 * @see: https://developer.paypal.com/docs/api/get-an-access-token-curl/
 */
function give_get_paypal_access_token() {

	$url = 'https://api.sandbox.paypal.com';

	if ( give_is_test_mode() ) {
		$url = 'https://api.sandbox.paypal.com';
	}


}
