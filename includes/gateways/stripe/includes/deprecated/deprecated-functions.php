<?php
/**
 * Give - Stripe Core | Deprecated Functions
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function will check whether the Stripe account is connected via Connect button or not.
 *
 * @since      1.0.0
 * @deprecated 2.2.0
 *
 * @return void
 */
function give_is_stripe_connected() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.2.0', 'give_stripe_is_connected', $backtrace );

	give_stripe_is_connected();
}
