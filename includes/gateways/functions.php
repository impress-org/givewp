<?php
/**
 * Gateway Functions
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
	$gateways = array(
		'paypal' => array(
			'admin_label'    => __( 'PayPal Standard', 'give' ),
			'checkout_label' => __( 'PayPal', 'give' ),
			'supports'       => array( 'buy_now' )
		),
		'manual' => array(
			'admin_label'    => __( 'Test Payment', 'give' ),
			'checkout_label' => __( 'Test Payment', 'give' )
		),
	);

	return apply_filters( 'give_payment_gateways', $gateways );

}

/**
 * Returns a list of all enabled gateways.
 *
 * @since 1.0
 * @return array $gateway_list All the available gateways
 */
function give_get_enabled_payment_gateways() {
	global $give_options;

	$gateways = give_get_payment_gateways();

	$enabled = isset( $_POST['gateways'] )
		? $_POST['gateways']
		: ( isset( $give_options['gateways'] ) ? $give_options['gateways'] : false );

	$gateway_list = array();

	foreach ( $gateways as $key => $gateway ) {
		if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
			$gateway_list[ $key ] = $gateway;
		}
	}

	return apply_filters( 'give_enabled_payment_gateways', $gateway_list );
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
 * @global $give_options Array of all the Give Options
 *
 * @param  $form_id      int ID of the Give Form
 *
 * @return string Gateway ID
 */
function give_get_default_gateway( $form_id ) {

	global $give_options;

	$default      = isset( $give_options['default_gateway'] ) && give_is_gateway_active( $give_options['default_gateway'] ) ? $give_options['default_gateway'] : 'paypal';
	$form_default = get_post_meta( $form_id, '_give_default_gateway', true );

	//Single Form settings varies compared to the Global default settings
	if ( ! empty( $form_default ) &&
	     $form_id !== null &&
	     $default !== $form_default &&
	     $form_default !== 'global' &&
	     give_is_gateway_active( $form_default )
	) {
		$default = $form_default;
	}

	return apply_filters( 'give_default_gateway', $default );
}

/**
 * Returns the admin label for the specified gateway
 *
 * @since 1.0.8.3
 *
 * @param string $gateway Name of the gateway to retrieve a label for
 *
 * @return string Gateway admin label
 */
function give_get_gateway_admin_label( $gateway ) {
	$gateways = give_get_enabled_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
	$payment  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;

	if ( $gateway == 'manual' && $payment ) {
		if ( give_get_payment_amount( $payment ) == 0 ) {
			$label = __( 'Test Donation', 'give' );
		}
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
	$gateways = give_get_enabled_payment_gateways();
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
 * Checks if a gateway supports buy now
 *
 * @since 1.8
 *
 * @param string $gateway ID of the gateway to retrieve a label for
 *
 * @return bool
 */
function give_gateway_supports_buy_now( $gateway ) {
	$supports = give_get_gateway_supports( $gateway );
	$ret      = in_array( 'buy_now', $supports );

	return apply_filters( 'give_gateway_supports_buy_now', $ret, $gateway );
}

/**
 * Checks if an enabled gateway supports buy now
 *
 * @since 1.0
 * @return bool
 */
function give_give_supports_buy_now() {
	$gateways = give_get_enabled_payment_gateways();
	$ret      = false;

	if ( $gateways ) {
		foreach ( $gateways as $gateway_id => $gateway ) {
			if ( give_gateway_supports_buy_now( $gateway_id ) ) {
				$ret = true;
				break;
			}
		}
	}

	return apply_filters( 'give_give_supports_buy_now', $ret );
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

	// $gateway must match the ID used when registering the gateway
	do_action( 'give_gateway_' . $gateway, $payment_data );
}


/**
 * Determines what the currently selected gateway is
 *
 * If the amount is zero, no option is shown and the checkout uses the manual
 * gateway to emulate a no-gateway-setup for a free donation
 *
 * @access public
 * @since  1.0
 *
 * @param  int $form_id The ID of the Form
 *
 * @return string $enabled_gateway The slug of the gateway
 */
function give_get_chosen_gateway( $form_id ) {
	$gateways        = give_get_enabled_payment_gateways();
	$request_form_id = isset( $_REQUEST['give_form_id'] ) ? $_REQUEST['give_form_id'] : 0;
	if ( empty( $request_form_id ) ) {
		$request_form_id = isset( $_REQUEST['form-id'] ) ? $_REQUEST['form-id'] : 0;
	}
	$chosen          = give_get_default_gateway( $form_id );
	$enabled_gateway = '';

	//Take into account request Form ID args
	if ( ! empty( $request_form_id ) && $form_id == $request_form_id ) {
		$chosen = $_REQUEST['payment-mode'];
	}

	if ( $chosen ) {
		$enabled_gateway = urldecode( $chosen );
	} else if ( count( $gateways ) >= 1 && ! $chosen ) {
		foreach ( $gateways as $gateway_id => $gateway ):
			$enabled_gateway = $gateway_id;
		endforeach;
	} else {
		$enabled_gateway = give_get_default_gateway( $form_id );
	}


	return apply_filters( 'give_chosen_gateway', $enabled_gateway );
}

/**
 * Record a gateway error
 *
 * A simple wrapper function for give_record_log()
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
	return give_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Counts the number of purchases made with a gateway
 *
 * @since 1.0
 *
 * @param string $gateway_id
 * @param string $status
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
		'fields'      => 'ids'
	);

	$payments = new WP_Query( $args );

	if ( $payments ) {
		$ret = $payments->post_count;
	}

	return $ret;
}
