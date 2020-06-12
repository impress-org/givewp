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

/**
 * This function is used to fetch the connect options for Stripe.
 *
 * @since      2.5.0
 * @deprecated 2.7.0
 *
 * @return bool
 */
if ( ! function_exists( 'give_stripe_get_connect_settings' ) ) {
	function give_stripe_get_connect_settings() {

		$backtrace = debug_backtrace();

		_give_deprecated_function( __FUNCTION__, '2.7.0', 'give_stripe_get_connect_settings', $backtrace );

		$options = [
			'connected_status'     => give_get_option( 'give_stripe_connected' ),
			'user_id'              => give_get_option( 'give_stripe_user_id' ),
			'access_token'         => give_get_option( 'live_secret_key' ),
			'access_token_test'    => give_get_option( 'test_secret_key' ),
			'publishable_key'      => give_get_option( 'live_publishable_key' ),
			'publishable_key_test' => give_get_option( 'test_publishable_key' ),
		];

		/**
		 * This filter hook is used to override the existing stripe connect settings stored in DB.
		 *
		 * @param array $options List of Stripe Connect settings required to make functionality work.
		 *
		 * @since 2.5.0
		 */
		return apply_filters( 'give_stripe_get_connect_settings', $options );
	}
}

/**
 * Delete all the Give settings options for Stripe Connect.
 *
 * @since 2.5.0
 * @deprecated 2.7.0
 *
 * @return void
 */
function give_stripe_connect_delete_options() {
	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.7.0', 'give_stripe_connect_delete_options', $backtrace );

	// Disconnection successful.
	// Remove the connect options within the db.
	give_delete_option( 'give_stripe_connected' );
	give_delete_option( 'give_stripe_user_id' );
	give_delete_option( 'live_secret_key' );
	give_delete_option( 'test_secret_key' );
	give_delete_option( 'live_publishable_key' );
	give_delete_option( 'test_publishable_key' );
}

/**
 * Checks whether Stripe is connected or not.
 *
 * @since 2.5.0
 * @deprecated 2.7.0
 *
 * @return bool
 */
function give_stripe_is_connected() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.7.0', 'give_stripe_is_connected', $backtrace );

	$settings = give_stripe_get_connect_settings();

	$user_api_keys_enabled = give_is_setting_enabled( give_get_option( 'stripe_user_api_keys' ) );

	// Return false, if manual API keys are used to configure Stripe.
	if ( $user_api_keys_enabled ) {
		return false;
	}

	// Check all the necessary options.
	if (
		! empty( $settings['connected_status'] ) && '1' === $settings['connected_status']
		&& ! empty( $settings['user_id'] )
		&& ! empty( $settings['access_token'] )
		&& ! empty( $settings['access_token_test'] )
		&& ! empty( $settings['publishable_key'] )
		&& ! empty( $settings['publishable_key_test'] )
	) {
		return true;
	}

	// Default return value.
	return false;
}

 /** Is Stripe Checkout Enabled?
 *
 * @since 2.5.0
 * @deprecated 2.6.4
 *
 * @return bool
 */
function give_stripe_is_checkout_enabled() {

	$backtrace = debug_backtrace();

	_give_deprecated_function( __FUNCTION__, '2.6.4', 'give_stripe_is_checkout_enabled', $backtrace );

	return give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled', 'disabled' ) );
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet.
 * Note: We are not changing @since and @deprecated as we moved this fn from Stripe Premium.
 *
 * @since  1.4
 * @deprecated 2.1
 *
 * @param  int $user_id_or_email The user ID or email to look up.
 *
 * @return string       Stripe customer ID.
 */
if ( ! function_exists( 'give_get_stripe_customer_id' ) ) {
	$stripeVersion  = defined( 'GIVE_STRIPE_VERSION' ) ? GIVE_STRIPE_VERSION : '';
	$isValidVersion = version_compare( $stripeVersion, '2.2.6', '>=' );

	if ( $isValidVersion ) {
		function give_get_stripe_customer_id( $user_id_or_email ) {
			$backtrace = debug_backtrace();

			_give_deprecated_function( __FUNCTION__, '2.7.0', 'give_stripe_get_customer_id', $backtrace );

			return give_stripe_get_customer_id( $user_id_or_email );
		}
	}
}
