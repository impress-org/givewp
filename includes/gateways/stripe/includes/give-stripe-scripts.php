<?php
/**
 * Give - Stripe Scripts
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Frontend javascript
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_frontend_scripts() {

	// Set vars for AJAX.
	$stripe_vars = give_stripe_get_localize_vars();

	// Load legacy Stripe checkout when the checkout type is `modal`.
	if ( 'modal' === give_stripe_get_checkout_type() ) {

		// Stripe checkout js.
		Give_Scripts::register_script( 'give-stripe-checkout-js', 'https://checkout.stripe.com/checkout.js', array( 'jquery' ), GIVE_VERSION );
		wp_enqueue_script( 'give-stripe-checkout-js' );

		$deps = array(
			'jquery',
			'give',
			'give-stripe-checkout-js',
		);

		// Give Stripe Checkout JS.
		Give_Scripts::register_script( 'give-stripe-popup-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-stripe-checkout.js', $deps, GIVE_VERSION );
		wp_enqueue_script( 'give-stripe-popup-js' );
		wp_localize_script( 'give-stripe-popup-js', 'give_stripe_vars', $stripe_vars );

		return;
	}

	// Load third-party stripe js when required gateways are active.
	if ( apply_filters( 'give_stripe_js_loading_conditions', give_stripe_is_any_payment_method_active() ) ) {
		Give_Scripts::register_script( 'give-stripe-js', 'https://js.stripe.com/v3/', array(), GIVE_VERSION );
		wp_enqueue_script( 'give-stripe-js' );
		wp_localize_script( 'give-stripe-js', 'give_stripe_vars', $stripe_vars );
	}

	// Load Stripe onpage credit card JS when Stripe credit card payment method is active.
	if ( give_is_gateway_active( 'stripe' ) ) {
		Give_Scripts::register_script( 'give-stripe-onpage-js', GIVE_PLUGIN_URL . 'assets/dist/js/give-stripe.js', array( 'give-stripe-js' ), GIVE_VERSION );
		wp_enqueue_script( 'give-stripe-onpage-js' );
	}
}

add_action( 'wp_enqueue_scripts', 'give_stripe_frontend_scripts' );

/**
 * WooCommerce checkout compatibility.
 *
 * This prevents Give from outputting scripts on Woo's checkout page.
 *
 * @since 1.4.3
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function give_stripe_woo_script_compatibility( $ret ) {

	if (
		function_exists( 'is_checkout' )
		&& is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'give_stripe_js_loading_conditions', 'give_stripe_woo_script_compatibility', 10, 1 );


/**
 * EDD checkout compatibility.
 *
 * This prevents Give from outputting scripts on EDD's checkout page.
 *
 * @since 1.4.6
 *
 * @param bool $ret JS compatibility status.
 *
 * @return bool
 */
function give_stripe_edd_script_compatibility( $ret ) {

	if (
		function_exists( 'edd_is_checkout' )
		&& edd_is_checkout()
	) {
		return false;
	}

	return $ret;

}

add_filter( 'give_stripe_js_loading_conditions', 'give_stripe_edd_script_compatibility', 10, 1 );
