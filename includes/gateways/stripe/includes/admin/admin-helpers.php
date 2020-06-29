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
	$settings             = give_get_settings();
	$gateways             = isset( $settings['gateways'] ) ? $settings['gateways'] : [];
	$stripePaymentMethods = give_stripe_supported_payment_methods();

	// Loop through gateways list.
	foreach ( array_keys( $gateways ) as $gateway ) {

		// Return true, if even single payment method is active.
		if ( in_array( $gateway, $stripePaymentMethods, true ) ) {
			return true;
		}
	}

	return false;
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
		[
			'stripe_action'         => 'connect',
			'mode'                  => give_is_test_mode() ? 'test' : 'live',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'give_stripe_connected' => '0',
		],
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
		'stripe_user_id' => $account_id,
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
