<?php
/**
 * Gateway Functions
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WordImpress
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
	$gateways = array(
		'paypal' => array(
			'admin_label'    => esc_html__( 'PayPal Standard', 'give' ),
			'checkout_label' => esc_html__( 'PayPal', 'give' ),
		),
		'manual' => array(
			'admin_label'    => esc_html__( 'Test Donation', 'give' ),
			'checkout_label' => esc_html__( 'Test Donation', 'give' ),
		),
	);

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

	$give_options = give_get_settings();
	$default      = isset( $give_options['default_gateway'] ) && give_is_gateway_active( $give_options['default_gateway'] ) ? $give_options['default_gateway'] : 'paypal';
	$form_default = get_post_meta( $form_id, '_give_default_gateway', true );

	// Single Form settings varies compared to the Global default settings.
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
 * @since 1.0
 *
 * @param string $gateway Name of the gateway to retrieve a label for
 *
 * @return string Gateway admin label
 */
function give_get_gateway_admin_label( $gateway ) {
	$gateways = give_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['admin_label'] : $gateway;
	$payment  = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : false;

	if ( $gateway == 'manual' && $payment ) {
		if ( give_get_payment_amount( $payment ) == 0 ) {
			$label = esc_html__( 'Test Donation', 'give' );
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
	$gateways = give_get_payment_gateways();
	$label    = isset( $gateways[ $gateway ] ) ? $gateways[ $gateway ]['checkout_label'] : $gateway;

	if ( $gateway == 'manual' ) {
		$label = esc_html__( 'Test Donation', 'give' );
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
	return give_record_log( $title, $message, $parent, 'gateway_error' );
}

/**
 * Counts the number of donations made with a gateway.
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


/**
 * Create payment.
 *
 * @param $payment_data
 *
 * @return bool|int
 */
function give_create_payment( $payment_data ) {

	$form_id  = intval( $payment_data['post_data']['give-form-id'] );
	$price_id = isset( $payment_data['post_data']['give-price-id'] ) ? $payment_data['post_data']['give-price-id'] : '';

	// Collect payment data.
	$payment_data = array(
		'price'           => $payment_data['price'],
		'give_form_title' => $payment_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $payment_data['date'],
		'user_email'      => $payment_data['user_email'],
		'purchase_key'    => $payment_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $payment_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'paypal',
	);

	// Record the pending payment.
	return give_insert_payment( $payment_data );
}

/**
 * Build paypal url
 *
 * Note: For internal use only.
 *
 * @param int   $payment_id   Payment ID
 * @param array $payment_data Array of payment data.
 *
 * @return mixed|string
 */
function give_build_paypal_url( $payment_id, $payment_data ) {
	$form_id = intval( $payment_data['post_data']['give-form-id'] );

	// Only send to PayPal if the pending payment is created successfully.
	$listener_url = add_query_arg( 'give-listener', 'IPN', home_url( 'index.php' ) );

	// Get the success url.
	$return_url = add_query_arg( array(
		'payment-confirmation' => 'paypal',
		'payment-id'           => $payment_id,

	), get_permalink( give_get_option( 'success_page' ) ) );

	// Get the PayPal redirect uri.
	$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';

	// Item name - pass level name if variable priced.
	$item_name = $payment_data['post_data']['give-form-title'];

	// Verify has variable prices.
	if ( give_has_variable_prices( $form_id ) && isset( $payment_data['post_data']['give-price-id'] ) ) {

		$item_price_level_text = give_get_price_option_name( $form_id, $payment_data['post_data']['give-price-id'] );

		$price_level_amount = give_get_price_option_amount( $form_id, $payment_data['post_data']['give-price-id'] );

		// Donation given doesn't match selected level (must be a custom amount).
		if ( $price_level_amount != give_sanitize_amount( $payment_data['price'] ) ) {
			$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
			// user custom amount text if any, fallback to default if not.
			$item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );

		} //Is there any donation level text?
		elseif ( ! empty( $item_price_level_text ) ) {
			$item_name .= ' - ' . $item_price_level_text;
		}
	} //Single donation: Custom Amount.
	elseif ( give_get_form_price( $form_id ) !== give_sanitize_amount( $payment_data['price'] ) ) {
		$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
		// user custom amount text if any, fallback to default if not.
		$item_name .= ' - ' . give_check_variable( $custom_amount_text, 'empty', esc_html__( 'Custom Amount', 'give' ) );
	}

	// Setup PayPal API params.
	$paypal_args = array(
		'business'      => give_get_option( 'paypal_email', false ),
		'first_name'    => $payment_data['user_info']['first_name'],
		'last_name'     => $payment_data['user_info']['last_name'],
		'email'         => $payment_data['user_email'],
		'invoice'       => $payment_data['purchase_key'],
		'amount'        => $payment_data['price'],
		'item_name'     => stripslashes( $item_name ),
		'no_shipping'   => '1',
		'shipping'      => '0',
		'no_note'       => '1',
		'currency_code' => give_get_currency(),
		'charset'       => get_bloginfo( 'charset' ),
		'custom'        => $payment_id,
		'rm'            => '2',
		'return'        => $return_url,
		'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $payment_id ),
		'notify_url'    => $listener_url,
		'page_style'    => give_get_paypal_page_style(),
		'cbt'           => get_bloginfo( 'name' ),
		'bn'            => 'givewp_SP',
	);

	// Add user address if present.
	if ( ! empty( $payment_data['user_info']['address'] ) ) {
		$default_address = array(
			'line1'   => '',
			'line2'   => '',
			'city'    => '',
			'state'   => '',
			'country' => '',
		);

		$address = wp_parse_args( $payment_data['user_info']['address'], $default_address );

		$paypal_args['address1'] = $address['line1'];
		$paypal_args['address2'] = $address['line2'];
		$paypal_args['city']     = $address['city'];
		$paypal_args['state']    = $address['state'];
		$paypal_args['country']  = $address['country'];
	}

	// Donations or regular transactions?
	$paypal_args['cmd'] = give_get_paypal_button_type();

	/**
	 * Filter the paypal redirect args.
	 *
	 * @since 1.8
	 *
	 * @param array $paypal_args
	 * @param array $payment_data
	 */
	$paypal_args = apply_filters( 'give_paypal_redirect_args', $paypal_args, $payment_data );

	// Build query.
	$paypal_redirect .= http_build_query( $paypal_args );

	// Fix for some sites that encode the entities.
	$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );

	return $paypal_redirect;
}


/**
 * Get paypal button type.
 *
 * Note: only for internal use
 *
 * @since 1.8
 * @return string
 */
function give_get_paypal_button_type() {
	// paypal_button_type can be donation or standard.
	$paypal_button_type = '_donations';
	if ( give_get_option( 'paypal_button_type' ) === 'standard' ) {
		$paypal_button_type = '_xclick';
	}

	return $paypal_button_type;
}
