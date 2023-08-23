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

use Give\Log\Log;
use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use Give\ValueObjects\Money;

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function is used to fetch the secret key based on the test mode status.
 *
 * @param int $form_id Form Id.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_secret_key( $form_id = 0 ) {

	// Get default Stripe account details.
	$default_account = give_stripe_get_default_account( $form_id );

	// Live Secret Key.
	$secret_key = ! empty( $default_account['live_secret_key'] ) ? trim( $default_account['live_secret_key'] ) : '';

	// Update secret key, if test mode is enabled.
	if ( give_is_test_mode() ) {
		$secret_key = ! empty( $default_account['test_secret_key'] ) ? trim( $default_account['test_secret_key'] ) : '';
	}

	return $secret_key;
}

/**
 * This function is used to fetch the account id of the Connected Stripe ACcount.
 *
 * @param int $form_id Form Id.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_get_connected_account_id( $form_id = 0 ) {
	$default_account = give_stripe_get_default_account( $form_id );

	return isset( $default_account['account_id'] ) ? trim( $default_account['account_id'] ) : '';
}

/**
 * This function will return connected account options.
 *
 * @since 2.5.0
 *
 * @return array
 */
function give_stripe_get_connected_account_options() {
	$form_id                = ! empty( $_POST['give-form-id'] ) ? absint( $_POST['give-form-id'] ) : 0;
	$default_account        = give_stripe_get_default_account( $form_id );
	$args['stripe_account'] = $default_account['account_id'];

	return $args;
}

/**
 * Get Publishable Key.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.5.0
 *
 * @return string
 */
