<?php
/**
 * Front-end Filters
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
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
		update_option( 'give_settings', $settings );
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

	$args['comment_author']       = isset( $_POST['give_first'] ) ? strip_tags( trim( $_POST['give_first'] ) ) : '';
	$args['comment_author_email'] = isset( $_POST['give_email'] ) ? $_POST['give_email'] : false;
	$args['blog']                 = get_option( 'home' );
	$args['blog_lang']            = get_locale();
	$args['blog_charset']         = get_option( 'blog_charset' );
	$args['user_ip']              = $_SERVER['REMOTE_ADDR'];
	$args['user_agent']           = $_SERVER['HTTP_USER_AGENT'];
	$args['referrer']             = $_SERVER['HTTP_REFERER'];
	$args['comment_type']         = 'contact-form';

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
 * Set the number of decimal places per currency
 *
 * @since 1.0
 * @since 1.6 $decimals parameter removed from function params
 * *
 * @return int $decimals
 */
function give_currency_decimal_filter() {

	remove_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );

	// Set default number of decimals.
	$decimals = give_get_price_decimals();

	add_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );

	// Get number of decimals with backward compatibility ( version < 1.6 )
	if ( 1 <= func_num_args() ) {
		$decimals = ( false === func_get_arg( 0 ) ? $decimals : absint( func_get_arg( 0 ) ) );
	}

	$currency = give_get_currency();

	switch ( $currency ) {
		// case 'RIAL' :
		case 'JPY' :
		case 'KRW' :
		// case 'TWD' :
		// case 'HUF' :

			$decimals = 0;
			break;
	}

	return apply_filters( 'give_currency_decimal_count', $decimals, $currency );
}

add_filter( 'give_sanitize_amount_decimals', 'give_currency_decimal_filter' );
add_filter( 'give_format_amount_decimals', 'give_currency_decimal_filter' );