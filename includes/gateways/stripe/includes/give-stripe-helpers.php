<?php
/**
 * Give - Stripe Core Helpers
 *
 * @since 2.5.0
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
 * This function is used to fetch the secret key based on the test mode status.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_get_secret_key() {

	$secret_key = trim( give_get_option( 'live_secret_key' ) );

	// Update secret key, if test mode is enabled.
	if ( give_is_test_mode() ) {
		$secret_key = trim( give_get_option( 'test_secret_key' ) );
	}

	return $secret_key;
}

/**
 * Is Pre-approved Enabled?
 *
 * @since 2.5.0
 *
 * @return bool
 */
function give_stripe_is_preapprove_enabled() {
	return give_is_setting_enabled( give_get_option( 'stripe_preapprove_only', 'disabled' ) );
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
 * Get Settings for the Stripe account connected via Connect API.
 *
 * @since 2.5.0
 *
 * @return mixed
 */
function give_stripe_get_connect_settings() {

	$options = array(
		'connected_status'     => give_get_option( 'give_stripe_connected' ),
		'user_id'              => give_get_option( 'give_stripe_user_id' ),
		'access_token'         => give_get_option( 'live_secret_key' ),
		'access_token_test'    => give_get_option( 'test_secret_key' ),
		'publishable_key'      => give_get_option( 'live_publishable_key' ),
		'publishable_key_test' => give_get_option( 'test_publishable_key' ),
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
 * Is Stripe connected using Connect API?
 *
 * @since 2.5.0
 *
 * @return bool
 */
function give_stripe_is_connected() {

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

/**
 * This function will return connected account options.
 *
 * @since 2.5.0
 *
 * @return array
 */
function give_stripe_get_connected_account_options() {

	$args = array();

	if ( give_is_stripe_connected() ) {
		$args['stripe_account'] = give_get_option( 'give_stripe_user_id' );
	}

	return $args;
}

/**
 * Displays Stripe Connect Button.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_connect_button() {

	$connected = give_get_option( 'give_stripe_connected' );

	// Prepare Stripe Connect URL.
	$link = add_query_arg(
		array(
			'stripe_action'         => 'connect',
			'mode'                  => give_is_test_mode() ? 'test' : 'live',
			'return_url'            => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
			'website_url'           => get_bloginfo( 'url' ),
			'give_stripe_connected' => ! empty( $connected ) ? '1' : '0',
		),
		esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);

	echo sprintf(
		'<a href="%1$s" id="give-stripe-connect"><span>%2$s</span></a>',
		esc_url( $link ),
        esc_html__( 'Connect with Stripe', 'give' )
	);
}

/**
 * Stripe Disconnect URL.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_disconnect_url() {

	// Prepare Stripe Disconnect URL.
	$link = add_query_arg(
		array(
			'stripe_action'  => 'disconnect',
			'mode'           => give_is_test_mode() ? 'test' : 'live',
			'stripe_user_id' => give_get_option( 'give_stripe_user_id' ),
			'return_url'     => rawurlencode( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings' ) ),
		),
        esc_url_raw( 'https://connect.givewp.com/stripe/connect.php' )
	);

	echo esc_url( $link );
}

/**
 * Get Publishable Key.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_publishable_key() {

	$publishable_key = give_get_option( 'live_publishable_key' );

	if ( give_is_test_mode() ) {
		$publishable_key = give_get_option( 'test_publishable_key' );
	}

	return $publishable_key;
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
 * This function will prepare JSON for default base styles.
 *
 * @since 2.5.0
 *
 * @return mixed|string
 */
function give_stripe_get_default_base_styles() {

	$float_labels = give_is_float_labels_enabled(
		array(
			'form_id' => get_the_ID(),
		)
	);

	return wp_json_encode(
		array(
			'color'             => '#32325D',
			'fontWeight'        => 500,
			'fontSize'          => '16px',
			'fontSmoothing'     => 'antialiased',
			'::placeholder'     => array(
				'color' => $float_labels ? '#CCCCCC' : '#222222',
			),
			':-webkit-autofill' => array(
				'color' => '#e39f48',
			),
		)
	);
}

/**
 * This function is used to get the stripe styles.
 *
 * @since 2.5.0
 *
 * @return mixed
 */
function give_stripe_get_stripe_styles() {

	$default_styles = array(
		'base'     => give_stripe_get_default_base_styles(),
		'empty'    => false,
		'invalid'  => false,
		'complete' => false,
	);

	return give_get_option( 'stripe_styles', $default_styles );
}

/**
 * Get Base Styles for Stripe Elements CC Fields.
 *
 * @since 2.5.0
 *
 * @return object
 */
function give_stripe_get_element_base_styles() {

	$stripe_styles = give_stripe_get_stripe_styles();
	$base_styles   = json_decode( $stripe_styles['base'] );

	return (object) apply_filters( 'give_stripe_get_element_base_styles', $base_styles );
}

/**
 * Get Complete Styles for Stripe Elements CC Fields.
 *
 * @since 2.5.0
 *
 * @return object
 */
function give_stripe_get_element_complete_styles() {

	$stripe_styles   = give_stripe_get_stripe_styles();
	$complete_styles = json_decode( $stripe_styles['complete'] );

	return (object) apply_filters( 'give_stripe_get_element_complete_styles', $complete_styles );
}

/**
 * Get Invalid Styles for Stripe Elements CC Fields.
 *
 * @since 2.5.0
 *
 * @return object
 */
function give_stripe_get_element_invalid_styles() {

	$stripe_styles  = give_stripe_get_stripe_styles();
	$invalid_styles = json_decode( $stripe_styles['invalid'] );

	return (object) apply_filters( 'give_stripe_get_element_invalid_styles', $invalid_styles );
}

/**
 * Get Empty Styles for Stripe Elements CC Fields.
 *
 * @since 2.5.0
 *
 * @return object
 */
function give_stripe_get_element_empty_styles() {

	$stripe_styles = give_stripe_get_stripe_styles();
	$empty_styles  = json_decode( $stripe_styles['empty'] );

	return (object) apply_filters( 'give_stripe_get_element_empty_styles', $empty_styles );
}

/**
 * Get Stripe Element Font Styles.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_element_font_styles() {

	$font_styles  = '';
	$stripe_fonts = give_get_option( 'stripe_fonts', 'google_fonts' );

	if ( 'custom_fonts' === $stripe_fonts ) {
		$custom_fonts_attributes = give_get_option( 'stripe_custom_fonts' );
		$font_styles = json_decode( $custom_fonts_attributes );
	} else {
		$font_styles = array(
			'cssSrc' => give_get_option( 'stripe_google_fonts_url' ),
		);
	}

	if ( empty( $font_styles ) ) {
		$font_styles = array();
	}

	return apply_filters( 'give_stripe_get_element_font_styles', $font_styles );

}
