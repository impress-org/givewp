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
 * @since 2.5.0
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
 * @since 2.5.0
 *
 * @return bool
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

/**
 * Get Statement Descriptor.
 *
 * Create the Statement Description.
 *
 * @see https://stripe.com/docs/api/php#create_charge-statement_descriptor
 *
 * @since 2.5.0
 *
 * @param array $data List of posted variable while submitting donation.
 *
 * @return mixed
 */
function give_stripe_get_statement_descriptor( $data = array() ) {

	$descriptor_option = give_get_option( 'stripe_statement_descriptor', get_bloginfo( 'name' ) );

	// Clean the statement descriptor.
	$unsupported_characters = array( '<', '>', '"', '\'' );
	$statement_descriptor   = mb_substr( $descriptor_option, 0, 22 );
	$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

	return apply_filters( 'give_stripe_statement_descriptor', $statement_descriptor, $data );

}

/**
 * Get the sequential order number of donation.
 *
 * @since 2.5.0
 *
 * @param integer $donation_or_post_id Donation or wp post id.
 * @param bool    $check_enabled       Check if sequential-ordering_status is activated or not.
 *
 * @return bool|string
 */
function give_stripe_get_sequential_id( $donation_or_post_id, $check_enabled = true ) {
	// Check if enabled.
	if ( true === $check_enabled ) {
		$sequential_ordering = give_get_option( 'sequential-ordering_status' );

		if ( ! give_is_setting_enabled( $sequential_ordering ) ) {
			return false;
		}
	}

	return Give()->seq_donation_number->get_serial_code( $donation_or_post_id );
}

/**
 * Get Custom FFM Fields.
 *
 * @param int $form_id     Donation Form ID.
 * @param int $donation_id Donation ID.
 *
 * @since 2.5.0
 *
 * @return array
 */
function give_stripe_get_custom_ffm_fields( $form_id, $donation_id = 0 ) {

	// Bail out, if FFM add-on is not active.
	if ( ! class_exists( 'Give_Form_Fields_Manager' ) ) {
		return array();
	}

	$ffm_meta     = array();
	$ffm_required = array();
	$ffm_optional = array();
	$field_label  = '';
	$ffm_fields   = give_get_meta( $form_id, 'give-form-fields', true );

	if ( is_array( $ffm_fields ) && count( $ffm_fields ) > 0 ) {

		// Loop through ffm fields.
		foreach ( $ffm_fields as $field ) {

			if ( $donation_id > 0 ) {
				$field_value = give_get_meta( $donation_id, $field['name'], true );
			} elseif ( ! empty( $_POST[ $field['name'] ] ) ) { // WPCS: input var ok, sanitization ok, CSRF ok.
				$field_value = give_clean( $_POST[ $field['name'] ] ); // WPCS: input var ok, sanitization ok, CSRF ok.
				$field_value = give_stripe_ffm_field_value_to_str( $field_value );

			} else {
				$field_value = __( '-- N/A --', 'give' );
			}

			// Strip the number of characters below 450 for custom fields value input when passed to metadata.
			if ( strlen( $field_value ) > 450 ) {
				$field_value = substr( $field_value, 0, 450 ) . '...';
			}

			if ( ! empty( $field['label'] ) ) {
				$field_label = strlen( $field['label'] ) > 25
					? trim( substr( $field['label'], 0, 25 ) ) . '...'
					: $field['label'];
			} elseif ( ! empty( $field['name'] ) ) {
				$field_label = strlen( $field['name'] ) > 25
					? trim( substr( $field['name'], 0, 25 ) ) . '...'
					: $field['name'];
			}

			// Make sure that the required fields are at the top.
			$required_field = ! empty( $field['required'] ) ? $field['required'] : '';
			if ( give_is_setting_enabled( $required_field ) ) {
				$ffm_required[ $field_label ] = is_array( $field_value ) ? implode( ' | ', $field_value ) : $field_value;
			} else {
				$ffm_optional[ $field_label ] = is_array( $field_value ) ? implode( ' | ', $field_value ) : $field_value;
			}

			$ffm_meta = array_merge( $ffm_required, $ffm_optional );

		} // End foreach().
	} // End if().

	return $ffm_meta;

}

/**
 * This function is used to set application information to Stripe.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_set_app_info() {

	try {

		/**
		 * This filter hook is used to change the application name when Stripe add-on is used.
		 *
		 * Note: This filter hook is for internal purposes only.
		 *
		 * @since 2.5.0
		 */
		$application_name = apply_filters( 'give_stripe_get_application_name', 'Give Core' );

		/**
		 * This filter hook is used to chnage the application version when Stripe add-on is used.
		 *
		 * Note: This filter hook is for internal purposes only.
		 *
		 * @since 2.5.0
		 */
		$application_version = apply_filters( 'give_stripe_get_application_version', GIVE_VERSION );

		\Stripe\Stripe::setAppInfo(
			$application_name,
			$application_version,
			esc_url_raw( 'https://givewp.com' ),
			'pp_partner_DKj75W1QYBxBLK' // Partner ID.
		);
	} catch ( \Stripe\Error\Base $e ) {
		Give_Stripe_Logger::log_error( $e, $this->id );
	} catch ( Exception $e ) {

		give_record_gateway_error(
			__( 'Stripe Error', 'give' ),
			sprintf(
				/* translators: %s Exception Error Message */
				__( 'Unable to set application information to Stripe. Details: %s', 'give' ),
				$e->getMessage()
			)
		);

		give_set_error( 'stripe_app_info_error', __( 'Unable to set application information to Stripe. Please try again.', 'give' ) );
	} // End try().

}

