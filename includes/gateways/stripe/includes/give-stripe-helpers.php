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

	if ( give_stripe_is_connected() ) {
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

/**
 * Get Preferred Locale based on the selection of language.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_preferred_locale() {

	$language_code = substr( get_locale(), 0, 2 ); // Get the lowercase language code. For Example, en, es, de.

	// Return "no" as accepted parameter for norwegian language code "nb" && "nn".
	$language_code = in_array( $language_code, array( 'nb', 'nn' ), true ) ? 'no' : $language_code;

	return apply_filters( 'give_stripe_elements_preferred_locale', $language_code );
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet.
 *
 * @since 2.5.0
 *
 * @param int $user_id_or_email The user ID or email to look up.
 *
 * @return string Stripe customer ID.
 */
function give_stripe_get_customer_id( $user_id_or_email ) {

	$user_id            = 0;
	$stripe_customer_id = '';

	// First check the customer meta of purchase email.
	if ( class_exists( 'Give_DB_Donor_Meta' ) && is_email( $user_id_or_email ) ) {
		$donor              = new Give_Donor( $user_id_or_email );
		$stripe_customer_id = $donor->get_meta( give_stripe_get_customer_key() );
	}

	// If not found via email, check user_id.
	if ( class_exists( 'Give_DB_Donor_Meta' ) && empty( $stripe_customer_id ) ) {
		$donor              = new Give_Donor( $user_id, true );
		$stripe_customer_id = $donor->get_meta( give_stripe_get_customer_key() );
	}

	// Get user ID from customer.
	if ( is_email( $user_id_or_email ) && empty( $stripe_customer_id ) ) {

		$donor = new Give_Donor( $user_id_or_email );
		// Pull user ID from customer object.
		if ( $donor->id > 0 && ! empty( $donor->user_id ) ) {
			$user_id = $donor->user_id;
		}
	} else {
		// This is a user ID passed.
		$user_id = $user_id_or_email;
	}

	// If no Stripe customer ID found in customer meta move to wp user meta.
	if ( empty( $stripe_customer_id ) && ! empty( $user_id ) ) {

		$stripe_customer_id = get_user_meta( $user_id, give_stripe_get_customer_key(), true );

	} elseif ( empty( $stripe_customer_id ) && class_exists( 'Give_Recurring_Subscriber' ) ) {

		// Not found in customer meta or user meta, check Recurring data.
		$by_user_id = is_int( $user_id_or_email ) ? true : false;
		$subscriber = new Give_Recurring_Subscriber( $user_id_or_email, $by_user_id );

		if ( $subscriber->id > 0 ) {

			$verified = false;

			if ( ( $by_user_id && $user_id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber.
				$verified = true;
			} else {
				// If the email used is the same as the primary email.
				if ( $subscriber->email == $user_id_or_email ) {
					$verified = true;
				}

				// If the email is in the Give's Additional emails.
				if ( property_exists( $subscriber, 'emails' ) && in_array( $user_id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}

			if ( $verified ) {

				// Backwards compatibility from changed method name.
				// We changed the method name in recurring.
				if ( method_exists( $subscriber, 'get_recurring_donor_id' ) ) {
					$stripe_customer_id = $subscriber->get_recurring_donor_id( 'stripe' );
				} elseif ( method_exists( $subscriber, 'get_recurring_customer_id' ) ) {
					$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
				}
			}
		}

		if ( ! empty( $stripe_customer_id ) ) {
			update_user_meta( $subscriber->user_id, give_stripe_get_customer_key(), $stripe_customer_id );
		}
	}// End if().

	return $stripe_customer_id;

}

/**
 * Get the meta key for storing Stripe customer IDs in.
 *
 * @since  2.5.0
 *
 * @return string $key
 */
function give_stripe_get_customer_key() {

	$key = '_give_stripe_customer_id';

	if ( give_is_test_mode() ) {
		$key .= '_test';
	}

	return $key;
}

/**
 * Determines if the shop is using a zero-decimal currency.
 *
 * @access      public
 * @since       1.0
 * @return      bool
 */
function give_stripe_is_zero_decimal_currency() {

	$ret      = false;
	$currency = give_get_currency();

	switch ( $currency ) {
		case 'BIF':
		case 'CLP':
		case 'DJF':
		case 'GNF':
		case 'JPY':
		case 'KMF':
		case 'KRW':
		case 'MGA':
		case 'PYG':
		case 'RWF':
		case 'VND':
		case 'VUV':
		case 'XAF':
		case 'XOF':
		case 'XPF':
			$ret = true;
			break;
	}

	return $ret;
}
