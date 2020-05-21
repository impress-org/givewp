<?php
/**
 * Give - Stripe Core Admin Helper Functions.
 *
 * @since 2.5.4
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function is used to get a list of slug which are supported by payment gateways.
 *
 * @since 2.5.5
 *
 * @return array
 */
function give_stripe_supported_payment_methods() {
	return [
		'stripe',
		'stripe_ach',
		'stripe_ideal',
		'stripe_google_pay',
		'stripe_apple_pay',
		'stripe_checkout',
		'stripe_sepa',
		'stripe_becs',
	];
}

/**
 * This function is used to check whether a payment method supported by Stripe with Give is active or not.
 *
 * @since 2.5.5
 *
 * @return bool
 */
function give_stripe_is_any_payment_method_active() {

	// Get settings.
	$settings = give_get_settings();
	$gateways = isset( $settings['gateways'] ) ? $settings['gateways'] : array();

	// Loop through gateways list.
	foreach ( array_keys( $gateways ) as $gateway ) {

		// Return true, if even single payment method is active.
		if ( in_array( $gateway, give_stripe_supported_payment_methods(), true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Get Settings for the Stripe account connected via Connect API.
 *
 * @since 2.5.0
 *
 * @return mixed
 */
function give_stripe_get_connect_settings() {

	$options = array(
		'type'                 => 'connect',
		'connected_status'     => give_get_option( 'give_stripe_connected' ),
		'give_stripe_user_id'  => give_get_option( 'give_stripe_user_id' ),
		'live_secret_key'      => give_get_option( 'live_secret_key' ),
		'test_secret_key'      => give_get_option( 'test_secret_key' ),
		'live_publishable_key' => give_get_option( 'live_publishable_key' ),
		'test_publishable_key' => give_get_option( 'test_publishable_key' ),
	);

	/**
	 * This filter hook is used to override the existing stripe connect settings stored in DB.
	 *
	 * @param array $options List of Stripe Connect settings required to make functionality work.
	 *
	 * @since 2.5.0
	 */
	return apply_filters( 'give_stripe_get_connect_settings', $options );
}

/**
 * Is Stripe Checkout Enabled?
 *
 * @since 2.5.0
 *
 * @return bool
 */
function give_stripe_is_checkout_enabled() {
	return give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled', 'disabled' ) );
}

/**
 * Displays Stripe Connect Button.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_connect_button() {
	// Prepare Stripe Connect URL.
	$link = add_query_arg(
		array(
			'stripe_action'         => 'connect',
			'mode'                  => give_is_test_mode() ? 'test' : 'live',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'give_stripe_connected' => '0',
		),
		esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);

	return sprintf(
		'<a href="%1$s" id="give-stripe-connect"><span>%2$s</span></a>',
		esc_url( $link ),
		esc_html__( 'Connect with Stripe', 'give' )
	);
}

/**
 * Stripe Disconnect URL.
 *
 * @param string $account_id   Stripe Account ID.
 * @param string $account_name Stripe Account Name.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_disconnect_url( $account_id = '', $account_name = '' ) {

	$args = [
		'stripe_action'  => 'disconnect',
		'mode'           => give_is_test_mode() ? 'test' : 'live',
		'stripe_user_id' => ! empty( $account_id ) ? $account_id : give_get_option( 'give_stripe_user_id' ),
		'return_url'     => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
	];

	// Send Account Name.
	if ( ! empty( $account_name ) ) {
		$args['account_name'] = $account_name;
	}

	// Prepare Stripe Disconnect URL.
	return add_query_arg(
		$args,
		esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);
}

/**
 * Delete all the Give settings options for Stripe Connect.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_connect_delete_options() {

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
 * This helper function is used to convert slug into name of Stripe connection.
 *
 * @param string $slug Connection Type Slug.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_connection_type_name( $slug = 'connect' ) {

	$names = [
		'manual'  => esc_html__( 'API Keys', 'give' ),
		'connect' => esc_html__( 'Stripe Connect', 'give' ),
	];

	return $names[ $slug ];
}