/**
 * This function is used to get application fee percentage.
 *
 * Note: This function is for internal purpose only.
 *
 * @since 2.5.0
 *
 * @return int
 */
function give_stripe_get_application_fee_percentage() {
	return 2;
}

/**
 * This function is used to calculate application fee amount.
 *
 * @param int $amount Donation amount.
 *
 * @since 2.5.0
 *
 * @return int
 */
function give_stripe_get_application_fee_amount( $amount ) {
	return $amount * give_stripe_get_application_fee_percentage() / 100;
}

/**
 * This function is used to fetch the donation id by meta key.
 *
 * @param string $id   Any String.
 * @param string $type intent_id/client_secret
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_get_donation_id_by( $id, $type ) {

	global $wpdb;

	$donation_id = 0;

	switch ( $type ) {
		case 'intent_id':
			$donation_id = $wpdb->get_var( $wpdb->prepare( "SELECT donation_id FROM {$wpdb->donationmeta} WHERE meta_key = '_give_stripe_payment_intent_id' AND meta_value = %s LIMIT 1", $id ) );
			break;

		case 'client_secret':
			$donation_id = $wpdb->get_var( $wpdb->prepare( "SELECT donation_id FROM {$wpdb->donationmeta} WHERE meta_key = '_give_stripe_payment_intent_client_secret' AND meta_value = %s LIMIT 1", $id ) );
			break;
	}

	return $donation_id;

}

/**
 * This function is used to set Stripe API Key.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_set_api_key() {

    try {

		// Fetch secret key.
        $secret_key = give_stripe_get_secret_key();

		// Set App Info.
		give_stripe_set_app_info();

        // Set secret key.
		\Stripe\Stripe::setApiKey( $secret_key );

	} catch ( \Stripe\Error\Base $e ) {

		// Log Error.
		$this->log_error( $e );

	} catch ( Exception $e ) {

		// Something went wrong outside of Stripe.
		give_record_gateway_error(
			__( 'Stripe Error', 'give' ),
			sprintf(
			/* translators: %s Exception Message Body */
				__( 'Unable to set Stripe API Key. Details: %s', 'give' ),
				$e->getMessage()
			)
		);
		give_set_error( 'stripe_error', __( 'An error occurred while processing the donation. Please try again.', 'give' ) );

		// Send donor back to donation form page.
		give_send_back_to_checkout( '?payment-mode=' . give_clean( $_GET['payment-mode'] ) );

	}

}

/**
 * This function is used to fetch the webhook key used to store in options table.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_webhook_key() {

	$mode = give_stripe_get_payment_mode();

	return "give_stripe_{$mode}_webhook_id";
}

/**
 * This function is used to fetch the webhook id which is stored in DB, if the webhook is set on Stripe.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_webhook_id() {

    $key = give_stripe_get_webhook_key();

	return trim( give_get_option( $key ) );
}

/**
 * This function is used to fetch the webhook id which is stored in DB, if the webhook is set on Stripe.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_delete_webhook_id() {

    $key = give_stripe_get_webhook_key();

	return trim( give_delete_option( $key ) );
}

/**
 * This function is used to get the payment mode text. For example, "test" or "live"
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_payment_mode() {

    $mode = 'live';

    if ( give_is_test_mode() ) {
        $mode = 'test';
    }

    return $mode;
}

/**
 * This function will be used to convert upto 2 dimensional array to string as per FFM add-on Repeater field needs.
 *
 * This function is for internal purpose only.
 *
 * @param array|string $data Data to be converted to string.
 *
 * @since 2.5.0
 *
 * @return array|string
 */
function give_stripe_ffm_field_value_to_str( $data ) {

	if ( is_array( $data ) && count( $data ) > 0 ) {
		$count = 0;
		foreach ( $data as $item ) {
			if ( is_array( $item ) && count( $item ) > 0 ) {
				$data[ $count ] = implode( ',', $item );
			}

			$count ++;
		}

		$data = implode( '|', $data );
	}

	return $data;
}

/**
 * This function will be used to get Stripe transaction id link.
 *
 * @param int    $donation_id    Donation ID.
 * @param string $transaction_id Stripe Transaction ID.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_transaction_link( $donation_id, $transaction_id = '' ) {

	// If empty transaction id then get transaction id from donation id.
	if ( empty( $transaction_id ) ) {
		$transaction_id = give_get_payment_transaction_id( $donation_id );
	}

	$transaction_link = sprintf(
		'<a href="%1$s" target="_blank">%2$s</a>',
		give_stripe_get_transaction_url( $transaction_id ),
		$transaction_id
	);

	return $transaction_link;
}

/**
 * This function will return stripe transaction url.
 *
 * @param string $transaction_id Stripe Transaction ID.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_transaction_url( $transaction_id ) {

	$mode = '';

	if ( give_is_test_mode() ) {
		$mode = 'test/';
	}

	$transaction_url = esc_url_raw( "https://dashboard.stripe.com/{$mode}payments/{$transaction_id}" );

	return $transaction_url;
}
