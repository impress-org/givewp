<?php
/**
 * Gateway Functions
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns a list of all available gateways.
 *
 * @since 1.0
 * @return array $gateways All the available gateways
 */
function give_get_payment_gateways() {
	// Default, built-in gateways
	$gateways = Give_Cache_Setting::get_option( 'gateways', array() );

	return apply_filters( 'give_payment_gateways', $gateways );

}

/**
 * Returns a list of all enabled gateways.
 *
 * @since  1.0
 *
 * @param  int $form_id Form ID
 *
 * @return array $gateway_list All the available gateways
 */
function give_get_enabled_payment_gateways( $form_id = 0 ) {

	$gateways = give_get_payment_gateways();

	$enabled = isset( $_POST['gateways'] ) ? $_POST['gateways'] : give_get_option( 'gateways' );

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	// Set order of payment gateway in list.
	$gateway_list = give_get_ordered_payment_gateways( $gateway_list );

	return apply_filters( 'give_enabled_payment_gateways', $gateway_list, $form_id );
}

/**
 * Checks whether a specified gateway is activated.
 *
 * @since 1.0
 *
 * @param string $gateway Name of the gateway to check for
 *
 * @return boolean true if enabled, false otherwise
 */
function give_is_gateway_active( $gateway ) {
	$gateways = give_get_enabled_payment_gateways();

	$ret = array_key_exists( $gateway, $gateways );

	return apply_filters( 'give_is_gateway_active', $ret, $gateway, $gateways );
}

/**
 * Gets the default payment gateway selected from the Give Settings
 *
 * @since 1.0
 *
 * @param  $form_id      int ID of the Give Form
 *
 * @return string Gateway ID
 */
function give_get_default_gateway( $form_id ) {

	$enabled_gateways = array_keys( give_get_enabled_payment_gateways() );
	$default_gateway  = give_get_option( 'default_gateway' );
	$default          = ! empty( $default_gateway ) && give_is_gateway_active( $default_gateway ) ? $default_gateway : $enabled_gateways[0];
	$form_default     = give_get_meta( $form_id, '_give_default_gateway', true );

	// Single Form settings varies compared to the Global default settings.
	if (
		! empty( $form_default ) &&
		$form_id !== null &&
		$default !== $form_default &&
		'global' !== $form_default &&
		give_is_gateway_active( $form_default )
	) {
		$default = $form_default;
	}

	return apply_filters( 'give_default_gateway', $default );
}

/**
 * Returns the admin label for the specified gateway
 *
 * @since 1.0
 *
 * @param string $gateway Name of the gateway to retrieve a label for
 *
 * @return string Gateway admin label
 */
function give_get_gateway_admin_label( $gateway ) {
	$gateways = give_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;

	if ( $gateway == 'manual' ) {
		$label = __( 'Test Donation', 'give' );
	}

	return apply_filters( 'give_gateway_admin_label', $label, $gateway );
}

/**
 * Returns the checkout label for the specified gateway
 *
 * @since 1.0
 *
 * @param string $gateway Name of the gateway to retrieve a label for
 *
 * @return string Checkout label for the gateway
 */
function give_get_gateway_checkout_label( $gateway ) {
	$gateways = give_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

	if ( $gateway == 'manual' ) {
		$label = __( 'Test Donation', 'give' );
	}

	return apply_filters( 'give_gateway_checkout_label', $label, $gateway );
}

/**
 * Returns the options a gateway supports
 *
 * @since 1.8
 *
 * @param string $gateway ID of the gateway to retrieve a label for
 *
 * @return array Options the gateway supports
 */
function give_get_gateway_supports( $gateway ) {
	$gateways = give_get_enabled_payment_gateways();
	$supports = isset( $gateways[ $gateway ]['supports'] ) ? $gateways[ $gateway ]['supports'] : array();

	return apply_filters( 'give_gateway_supports', $supports, $gateway );
}

/**
 * Sends all the payment data to the specified gateway
 *
 * @since 1.0
 *
 * @param string $gateway      Name of the gateway
 * @param array  $payment_data All the payment data to be sent to the gateway
 *
 * @return void
 */
function give_send_to_gateway( $gateway, $payment_data ) {

	$payment_data['gateway_nonce'] = wp_create_nonce( 'give-gateway' );

	/**
	 * Fires while loading payment gateway via AJAX.
	 *
	 * The dynamic portion of the hook name '$gateway' must match the ID used when registering the gateway.
	 *
	 * @since 1.0
	 *
	 * @param array $payment_data All the payment data to be sent to the gateway.
	 */
	do_action( "give_gateway_{$gateway}", $payment_data );
}