function give_stripe_get_publishable_key( $form_id = 0 ) {

	// Get default Stripe account details.
	$default_account = give_stripe_get_default_account( $form_id );

	// Live Publishable Key.
	$publishable_key = ! empty( $default_account['live_publishable_key'] ) ? trim( $default_account['live_publishable_key'] ) : '';

	// Update publishable key, if test mode is enabled.
	if ( give_is_test_mode() ) {
		$publishable_key = ! empty( $default_account['test_publishable_key'] ) ? trim( $default_account['test_publishable_key'] ) : '';
	}

	return $publishable_key;
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
		[
			'form_id' => get_the_ID(),
		]
	);

	return wp_json_encode(
		[
			'color'             => '#32325D',
			'fontWeight'        => 500,
			'fontSize'          => '16px',
			'fontSmoothing'     => 'antialiased',
			'::placeholder'     => [
				'color' => $float_labels ? '#CCCCCC' : '#222222',
			],
			':-webkit-autofill' => [
				'color' => '#e39f48',
			],
		]
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

	$default_styles = [
		'base'     => give_stripe_get_default_base_styles(),
		'empty'    => false,
		'invalid'  => false,
		'complete' => false,
	];

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
		$font_styles             = json_decode( $custom_fonts_attributes );
	} else {
		$font_styles = [
			'cssSrc' => give_get_option( 'stripe_google_fonts_url' ),
		];
	}

	if ( empty( $font_styles ) ) {
		$font_styles = [];
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

	if ( 'modal' === give_stripe_get_checkout_type() ) {
		// For Legacy Checkout, Return "no" as accepted parameter for norwegian language code "nb" && "nn".
		$language_code = in_array( $language_code, [ 'nb', 'nn' ], true ) ? 'no' : $language_code;
	} else {
		// For Checkout 2.0, Return "nb" as accepted parameter for norwegian language code "no" && "nn".
		$language_code = in_array( $language_code, [ 'no', 'nn' ], true ) ? 'nb' : $language_code;
	}

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
 * @since 2.19.0 Previously stripe accounts have single global statement descriptor.
 *             Now each stripe account will have their own statement descriptor.
 * @since 2.19.2 give_stripe_get_connected_account_options function returns empty string as account id for stripe account connected with api keys.
 *             For this return an exception throws. Internal logic update to get donation form stripe account on basis of account type
 *
 * @param array $donation_data List of posted variable while submitting donation.
 *
 * @return mixed
 * @throws InvalidPropertyName
 */
function give_stripe_get_statement_descriptor($donation_data = [])
{
    $form_id = 0;

    if ($donation_data && $donation_data['post_data']['give-form-id']) {
        $form_id = (int)$donation_data['post_data']['give-form-id'];
    } elseif (!empty($_POST['give-form-id'])) {
        $form_id = absint($_POST['give-form-id']);
    }

    /*
     * Stripe account connected with api keys does not have account id.
     * We can use account slug to retrieve account data .
     */
    $defaultAccount = give_stripe_get_default_account($form_id);
    $stripAccountId = $defaultAccount['account_id'] ?: $defaultAccount['account_slug'];

    $stripeAccountFormPayment = give(Settings::class)
        ->getStripeAccountById($stripAccountId);
    $text = $stripeAccountFormPayment->statementDescriptor;

    return apply_filters('give_stripe_statement_descriptor', $text, $donation_data);
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
		return [];
	}

	$ffm_meta     = [];
	$ffm_required = [];
	$ffm_optional = [];
	$field_label  = '';
	$ffm_fields   = give_get_meta( $form_id, 'give-form-fields', true );

	if ( is_array( $ffm_fields ) && count( $ffm_fields ) > 0 ) {

		// Loop through ffm fields.
		foreach ( $ffm_fields as $field ) {

			// Continue, if field name is empty which means the input type is not submitable.
			if ( empty( $field['name'] ) ) {
				continue;
			}

			$input_field_value = ! empty( $_POST[ $field['name'] ] ) ? give_clean( $_POST[ $field['name'] ] ) : '';

			if ( $donation_id > 0 ) {
				$field_value = give_get_meta( $donation_id, $field['name'], true );
			} elseif ( ! empty( $input_field_value ) ) {
				$field_value = give_stripe_ffm_field_value_to_str( $input_field_value );
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

	return array_filter( $ffm_meta );

}

/**
 * This function is used to set application information to Stripe.
 *
 * Note: to setup stripe app environment either pass form Id or make sure you have `give-form-id` in POST global variable.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_set_app_info( $form_id = 0 ) {

	try {

		/**
		 * This filter hook is used to change the application name when Stripe add-on is used.
		 *
		 * Note: This filter hook is for internal purposes only.
		 *
		 * @since 2.5.0
		 */
		$application_name = apply_filters( 'give_stripe_get_application_name', 'GiveWP Core' );

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

	$form_id = ! empty( $_POST['give-form-id'] ) ? absint( $_POST['give-form-id'] ) : $form_id;

	// Set API Key on every Stripe API request call.
	give_stripe_set_api_key( $form_id );
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
	return round( $amount * give_stripe_get_application_fee_percentage() / 100, 0 );
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
 * @param int $form_id Form ID.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_set_api_key( $form_id = 0 ) {

	try {

		// Fetch secret key.
		$secret_key = give_stripe_get_secret_key( $form_id );

		// Set secret key.
		\Stripe\Stripe::setApiKey( $secret_key );

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

/**
 * This function will record errors under Stripe Log.
 *
 * @param string $title   Log Title.
 * @param string $message Log Message.
 * @param int    $parent  Parent.
 *
 * @since 2.5.0
 *
 * @return int
 */
function give_stripe_record_log( $title = '', $message = '', $parent = 0 ) {
	$title = empty( $title ) ? esc_html__( 'Stripe Error', 'give' ) : $title;

	return give_record_log( $title, $message, $parent, 'stripe' );
}

/**
 * This function will check whether the ID exists or not based on type.
 *
 * @param string $id   Source ID.
 * @param string $type Source type.
 *
 * @since  2.5.0
 * @access public
 *
 * @return bool
 */
function give_stripe_is_source_type( $id, $type = 'src' ) {
	return (
		$id &&
		preg_match( "/{$type}_/i", $id )
	);
}

/**
 * This helper function is used to process Stripe payments.
 *
 * @param array  $donation_data  Donation form data.
 * @param object $stripe_gateway $this data.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_process_payment( $donation_data, $stripe_gateway ) {

	// Make sure we don't have any left over errors present.
	give_clear_errors();

	$stripe_gateway = new Give_Stripe_Gateway();

	$payment_method_id = ! empty( $donation_data['post_data']['give_stripe_payment_method'] )
		? $donation_data['post_data']['give_stripe_payment_method']
		: false;

	// Any errors?
	$errors = give_get_errors();

	// No errors, proceed.
	if ( ! $errors ) {

		$form_id          = ! empty( $donation_data['post_data']['give-form-id'] ) ? intval( $donation_data['post_data']['give-form-id'] ) : 0;
		$price_id         = ! empty( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : 0;
		$donor_email      = ! empty( $donation_data['post_data']['give_email'] ) ? $donation_data['post_data']['give_email'] : 0;
		$donation_summary = give_payment_gateway_donation_summary( $donation_data, false );

		// Get an existing Stripe customer or create a new Stripe Customer and attach the source to customer.
		$give_stripe_customer = new Give_Stripe_Customer( $donor_email, $payment_method_id );
		$stripe_customer      = $give_stripe_customer->customer_data;
		$stripe_customer_id   = $give_stripe_customer->get_id();

		// We have a Stripe customer, charge them.
		if ( $stripe_customer_id ) {

			// Proceed to get stripe source/payment method details.
			$payment_method    = $give_stripe_customer->attached_payment_method;
			$payment_method_id = $payment_method->id;

			// Setup the payment details.
			$payment_data = [
				'price'           => $donation_data['price'],
				'give_form_title' => $donation_data['post_data']['give-form-title'],
				'give_form_id'    => $form_id,
				'give_price_id'   => $price_id,
				'date'            => $donation_data['date'],
				'user_email'      => $donation_data['user_email'],
				'purchase_key'    => $donation_data['purchase_key'],
				'currency'        => give_get_currency( $form_id ),
				'user_info'       => $donation_data['user_info'],
				'status'          => 'pending',
				'gateway'         => $stripe_gateway->id,
			];

			// Record the pending payment in Give.
			$donation_id = give_insert_payment( $payment_data );

			// Assign required data to array of donation data for future reference.
			$donation_data['donation_id'] = $donation_id;
			$donation_data['description'] = $donation_summary;
			$donation_data['source_id']   = $payment_method_id;

			// Save Stripe Customer ID to Donation note, Donor and Donation for future reference.
			give_insert_payment_note( $donation_id, 'Stripe Customer ID: ' . $stripe_customer_id );
			$stripe_gateway->save_stripe_customer_id( $stripe_customer_id, $donation_id );
			give_update_meta( $donation_id, '_give_stripe_customer_id', $stripe_customer_id );

			// Save Source ID to donation note and DB.
			give_insert_payment_note( $donation_id, 'Stripe Source/Payment Method ID: ' . $payment_method_id );
			give_update_meta( $donation_id, '_give_stripe_source_id', $payment_method_id );

			// Save donation summary to donation.
			give_update_meta( $donation_id, '_give_stripe_donation_summary', $donation_summary );

			/**
			 * This filter hook is used to update the payment intent arguments.
			 *
			 * @since 2.5.0
			 */
			$intent_args = apply_filters(
				'give_stripe_create_intent_args',
				[
					'amount'               => $stripe_gateway->format_amount( $donation_data['price'] ),
					'currency'             => give_get_currency( $form_id ),
					'payment_method_types' => [ 'card' ],
					'statement_descriptor' => give_stripe_get_statement_descriptor(),
					'description'          => give_payment_gateway_donation_summary( $donation_data ),
					'metadata'             => $stripe_gateway->prepare_metadata( $donation_id ),
					'customer'             => $stripe_customer_id,
					'payment_method'       => $payment_method_id,
					'confirm'              => true,
					'return_url'           => give_get_success_page_uri(),
				]
			);

			// Send Stripe Receipt emails when enabled.
			if ( give_is_setting_enabled( give_get_option( 'stripe_receipt_emails' ) ) ) {
				$intent_args['receipt_email'] = $donation_data['user_email'];
			}

			$intent = $stripe_gateway->payment_intent->create( $intent_args );

			// Save Payment Intent Client Secret to donation note and DB.
			give_insert_payment_note( $donation_id, 'Stripe Payment Intent Client Secret: ' . $intent->client_secret );
			give_update_meta( $donation_id, '_give_stripe_payment_intent_client_secret', $intent->client_secret );

			// Set Payment Intent ID as transaction ID for the donation.
			give_set_payment_transaction_id( $donation_id, $intent->id );
			give_insert_payment_note( $donation_id, 'Stripe Charge/Payment Intent ID: ' . $intent->id );

			// Process additional steps for SCA or 3D secure.
			give_stripe_process_additional_authentication( $donation_id, $intent );

			// Send them to success page.
			give_send_to_success_page();
		} else {

			// No customer, failed.
			give_record_gateway_error(
				__( 'Stripe Customer Creation Failed', 'give' ),
				sprintf(
					/* translators: %s Donation Data */
					__( 'Customer creation failed while processing the donation. Details: %s', 'give' ),
					wp_json_encode( $donation_data )
				)
			);
			give_set_error( 'stripe_error', __( 'The Stripe Gateway returned an error while processing the donation.', 'give' ) );
			give_send_back_to_checkout( '?payment-mode=' . give_clean( $_POST['payment-mode'] ) );

		} // End if().
	} else {
		give_send_back_to_checkout( '?payment-mode=' . give_clean( $_POST['payment-mode'] ) );
	} // End if().
}

/**
 * Process additional authentication.
 *
 * @param int                   $donation_id    Donation ID.
 * @param \Stripe\PaymentIntent $payment_intent Stripe Payment Intent Object.
 *
 * @since 2.5.0
 *
 * @return void
 */
function give_stripe_process_additional_authentication( $donation_id, $payment_intent ) {

	// Additional steps required when payment intent status is set to `requires_action`.
	if ( 'requires_action' === $payment_intent->status ) {

		$action_url = $payment_intent->next_action->redirect_to_url->url;

		// Save Payment Intent requires action related information to donation note and DB.
		give_insert_payment_note( $donation_id, 'Stripe requires additional action to be fulfilled.' );
		give_update_meta( $donation_id, '_give_stripe_payment_intent_require_action_url', $action_url );

		wp_redirect( $action_url );
		exit;
	}

}

/**
 * Converts Cents to Dollars
 *
 * @param string $cents Amount in cents.
 *
 * @since  2.5.0
 *
 * @return string
 */
function give_stripe_cents_to_dollars( $cents ) {
	return ( $cents / 100 );
}

/**
 * Converts Dollars to Cents
 *
 * @param string $dollars Amount in dollars.
 *
 * @since  2.5.0
 * @since 2.9.2  Return amount in cent only if currency is not zero-decimal currency.
 *
 * @return string
 */
function give_stripe_dollars_to_cents( $dollars ) {
	return Money::of( $dollars, give_get_currency() )->getMinorAmount();
}

/**
 * Format currency for Stripe.
 *
 * @see https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support
 *
 * @param float $amount Donation amount.
 *
 * @since 2.5.4
 *
 * @return mixed
 */
function give_stripe_format_amount( $amount ) {

	// Return donation amount based on whether the currency is zero decimal or not.
	if ( give_stripe_is_zero_decimal_currency() ) {
		return round( $amount );
	}

	return give_stripe_dollars_to_cents( $amount );
}

/**
 * This function is used to return the checkout type.
 *
 * Note: This function is for internal purposes only and will get deprecated with legacy Stripe Checkout.
 *
 * @since 2.5.5
 *
 * @return string
 */
function give_stripe_get_checkout_type() {
	return give_get_option( 'stripe_checkout_type', 'modal' );
}

/**
 * This function will prepare metadata to send to Stripe.
 *
 * @param int   $donation_id   Donation ID.
 * @param array $donation_data Donation Data.
 *
 * @since  2.5.5
 * @access public
 *
 * @return array
 */
function give_stripe_prepare_metadata( $donation_id, $donation_data = [] ) {

	// Bailout, if donation id doesn't exists.
	if ( ! $donation_id ) {
		return [];
	}

	$form_id = give_get_payment_form_id( $donation_id );
	$email   = give_get_payment_user_email( $donation_id );

	$args = apply_filters(
		'give_stripe_prepare_metadata',
		[
			'Email'            => $email,
			'Donation Post ID' => $donation_id,
		],
		$donation_id,
		$donation_data
	);

	// Add Sequential Metadata.
	$seq_donation_id = give_stripe_get_sequential_id( $donation_id );
	if ( $seq_donation_id ) {
		$args['Sequential ID'] = $seq_donation_id;
	}

	// Add custom FFM fields to Stripe metadata.
	$args = array_merge( $args, give_stripe_get_custom_ffm_fields( $form_id, $donation_id ) );

	// Limit metadata passed to Stripe as maximum of 50 metadata is only allowed.
    // Stripe doc: https://stripe.com/docs/api/metadata
	if ( count( $args ) > 50 ) {
		$args = array_slice( $args, 0, 49 );
		$args = array_merge(
			$args,
			[
				'More Details' => esc_url_raw( admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $donation_id ) ),
			]
		);
	}

	return $args;
}
/**
 * This helper function is used to determine whether the screen is update payment method screen or not.
 *
 * @since 2.5.14
 *
 * @return bool
 */
function give_stripe_is_update_payment_method_screen() {

	$get_data         = give_clean( filter_input_array( INPUT_GET ) );
	$subscription_id  = ! empty( $get_data['subscription_id'] ) ? absint( $get_data['subscription_id'] ) : false;
	$is_update_screen = false;

	if (
		isset( $get_data['action'] ) &&
		'update' === $get_data['action'] &&
		$subscription_id
	) {
		$is_update_screen = $subscription_id;
	}

	return $is_update_screen;
}

/**
 * This function will return the default mandate acceptance text.
 *
 * @param string $method Type of Direct Debit Method.
 *
 * @since 2.6.1
 *
 * @return string
 */
function give_stripe_get_default_mandate_acceptance_text( $method = 'sepa' ) {

	// For SEPA Direct Debit.
	$mandate_acceptance_text = sprintf(
		__( 'By providing your IBAN and confirming this payment, you are authorizing %1$s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'give' ),
		get_bloginfo( 'sitename' )
	);

	if ( 'becs' === $method ) {
		// For BECS Direct Debit.
		$mandate_acceptance_text = sprintf(
			__( 'By providing your bank account details and confirming this payment, you agree to this Direct Debit Request and the <a href="%1$s" target="_blank">Direct Debit Request service agreement</a>, and authorize Stripe Payments Australia Pty Ltd ACN 160 180 343 Direct Debit User ID number 507156 (“Stripe”) to debit your account through the Bulk Electronic Clearing System (BECS) on behalf of %2$s (the “Merchant”) for any amounts separately communicated to you by the Merchant. You certify that you are either an account holder or an authorized signatory on the account listed above.', 'give' ),
			esc_url_raw( 'https://stripe.com/au-becs-dd-service-agreement/legal' ),
			get_bloginfo( 'sitename' )
		);
	}

	return $mandate_acceptance_text;
}

/**
 * This function is used to get mandate acceptance text which is saved in admin.
 *
 * @param string $method Type of Direct Debit method.
 *
 * @since 2.6.1
 *
 * @return string
 */
function give_stripe_get_mandate_acceptance_text( $method = 'sepa' ) {

	$default_text = give_stripe_get_default_mandate_acceptance_text();
	$text         = give_get_option( 'stripe_mandate_acceptance_text', $default_text );

	if ( 'becs' === $method ) {
		$default_text = give_stripe_get_default_mandate_acceptance_text( 'becs' );
		$text         = give_get_option( 'stripe_becs_mandate_acceptance_text', $default_text );
	}

	return apply_filters( 'give_stripe_get_mandate_acceptance_text', $text );
}

/**
 * This helper function is used get stored value of whether we need to hide icon for IBAN element or not.
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 2.6.1
 *
 * @return string
 */
function give_stripe_hide_iban_icon( $form_id ) {

	$hide_icon = give_get_option( 'stripe_hide_icon', 'enabled' );

	return apply_filters( 'give_stripe_hide_iban_icon', $hide_icon, $form_id );
}

/**
 * This helper function is used get IBAN element icon style.
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 2.6.1
 *
 * @return string
 */
function give_stripe_get_iban_icon_style( $form_id ) {

	$icon_style = give_get_option( 'stripe_icon_style', 'default' );

	return apply_filters( 'give_stripe_get_iban_icon_style', $icon_style, $form_id );
}

/**
 * This function will be used to set placeholder country for IBAN element.
 *
 * @since 2.6.1
 *
 * @return string
 */
function give_stripe_get_iban_placeholder_country() {
	return apply_filters( 'give_stripe_get_iban_placeholder_country', 'DE' );
}

/**
 * This helper function is used get stored value of whether we need to hide icon for Bank Account element or not.
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 2.6.3
 *
 * @return string
 */
function give_stripe_becs_hide_icon( $form_id ) {

	$hide_icon = give_get_option( 'stripe_becs_hide_icon', 'enabled' );

	return apply_filters( 'give_stripe_becs_hide_icon', $hide_icon, $form_id );
}

/**
 * This helper function is used get IBAN element icon style.
 *
 * @param int $form_id Donation Form ID.
 *
 * @since 2.6.3
 *
 * @return string
 */
function give_stripe_get_becs_icon_style( $form_id ) {

	$icon_style = give_get_option( 'stripe_becs_icon_style', 'default' );

	return apply_filters( 'give_stripe_get_becs_icon_style', $icon_style, $form_id );
}

/**
 * This helper function will be used check whether Stripe Premium add-on is active or not.
 *
 * @since 2.7.0
 *
 * @return bool
 */
function give_stripe_is_premium_active() {
	return (
		is_plugin_active( 'give-stripe/give-stripe.php' ) &&
		defined( 'GIVE_STRIPE_VERSION' )
	);
}

/**
 * Get all Stripe accounts.
 *
 * @since 2.7.0
 *
 * @return array
 */
function give_stripe_get_all_accounts() {
	return give_get_option( '_give_stripe_get_all_accounts', [] );
}

/**
 * This helper function will return admin settings page url.
 *
 * @param array $args List of arguments.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_get_admin_settings_page_url( $args = [] ) {

	$default_args = [
		'post_type' => 'give_forms',
		'page'      => 'give-settings',
		'tab'       => 'gateways',
		'section'   => 'stripe-settings',
	];

	$args = wp_parse_args( $args, $default_args );

	return esc_url_raw( add_query_arg(
		$args,
		admin_url( 'edit.php' )
	) );
}

/**
 * Send user back to Stripe settings page.
 *
 * @param array $args List of arguments.
 *
 * @since 2.7.0
 *
 * @return void
 */
function give_stripe_get_back_to_settings_page( $args = [] ) {
	$redirect_to = give_stripe_get_admin_settings_page_url( $args );

	wp_safe_redirect( $redirect_to );
	give_die();
}

/**
 * Get Default Stripe Account.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.7.0
 *
 * @return array
 */
function give_stripe_get_default_account( $form_id = 0 ) {

	$default_account_details = [];
	$all_accounts            = give_stripe_get_all_accounts();
	$default_account         = give_stripe_get_default_account_slug( $form_id );

	if ( $all_accounts && ! empty( $default_account ) ) {
		$default_account_details = isset( $all_accounts[ $default_account ] ) ? $all_accounts[ $default_account ] : [];
	}

	return $default_account_details;
}

/**
 * This helper function is used to get default account slug.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_get_default_account_slug( $form_id = 0 ) {

	// Global Stripe account.
	$default_account = give_get_option( '_give_stripe_default_account', '' );

	// Return default Stripe account of the form, if enabled.
	if (
		$form_id > 0 &&
		give_is_setting_enabled( give_get_meta( $form_id, 'give_stripe_per_form_accounts', true ) )
	) {
		$default_account = give_get_meta( $form_id, '_give_stripe_default_account', true );
	}

	return $default_account;
}



/**
 * Convert Slug to Title.
 *
 * @param string $slug Slug.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_convert_slug_to_title( $slug ) {
	return ucfirst( str_replace( '_', ' ', $slug ) );
}

/**
 * Convert Title to Slug.
 *
 * @param string $title Title.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_convert_title_to_slug( $title ) {
	return str_replace( ' ', '_', strtolower( $title ) );
}

/**
 * This helper fn is used to generate unique account slug.
 *
 * @param array $all_accounts   All Stripe accounts.
 * @param int   $accounts_count Total Stripe accounts connected count.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_get_unique_account_slug( $all_accounts, $accounts_count = 1 ) {

	$account_slug = 'account_' . $accounts_count;

	if ( ! in_array( $account_slug, array_keys( $all_accounts ), true ) ) {
		return $account_slug;
	}

	$accounts_count++;
	return give_stripe_get_unique_account_slug( $all_accounts, $accounts_count );
}

/**
 * This function is used to disconnect Stripe account.
 *
 * @param string $slug Account Slug.
 *
 * @return void
 */
function give_stripe_disconnect_account( $slug ) {
	$stripe_accounts = give_stripe_get_all_accounts();

	// Unset Account ID from the list.
	unset( $stripe_accounts[ $slug ] );

	// Update Stripe accounts.
	give_update_option( '_give_stripe_get_all_accounts', $stripe_accounts );
}

/**
 * This helper function is used to get account options.
 *
 * @since 2.7.0
 *
 * @return array
 */
function give_stripe_get_account_options() {

	$options         = [];
	$stripe_accounts = give_stripe_get_all_accounts();

	foreach ( $stripe_accounts as $slug => $details ) {
		$options[ $slug ] = ! empty( $details['account_name'] ) ?
			$details['account_name'] :
			give_stripe_convert_slug_to_title( $slug );
	}

	return $options;
}

/**
 * This function is used to get single ip address for Stripe.
 *
 * @since 2.7.0
 *
 * @return string
 */
function give_stripe_get_ip_address() {

	$ip_address_details = explode( ',', give_get_ip() );

	return $ip_address_details[0];
}

/**
 * This helper function will be used to fetch account details for the users connected via Stripe Connect.
 *
 * @param string $id Stripe Account ID of the connected user.
 *
 * @since 2.7.0
 *
 * @return bool|\Stripe\Account
 */
function give_stripe_get_account_details( $id ) {

	try {
		$account = \Stripe\Account::retrieve( $id );
	} catch ( Exception $e ) {
		give_record_gateway_error(
			esc_html__( 'Give - Stripe Error', 'give' ),
			sprintf(
				'%1$s: %2$s',
				esc_html__( 'Unable to retrieve account details. Please contact support for assistance. Details:', 'give' ),
				$e->getMessage()
			)
		);

		return false;
	}

	return $account;
}
