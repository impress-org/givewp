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

	// Get publishable key.
	$publishable_key = give_stripe_get_publishable_key();

	// Checkout options.
	// @TODO: convert checkboxes to radios.
	$zip_option      = give_is_setting_enabled( give_get_option( 'stripe_checkout_zip_verify' ) );
	$remember_option = give_is_setting_enabled( give_get_option( 'stripe_checkout_remember_me' ) );

	$stripe_card_update = give_stripe_is_update_payment_method_screen();

	// Set vars for AJAX.
	$stripe_vars = array(
		'zero_based_currency'          => give_is_zero_based_currency(),
		'zero_based_currencies_list'   => give_get_zero_based_currencies(),
		'sitename'                     => give_get_option( 'stripe_checkout_name' ),
		'publishable_key'              => $publishable_key,
		'checkout_image'               => give_get_option( 'stripe_checkout_image' ),
		'checkout_address'             => give_get_option( 'stripe_collect_billing' ),
		'checkout_processing_text'     => give_get_option( 'stripe_checkout_processing_text', __( 'Donation Processing...', 'give' ) ),
		'zipcode_option'               => $zip_option,
		'remember_option'              => $remember_option,
		'give_version'                 => get_option( 'give_version' ),
		'cc_fields_format'             => give_get_option( 'stripe_cc_fields_format', 'multi' ),
		'card_number_placeholder_text' => __( 'Card Number', 'give' ),
		'card_cvc_placeholder_text'    => __( 'CVC', 'give' ),
		'donate_button_text'           => __( 'Donate Now', 'give' ),
		'element_font_styles'          => give_stripe_get_element_font_styles(),
		'element_base_styles'          => give_stripe_get_element_base_styles(),
		'element_complete_styles'      => give_stripe_get_element_complete_styles(),
		'element_empty_styles'         => give_stripe_get_element_empty_styles(),
		'element_invalid_styles'       => give_stripe_get_element_invalid_styles(),
		'float_labels'                 => give_is_float_labels_enabled(
			array(
				'form_id' => get_the_ID(),
			)
		),
		'base_country'                 => give_get_option( 'base_country' ),
		'stripe_card_update'           => $stripe_card_update,
		'stripe_account_id'            => give_stripe_is_connected() ? give_get_option( 'give_stripe_user_id' ) : false,
		'preferred_locale'             => give_stripe_get_preferred_locale(),
	);

	// Load third-party stripe js when required gateways are active.
	if ( apply_filters( 'give_stripe_js_loading_conditions', give_stripe_is_any_payment_method_active() ) ) {
		Give_Scripts::register_script( 'give-stripe-js', 'https://js.stripe.com/v3/', array(), GIVE_VERSION );
		wp_enqueue_script( 'give-stripe-js' );
		wp_localize_script( 'give-stripe-js', 'give_stripe_vars', $stripe_vars );
	}

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
	}

	// Load Stripe onpage credit card JS when Stripe credit card payment method is active.
	if ( give_is_gateway_active( 'stripe' ) || $stripe_card_update ) {
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