/**
 * Determines the currently selected donation payment gateway.
 *
 * @access public
 * @since  1.0
 *
 * @param  int $form_id The ID of the Form
 *
 * @return string $enabled_gateway The slug of the gateway
 */
function give_get_chosen_gateway( $form_id ) {

	$request_form_id = isset( $_REQUEST['give_form_id'] ) ? $_REQUEST['give_form_id'] : 0;

	// Back to check if 'form-id' is present.
	if ( empty( $request_form_id ) ) {
		$request_form_id = isset( $_REQUEST['form-id'] ) ? $_REQUEST['form-id'] : 0;
	}

	$request_payment_mode = isset( $_REQUEST['payment-mode'] ) ? $_REQUEST['payment-mode'] : '';
	$chosen               = false;

	// If both 'payment-mode' and 'form-id' then set for only this form.
	if ( ! empty( $request_form_id ) && $form_id == $request_form_id ) {
		$chosen = $request_payment_mode;
	} elseif ( empty( $request_form_id ) && $request_payment_mode ) {
		// If no 'form-id' but there is 'payment-mode'.
		$chosen = $request_payment_mode;
	}

	// Get the enable gateway based of chosen var.
	if ( $chosen && give_is_gateway_active( $chosen ) ) {
		$enabled_gateway = urldecode( $chosen );
	} else {
		$enabled_gateway = give_get_default_gateway( $form_id );
	}

	return apply_filters( 'give_chosen_gateway', $enabled_gateway );

}

/**
 * Record a log entry
 *
 * A wrapper function for the Give_Logging class add() method.
 *
 * @since  1.0
 * @since  2.0 Use global logs object
 *
 * @param  string $title   Log title. Default is empty.
 * @param  string $message Log message. Default is empty.
 * @param  int    $parent  Parent log. Default is 0.
 * @param  string $type    Log type. Default is null.
 *
 * @return int             ID of the new log entry.
 */
function give_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	return Give()->logs->add( $title, $message, $parent, $type );
}

/**
 * Record a gateway error.
 *
 * A simple wrapper function for give_record_log().
 *
 * @access public
 * @since  1.0
 *
 * @param string $title   Title of the log entry (default: empty)
 * @param string $message Message to store in the log entry (default: empty)
 * @param int    $parent  Parent log entry (default: 0)
 *
 * @return int ID of the new log entry
 */
function give_record_gateway_error( $title = '', $message = '', $parent = 0 ) {
	$title = empty( $title ) ? esc_html__( 'Payment Error', 'give' ) : $title;

	return give_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Counts the number of donations made with a gateway.
 *
 * @since 1.0
 *
 * @param string       $gateway_id
 * @param array|string $status
 *
 * @return int
 */
function give_count_sales_by_gateway( $gateway_id = 'paypal', $status = 'publish' ) {

	$ret  = 0;
	$args = array(
		'meta_key'    => '_give_payment_gateway',
		'meta_value'  => $gateway_id,
		'nopaging'    => true,
		'post_type'   => 'give_payment',
		'post_status' => $status,
		'fields'      => 'ids',
	);

	$payments = new WP_Query( $args );

	if ( $payments ) {
		$ret = $payments->post_count;
	}

	return $ret;
}


/**
 * Returns a ordered list of all available gateways.
 *
 * @since 1.4.5
 *
 * @param array $gateways List of payment gateways
 *
 * @return array $gateways All the available gateways
 */
function give_get_ordered_payment_gateways( $gateways ) {

	// Get gateways setting.
	$gateways_setting = isset( $_POST['gateways'] ) ? $_POST['gateways'] : give_get_option( 'gateways' );

	// Return from here if we do not have gateways setting.
	if ( empty( $gateways_setting ) ) {
		return $gateways;
	}

	// Reverse array to order payment gateways.
	$gateways_setting = array_reverse( $gateways_setting );

	// Reorder gateways array
	foreach ( $gateways_setting as $gateway_key => $value ) {

		$new_gateway_value = isset( $gateways[ $gateway_key ] ) ? $gateways[ $gateway_key ] : '';
		unset( $gateways[ $gateway_key ] );

		if ( ! empty( $new_gateway_value ) ) {
			$gateways = array_merge( array( $gateway_key => $new_gateway_value ), $gateways );
		}
	}

	/**
	 * Filter payment gateways order.
	 *
	 * @since 1.7
	 *
	 * @param array $gateways All the available gateways
	 */
	return apply_filters( 'give_payment_gateways_order', $gateways );
}
