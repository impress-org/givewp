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
 * @since 2.27.0 add filter for the array of supported stripe gateways.
 * @since 2.5.5
 *
 * @return array
 */
function give_stripe_supported_payment_methods()
{
    return apply_filters('give_stripe_supported_payment_methods', [
        'stripe',
        'stripe_ach',
        'stripe_ideal',
        'stripe_google_pay',
        'stripe_apple_pay',
        'stripe_checkout',
        'stripe_sepa',
        'stripe_becs',
    ]);
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
