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
 * @deprecated 2.5.0
 *
 * @return bool
 */
function give_is_stripe_connected() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.5.0', 'give_stripe_is_connected', $backtrace );

	return give_stripe_is_connected();
}

/**
 * This function is used to get the connect options.
 *
 * @since      1.0.0
 * @deprecated 2.5.0
 *
 * @return array
 */
function get_give_stripe_connect_options() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.5.0', 'give_stripe_get_connect_settings', $backtrace );

	return give_stripe_get_connect_settings();
}

/**
 * This function is used to get stripe statement descriptor.
 *
 * @param \Stripe\Subscription $subscription Subscription object from Stripe.
 *
 * @since      1.0.0
 * @deprecated 2.5.0
 *
 * @return string
 */
function give_get_stripe_statement_descriptor( $subscription ) {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.5.0', 'give_stripe_get_statement_descriptor', $backtrace );

	return give_stripe_get_statement_descriptor( $subscription );

}

/**
 * This function is used to check whether Stripe checkout is enabled or not.
 *
 * @since      1.0.0
 * @deprecated 2.5.0
 *
 * @return bool
 */
function give_is_stripe_checkout_enabled() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.5.0', 'give_stripe_is_checkout_enabled', $backtrace );

	return give_stripe_is_checkout_enabled();
}

/**
 * This function is used to fetch the connect options for Stripe.
 *
 * @since      1.0.0
 * @deprecated 2.5.0
 *
 * @return bool
 */
if ( ! function_exists( 'get_give_stripe_connect_options' ) ) {
	function get_give_stripe_connect_options() {

		$backtrace = debug_backtrace();

		_give_deprecated_function( __FUNCTION__, '2.5.0', 'give_stripe_get_connect_settings', $backtrace );

		return give_stripe_get_connect_settings();
	}
}
