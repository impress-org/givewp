<?php
/**
 * Front-end Filters
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Add backward compatibility for settings who has disable_ as name prefix.
 * TODO: Remove this backward compatibility when do not need.
 *
 * @since  1.8
 *
 * @param  array $old_settings Array of settings.
 * @param  array $settings     Array of settings.
 *
 * @return void
 */
function give_set_settings_with_disable_prefix( $old_settings, $settings ) {
	// Bailout.
	if ( ! function_exists( 'give_v18_renamed_core_settings' ) ) {
		return;
	}

	// Get old setting names.
	$old_settings   = array_flip( give_v18_renamed_core_settings() );
	$update_setting = false;

	foreach ( $settings as $key => $value ) {

		// Check 1. Check if new option is really updated or not.
		// Check 2. Continue if key is not renamed.
		if ( ! isset( $old_settings[ $key ] ) ) {
			continue;
		}

		// Set old setting.
		$settings[ $old_settings[ $key ] ] = 'on';

		// Do not need to set old setting if new setting is not set.
		if (
			( give_is_setting_enabled( $value ) && ( false !== strpos( $old_settings[ $key ], 'disable_' ) ) )
			|| ( ! give_is_setting_enabled( $value ) && ( false !== strpos( $old_settings[ $key ], 'enable_' ) ) )

		) {
			unset( $settings[ $old_settings[ $key ] ] );
		}

		// Tell bot to update setting.
		$update_setting = true;
	}

	// Update setting if any old setting set.
	if ( $update_setting ) {
		update_option( 'give_settings', $settings, false );
	}
}

add_action( 'update_option_give_settings', 'give_set_settings_with_disable_prefix', 10, 2 );

/**
 * Check spam through Akismet.
 *
 * It will build Akismet query string and call Akismet API.
 * Akismet response return 'true' for spam donation.
 *
 * @since 1.8.14
 *
 * @param $spam
 *
 * @return bool|mixed
 */
function give_akismet( $spam ) {

	// Bail out, If spam.
	if ( $spam ) {
		return $spam;
	}

	// Bail out, if Akismet key not exist.
	if ( ! give_check_akismet_key() ) {
		return false;
	}

	// Build args array.
	$args = array();

	$args['comment_author']       = isset( $_POST['give_first'] ) ? give_clean( $_POST['give_first'] ) : '';
	$args['comment_author_email'] = isset( $_POST['give_email'] ) ? sanitize_email( $_POST['give_email'] ) : false;
	$args['blog']                 = get_option( 'home' );
	$args['blog_lang']            = get_locale();
	$args['blog_charset']         = get_option( 'blog_charset' );
	$args['user_ip']              = $_SERVER['REMOTE_ADDR'];
	$args['user_agent']           = $_SERVER['HTTP_USER_AGENT'];
	$args['referrer']             = $_SERVER['HTTP_REFERER'];
	$args['comment_type']         = 'contact-form';

	$form_id = isset( $_POST['give-form-id'] ) ? absint( $_POST['give-form-id'] ) : 0;

	// Pass Donor comment if enabled.
	if ( give_is_donor_comment_field_enabled( $form_id ) ) {
		$give_comment = isset( $_POST['give_comment'] ) ? give_clean( $_POST['give_comment'] ) : '';

		$args['comment_content'] = $give_comment;
	}

	$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );

	foreach ( $_SERVER as $key => $value ) {
		if ( ! in_array( $key, (array) $ignore ) ) {
			$args["$key"] = $value;
		}
	}

	// It will return Akismet spam detect API response.
	return give_akismet_spam_check( $args );

}

add_filter( 'give_spam', 'give_akismet' );

/**
 * Check Akismet API Key.
 *
 * @since 1.8.14
 *
 * @return bool
 */
function give_check_akismet_key() {
	if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) { // Akismet v3.0+
		return (bool) Akismet::get_api_key();
	}

	if ( function_exists( 'akismet_get_key' ) ) {
		return (bool) akismet_get_key();
	}

	return false;
}

/**
 * Detect spam through Akismet Comment API.
 *
 * @since 1.8.14
 *
 * @param array $args
 *
 * @return bool|mixed
 */
function give_akismet_spam_check( $args ) {
	global $akismet_api_host, $akismet_api_port;

	$spam         = false;
	$query_string = http_build_query( $args );

	if ( is_callable( array( 'Akismet', 'http_post' ) ) ) { // Akismet v3.0+
		$response = Akismet::http_post( $query_string, 'comment-check' );
	} else {
		$response = akismet_http_post( $query_string, $akismet_api_host,
			'/1.1/comment-check', $akismet_api_port );
	}

	// It's spam if response status is true.
	if ( 'true' === $response[1] ) {
		$spam = true;
	}

	// Allow developer to modified Akismet spam detection response.
	return apply_filters( 'give_akismet_spam_check', $spam, $args );
}

/**
 * Add support of RIAL code for backward compatibility.
 * Note: for internal use only
 *
 * @since 1.8.17
 *
 * @param array $currencies
 *
 * @return array
 */
function give_bc_v1817_iranian_currency_code( $currencies ) {
	$currencies['RIAL'] = $currencies['IRR'];

	return $currencies;
}

if ( ! give_has_upgrade_completed( 'v1817_update_donation_iranian_currency_code' ) ) {
	add_filter( 'give_currencies', 'give_bc_v1817_iranian_currency_code', 0 );
}


/**
 * Format right to left supported currency amount.
 *
 * @since 1.8.17
 *
 * @param $formatted_amount
 * @param $currency_args
 * @param $price
 *
 * @return string
 */
function give_format_price_for_right_to_left_supported_currency( $formatted_amount, $currency_args, $price ) {
	if ( ! give_is_right_to_left_supported_currency( $currency_args['currency_code'] ) ) {
		return $formatted_amount;
	}

	$formatted_amount = (
	'before' === (string) $currency_args['position'] ?
		'&#x202B;' . $price . $currency_args['symbol'] . '&#x202C;' :
		'&#x202A;' . $price . $currency_args['symbol'] . '&#x202C;'
	);

	$formatted_amount = $currency_args['decode_currency'] ?
		html_entity_decode( $formatted_amount, ENT_COMPAT, 'UTF-8' ) :
		$formatted_amount;

	return $formatted_amount;
}

add_filter( 'give_currency_filter', 'give_format_price_for_right_to_left_supported_currency', 10, 3 );

/**
 * Validate active gateway value before returning result.
 *
 * @since 2.1.0
 *
 * @param $value
 *
 * @return array
 */
function __give_validate_active_gateways( $value ) {
	$gateways = array_keys( give_get_payment_gateways() );
	$active_gateways = is_array( $value ) ? array_keys( $value ) : array();

	// Remove deactivated payment gateways.
	if( ! empty( $active_gateways ) ) {
		foreach ( $active_gateways as $index => $gateway_id ) {
			if( ! in_array( $gateway_id, $gateways ) ) {
				unset( $value[$gateway_id] );
			}
		}
	}

	if ( empty( $value ) ) {
		/**
		 * Filter the default active gateway
		 *
		 * @since 2.1.0
		 */
		$value = apply_filters( 'give_default_active_gateways', array(
			'manual' => 1,
		) );
	}

	return $value;
}

add_filter( 'give_get_option_gateways', '__give_validate_active_gateways', 10, 1 );
