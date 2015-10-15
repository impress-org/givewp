<?php
/**
 * Process Purchase
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Process Purchase Form
 *
 * Handles the purchase form process.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_process_purchase_form() {

	do_action( 'give_pre_process_purchase' );

	// Validate the form $_POST data
	$valid_data = give_purchase_form_validate_fields();

	// Allow themes and plugins to hook to errors
	do_action( 'give_checkout_error_checks', $valid_data, $_POST );

	$is_ajax = isset( $_POST['give_ajax'] );

	// Process the login form
	if ( isset( $_POST['give_login_submit'] ) ) {
		give_process_form_login();
	}

	// Validate the user
	$user = give_get_purchase_form_user( $valid_data );

	if ( give_get_errors() || ! $user ) {
		if ( $is_ajax ) {
			do_action( 'give_ajax_checkout_errors' );
			give_die();
		} else {
			return false;
		}
	}

	if ( $is_ajax ) {
		echo 'success';
		give_die();
	}

	// Setup user information
	$user_info = array(
		'id'         => $user['user_id'],
		'email'      => $user['user_email'],
		'first_name' => $user['user_first'],
		'last_name'  => $user['user_last'],
		'address'    => $user['address']
	);

	$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

	// Setup purchase information
	$purchase_data = array(
		'price'        => ( isset( $_POST['give-amount'] ) ? (float) apply_filters( 'give_donation_total', give_sanitize_amount( give_format_amount( $_POST['give-amount'] ) ) ) : '0.00' ),
		'purchase_key' => strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'give', true ) ) ),
		'user_email'   => $user['user_email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => stripslashes_deep( $user_info ),
		'post_data'    => $_POST,
		'gateway'      => $valid_data['gateway'],
		'card_info'    => $valid_data['cc_info']
	);

	// Add the user data for hooks
	$valid_data['user'] = $user;

	// Allow themes and plugins to hook before the gateway
	do_action( 'give_checkout_before_gateway', $_POST, $user_info, $valid_data );


	if ( ! $purchase_data['price'] ) {
		// Revert to manual
		$purchase_data['gateway'] = 'manual';
		$_POST['give-gateway']    = 'manual';
	}

	// Allow the purchase data to be modified before it is sent to the gateway
	$purchase_data = apply_filters( 'give_purchase_data_before_gateway', $purchase_data, $valid_data );

	// Setup the data we're storing in the purchase session
	$session_data = $purchase_data;

	// Make sure credit card numbers are never stored in sessions
	unset( $session_data['card_info']['card_number'] );

	// Used for showing data to non logged-in users after purchase, and for other plugins needing purchase data.
	give_set_purchase_session( $session_data );

	// Send info to the gateway for payment processing
	give_send_to_gateway( $purchase_data['gateway'], $purchase_data );
	give_die();

}

add_action( 'give_purchase', 'give_process_purchase_form' );
add_action( 'wp_ajax_give_process_checkout', 'give_process_purchase_form' );
add_action( 'wp_ajax_nopriv_give_process_checkout', 'give_process_purchase_form' );

/**
 * Process the checkout login form
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_process_form_login() {

	$is_ajax = isset( $_POST['give_ajax'] );

	$user_data = give_purchase_form_validate_user_login();

	if ( give_get_errors() || $user_data['user_id'] < 1 ) {
		if ( $is_ajax ) {
			do_action( 'give_ajax_checkout_errors' );
			give_die();
		} else {
			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;
		}
	}

	give_log_user_in( $user_data['user_id'], $user_data['user_login'], $user_data['user_pass'] );

	if ( $is_ajax ) {
		echo 'success';
		give_die();
	} else {
		wp_redirect( $_SERVER['HTTP_REFERER'] );
	}
}

add_action( 'wp_ajax_give_process_checkout_login', 'give_process_form_login' );
add_action( 'wp_ajax_nopriv_give_process_checkout_login', 'give_process_form_login' );

/**
 * Purchase Form Validate Fields
 *
 * @access      private
 * @since       1.0
 * @return      bool|array
 */
function give_purchase_form_validate_fields() {

	global $give_options;

	// Check if there is $_POST
	if ( empty( $_POST ) ) {
		return false;
	}

	$form_id = isset( $_POST['give-form-id'] ) ? $_POST['give-form-id'] : '';

	// Start an array to collect valid data
	$valid_data = array(
		'gateway'          => give_purchase_form_validate_gateway(), // Gateway fallback
		'need_new_user'    => false,     // New user flag
		'need_user_login'  => false,     // Login user flag
		'logged_user_data' => array(),   // Logged user collected data
		'new_user_data'    => array(),   // New user collected data
		'login_user_data'  => array(),   // Login user collected data
		'guest_user_data'  => array(),   // Guest user collected data
		'cc_info'          => give_purchase_form_validate_cc()    // Credit card info
	);

	// Validate agree to terms
	$terms_option = get_post_meta( $form_id, '_give_terms_option', true );
	if ( isset( $terms_option ) && $terms_option === 'yes' ) {
		give_purchase_form_validate_agree_to_terms();
	}

	//Validate amount is more than 0


	if ( is_user_logged_in() ) {
		// Collect logged in user data
		$valid_data['logged_in_user'] = give_purchase_form_validate_logged_in_user();
	} else if ( isset( $_POST['give-purchase-var'] ) && $_POST['give-purchase-var'] == 'needs-to-register' ) {
		// Set new user registration as required
		$valid_data['need_new_user'] = true;
		// Validate new user data
		$valid_data['new_user_data'] = give_purchase_form_validate_new_user();
		// Check if login validation is needed
	} else if ( isset( $_POST['give-purchase-var'] ) && $_POST['give-purchase-var'] == 'needs-to-login' ) {
		// Set user login as required
		$valid_data['need_user_login'] = true;
		// Validate users login info
		$valid_data['login_user_data'] = give_purchase_form_validate_user_login();
	} else {
		// Not registering or logging in, so setup guest user data
		$valid_data['guest_user_data'] = give_purchase_form_validate_guest_user();
	}

	// Return collected data
	return $valid_data;
}

/**
 * Purchase Form Validate Gateway
 *
 * @access      private
 * @since       1.0
 * @return      string
 */
function give_purchase_form_validate_gateway() {

	$gateway = give_get_default_gateway( $_REQUEST['give-form-id'] );

	// Check if a gateway value is present
	if ( ! empty( $_REQUEST['give-gateway'] ) ) {

		$gateway = sanitize_text_field( $_REQUEST['give-gateway'] );

		//Is amount being donated in LIVE mode above 0.00?
		if ( '0.00' == $_REQUEST['give-amount'] && ! give_is_test_mode() ) {

			give_set_error( 'invalid_donation_amount', __( 'Please insert a valid donation amount.', 'give' ) );

		} //Is this test mode zero donation? Let it through but set to manual gateway
		elseif ( '0.00' == $_REQUEST['give-amount'] && give_is_test_mode() ) {

			$gateway = 'manual';

		} //Check if this gateway is active
		elseif ( ! give_is_gateway_active( $gateway ) ) {

			give_set_error( 'invalid_gateway', __( 'The selected payment gateway is not enabled', 'give' ) );

		}

	}

	return $gateway;

}

/**
 * Purchase Form Validate Agree To Terms
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_purchase_form_validate_agree_to_terms() {
	// Validate agree to terms
	if ( ! isset( $_POST['give_agree_to_terms'] ) || $_POST['give_agree_to_terms'] != 1 ) {
		// User did not agree
		give_set_error( 'agree_to_terms', apply_filters( 'give_agree_to_terms_text', __( 'You must agree to the terms of use', 'give' ) ) );
	}
}

/**
 * Purchase Form Required Fields
 *
 * @access      private
 * @since       1.0
 *
 * @param       $form_id
 *
 * @return      array
 */
function give_purchase_form_required_fields( $form_id ) {

	$payment_mode = give_get_chosen_gateway( $form_id );

	$required_fields = array(
		'give_email' => array(
			'error_id'      => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address', 'give' )
		),
		'give_first' => array(
			'error_id'      => 'invalid_first_name',
			'error_message' => __( 'Please enter your first name', 'give' )
		)
	);

	$require_address = give_require_billing_address( $payment_mode );

	if ( $require_address ) {
		$required_fields['card_address']    = array(
			'error_id'      => 'invalid_card_address',
			'error_message' => __( 'Please enter your primary billing address', 'give' )
		);
		$required_fields['card_zip']        = array(
			'error_id'      => 'invalid_zip_code',
			'error_message' => __( 'Please enter your zip / postal code', 'give' )
		);
		$required_fields['card_city']       = array(
			'error_id'      => 'invalid_city',
			'error_message' => __( 'Please enter your billing city', 'give' )
		);
		$required_fields['billing_country'] = array(
			'error_id'      => 'invalid_country',
			'error_message' => __( 'Please select your billing country', 'give' )
		);
		$required_fields['card_state']      = array(
			'error_id'      => 'invalid_state',
			'error_message' => __( 'Please enter billing state / province', 'give' )
		);
	}

	return apply_filters( 'give_purchase_form_required_fields', $required_fields, $form_id );

}

/**
 * Check if the Billing Address is required
 *
 * @since  1.0.1
 *
 * @param $payment_mode
 *
 * @return mixed|void
 */
function give_require_billing_address( $payment_mode ) {

	$return = false;

	if ( isset( $_POST['billing_country'] ) || did_action( "give_{$payment_mode}_cc_form" ) || did_action( 'give_cc_form' ) ) {
		$return = true;
	}

	// Let payment gateways and other extensions determine if address fields should be required
	return apply_filters( 'give_require_billing_address', $return );

}

/**
 * Purchase Form Validate Logged In User
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function give_purchase_form_validate_logged_in_user() {
	global $user_ID;

	$form_id = isset( $_POST['give-form-id'] ) ? $_POST['give-form-id'] : '';

	// Start empty array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => - 1
	);

	// Verify there is a user_ID
	if ( $user_ID > 0 ) {
		// Get the logged in user data
		$user_data = get_userdata( $user_ID );

		// Loop through required fields and show error messages
		foreach ( give_purchase_form_required_fields( $form_id ) as $field_name => $value ) {
			if ( in_array( $value, give_purchase_form_required_fields( $form_id ) ) && empty( $_POST[ $field_name ] ) ) {
				give_set_error( $value['error_id'], $value['error_message'] );
			}
		}

		// Verify data
		if ( $user_data ) {
			// Collected logged in user data
			$valid_user_data = array(
				'user_id'    => $user_ID,
				'user_email' => isset( $_POST['give_email'] ) ? sanitize_email( $_POST['give_email'] ) : $user_data->user_email,
				'user_first' => isset( $_POST['give_first'] ) && ! empty( $_POST['give_first'] ) ? sanitize_text_field( $_POST['give_first'] ) : $user_data->first_name,
				'user_last'  => isset( $_POST['give_last'] ) && ! empty( $_POST['give_last'] ) ? sanitize_text_field( $_POST['give_last'] ) : $user_data->last_name,
			);

			if ( ! is_email( $valid_user_data['user_email'] ) ) {
				give_set_error( 'email_invalid', __( 'Invalid email', 'give' ) );
			}

		} else {
			// Set invalid user error
			give_set_error( 'invalid_user', __( 'The user information is invalid', 'give' ) );
		}
	}

	// Return user data
	return $valid_user_data;
}

/**
 * Purchase Form Validate New User
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function give_purchase_form_validate_new_user() {
	$registering_new_user = false;

	$form_id = isset( $_POST['give-form-id'] ) ? $_POST['give-form-id'] : '';

	// Start an empty array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id'    => - 1,
		// Get first name
		'user_first' => isset( $_POST['give_first'] ) ? sanitize_text_field( $_POST['give_first'] ) : '',
		// Get last name
		'user_last'  => isset( $_POST['give_last'] ) ? sanitize_text_field( $_POST['give_last'] ) : '',
	);

	// Check the new user's credentials against existing ones
	$user_login   = isset( $_POST['give_user_login'] ) ? trim( $_POST['give_user_login'] ) : false;
	$user_email   = isset( $_POST['give_email'] ) ? trim( $_POST['give_email'] ) : false;
	$user_pass    = isset( $_POST['give_user_pass'] ) ? trim( $_POST['give_user_pass'] ) : false;
	$pass_confirm = isset( $_POST['give_user_pass_confirm'] ) ? trim( $_POST['give_user_pass_confirm'] ) : false;

	// Loop through required fields and show error messages
	foreach ( give_purchase_form_required_fields( $form_id ) as $field_name => $value ) {
		if ( in_array( $value, give_purchase_form_required_fields( $form_id ) ) && empty( $_POST[ $field_name ] ) ) {
			give_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	// Check if we have an username to register
	if ( $user_login && strlen( $user_login ) > 0 ) {
		$registering_new_user = true;

		// We have an user name, check if it already exists
		if ( username_exists( $user_login ) ) {
			// Username already registered
			give_set_error( 'username_unavailable', __( 'Username already taken', 'give' ) );
			// Check if it's valid
		} else if ( ! give_validate_username( $user_login ) ) {
			// Invalid username
			if ( is_multisite() ) {
				give_set_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'give' ) );
			} else {
				give_set_error( 'username_invalid', __( 'Invalid username', 'give' ) );
			}
		} else {
			// All the checks have run and it's good to go
			$valid_user_data['user_login'] = $user_login;
		}
	} else {
		if ( give_no_guest_checkout( $form_id ) ) {
			give_set_error( 'registration_required', __( 'You must register or login to complete your donation', 'give' ) );
		}
	}

	// Check if we have an email to verify
	if ( $user_email && strlen( $user_email ) > 0 ) {
		// Validate email
		if ( ! is_email( $user_email ) ) {
			give_set_error( 'email_invalid', __( 'Invalid email', 'give' ) );
			// Check if email exists
		} else if ( email_exists( $user_email ) && $registering_new_user ) {
			give_set_error( 'email_used', __( 'Email already used', 'give' ) );
		} else {
			// All the checks have run and it's good to go
			$valid_user_data['user_email'] = $user_email;
		}
	} else {
		// No email
		give_set_error( 'email_empty', __( 'Enter an email', 'give' ) );
	}

	// Check password
	if ( $user_pass && $pass_confirm ) {
		// Verify confirmation matches
		if ( $user_pass != $pass_confirm ) {
			// Passwords do not match
			give_set_error( 'password_mismatch', __( 'Passwords don\'t match', 'give' ) );
		} else {
			// All is good to go
			$valid_user_data['user_pass'] = $user_pass;
		}
	} else {
		// Password or confirmation missing
		if ( ! $user_pass && $registering_new_user ) {
			// The password is invalid
			give_set_error( 'password_empty', __( 'Enter a password', 'give' ) );
		} else if ( ! $pass_confirm && $registering_new_user ) {
			// Confirmation password is invalid
			give_set_error( 'confirmation_empty', __( 'Enter the password confirmation', 'give' ) );
		}
	}

	return $valid_user_data;
}

/**
 * Donation Form Validate User Login
 *
 * @access      private
 * @since       1.0
 * @return      array
 */
function give_purchase_form_validate_user_login() {

	// Start an array to collect valid user data
	$valid_user_data = array(
		// Assume there will be errors
		'user_id' => - 1
	);

	// Username
	if ( ! isset( $_POST['give_user_login'] ) || $_POST['give_user_login'] == '' ) {
		give_set_error( 'must_log_in', __( 'You must login or register to complete your donation', 'give' ) );

		return $valid_user_data;
	}

	// Get the user by login
	$user_data = get_user_by( 'login', strip_tags( $_POST['give_user_login'] ) );

	// Check if user exists
	if ( $user_data ) {
		// Get password
		$user_pass = isset( $_POST['give_user_pass'] ) ? $_POST['give_user_pass'] : false;

		// Check user_pass
		if ( $user_pass ) {
			// Check if password is valid
			if ( ! wp_check_password( $user_pass, $user_data->user_pass, $user_data->ID ) ) {
				// Incorrect password
				give_set_error(
					'password_incorrect',
					sprintf(
						__( 'The password you entered is incorrect. %sReset Password%s', 'give' ),
						'<a href="' . wp_lostpassword_url( "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" ) . '" title="' . __( 'Lost Password' ) . '">',
						'</a>'
					)
				);
				// All is correct
			} else {
				// Repopulate the valid user data array
				$valid_user_data = array(
					'user_id'    => $user_data->ID,
					'user_login' => $user_data->user_login,
					'user_email' => $user_data->user_email,
					'user_first' => $user_data->first_name,
					'user_last'  => $user_data->last_name,
					'user_pass'  => $user_pass,
				);
			}
		} else {
			// Empty password
			give_set_error( 'password_empty', __( 'Enter a password', 'give' ) );
		}
	} else {
		// no username
		give_set_error( 'username_incorrect', __( 'The username you entered does not exist', 'give' ) );
	}

	return $valid_user_data;
}

/**
 * Purchase Form Validate Guest User
 *
 * @access  private
 * @since   1.0
 * @return  array
 */
function give_purchase_form_validate_guest_user() {

	$form_id = isset( $_POST['give-form-id'] ) ? $_POST['give-form-id'] : '';

	// Start an array to collect valid user data
	$valid_user_data = array(
		// Set a default id for guests
		'user_id' => 0,
	);

	// Show error message if user must be logged in
	if ( give_logged_in_only() ) {
		give_set_error( 'logged_in_only', __( 'You must be logged into an account to donation', 'give' ) );
	}

	// Get the guest email
	$guest_email = isset( $_POST['give_email'] ) ? $_POST['give_email'] : false;

	// Check email
	if ( $guest_email && strlen( $guest_email ) > 0 ) {
		// Validate email
		if ( ! is_email( $guest_email ) ) {
			// Invalid email
			give_set_error( 'email_invalid', __( 'Invalid email', 'give' ) );
		} else {
			// All is good to go
			$valid_user_data['user_email'] = $guest_email;
		}
	} else {
		// No email
		give_set_error( 'email_empty', __( 'Enter an email', 'give' ) );
	}

	// Loop through required fields and show error messages
	foreach ( give_purchase_form_required_fields( $form_id ) as $field_name => $value ) {
		if ( in_array( $value, give_purchase_form_required_fields( $form_id ) ) && empty( $_POST[ $field_name ] ) ) {
			give_set_error( $value['error_id'], $value['error_message'] );
		}
	}

	return $valid_user_data;
}

/**
 * Register And Login New User
 *
 * @param array $user_data
 *
 * @access  private
 * @since   1.0
 * @return  integer
 */
function give_register_and_login_new_user( $user_data = array() ) {
	// Verify the array
	if ( empty( $user_data ) ) {
		return - 1;
	}

	if ( give_get_errors() ) {
		return - 1;
	}

	$user_args = apply_filters( 'give_insert_user_args', array(
		'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
		'user_pass'       => isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : '',
		'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
		'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
		'last_name'       => isset( $user_data['user_last'] ) ? $user_data['user_last'] : '',
		'user_registered' => date( 'Y-m-d H:i:s' ),
		'role'            => get_option( 'default_role' )
	), $user_data );

	// Insert new user
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user
	if ( is_wp_error( $user_id ) ) {
		return - 1;
	}

	// Allow themes and plugins to filter the user data
	$user_data = apply_filters( 'give_insert_user_data', $user_data, $user_args );

	// Allow themes and plugins to hook
	do_action( 'give_insert_user', $user_id, $user_data );

	// Login new user
	give_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );

	// Return user id
	return $user_id;
}

/**
 * Get Purchase Form User
 *
 * @param array $valid_data
 *
 * @access  private
 * @since   1.0
 * @return  array
 */
function give_get_purchase_form_user( $valid_data = array() ) {
	// Initialize user
	$user    = false;
	$is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

	if ( $is_ajax ) {
		// Do not create or login the user during the ajax submission (check for errors only)
		return true;
	} else if ( is_user_logged_in() ) {
		// Set the valid user as the logged in collected data

		$user = $valid_data['logged_in_user'];
	} else if ( $valid_data['need_new_user'] === true || $valid_data['need_user_login'] === true ) {
		// New user registration
		if ( $valid_data['need_new_user'] === true ) {
			// Set user
			$user = $valid_data['new_user_data'];
			// Register and login new user
			$user['user_id'] = give_register_and_login_new_user( $user );
			// User login
		} else if ( $valid_data['need_user_login'] === true && ! $is_ajax ) {

			/*
			 * The login form is now processed in the give_process_purchase_login() function.
			 * This is still here for backwards compatibility.
			 * This also allows the old login process to still work if a user removes the
			 * checkout login submit button.
			 *
			 * This also ensures that the donor is logged in correctly if they click "Purchase"
			 * instead of submitting the login form, meaning the donor is logged in during the purchase process.
			 */

			// Set user
			$user = $valid_data['login_user_data'];
			// Login user
			give_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
		}
	}

	// Check guest checkout
	if ( false === $user && false === give_no_guest_checkout( $_POST['give-form-id'] ) ) {
		// Set user
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user
	if ( false === $user || empty( $user ) ) {
		// Return false
		return false;
	}

	// Get user first name
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $_POST["give_first"] ) ? strip_tags( trim( $_POST["give_first"] ) ) : '';
	}

	// Get user last name
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $_POST["give_last"] ) ? strip_tags( trim( $_POST["give_last"] ) ) : '';
	}

	// Get the user's billing address details
	$user['address']            = array();
	$user['address']['line1']   = ! empty( $_POST['card_address'] ) ? sanitize_text_field( $_POST['card_address'] ) : false;
	$user['address']['line2']   = ! empty( $_POST['card_address_2'] ) ? sanitize_text_field( $_POST['card_address_2'] ) : false;
	$user['address']['city']    = ! empty( $_POST['card_city'] ) ? sanitize_text_field( $_POST['card_city'] ) : false;
	$user['address']['state']   = ! empty( $_POST['card_state'] ) ? sanitize_text_field( $_POST['card_state'] ) : false;
	$user['address']['country'] = ! empty( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : false;
	$user['address']['zip']     = ! empty( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : false;

	if ( empty( $user['address']['country'] ) ) {
		$user['address'] = false;
	} // Country will always be set if address fields are present

	if ( ! empty( $user['user_id'] ) && $user['user_id'] > 0 && ! empty( $user['address'] ) ) {
		// Store the address in the user's meta so the cart can be pre-populated with it on return purchases
		update_user_meta( $user['user_id'], '_give_user_address', $user['address'] );
	}

	// Return valid user
	return $user;
}

/**
 * Validates the credit card info
 *
 * @access  private
 * @since   1.0
 * @return  array
 */
function give_purchase_form_validate_cc() {

	$card_data = give_get_purchase_cc_info();

	// Validate the card zip
	if ( ! empty( $card_data['card_zip'] ) ) {
		if ( ! give_purchase_form_validate_cc_zip( $card_data['card_zip'], $card_data['card_country'] ) ) {
			give_set_error( 'invalid_cc_zip', __( 'The zip / postal code you entered for your billing address is invalid', 'give' ) );
		}
	}

	//Ensure no spaces
	if ( ! empty( $card_data['card_number'] ) ) {
		$card_data['card_number'] = str_replace( '+', '', $card_data['card_number'] ); //no "+" signs
		$card_data['card_number'] = str_replace( ' ', '', $card_data['card_number'] ); // No spaces
	}

	// This should validate card numbers at some point too
	return $card_data;
}

/**
 * Get Credit Card Info
 *
 * @access  private
 * @since   1.0
 * @return  array
 */
function give_get_purchase_cc_info() {
	$cc_info                   = array();
	$cc_info['card_name']      = isset( $_POST['card_name'] ) ? sanitize_text_field( $_POST['card_name'] ) : '';
	$cc_info['card_number']    = isset( $_POST['card_number'] ) ? sanitize_text_field( $_POST['card_number'] ) : '';
	$cc_info['card_cvc']       = isset( $_POST['card_cvc'] ) ? sanitize_text_field( $_POST['card_cvc'] ) : '';
	$cc_info['card_exp_month'] = isset( $_POST['card_exp_month'] ) ? sanitize_text_field( $_POST['card_exp_month'] ) : '';
	$cc_info['card_exp_year']  = isset( $_POST['card_exp_year'] ) ? sanitize_text_field( $_POST['card_exp_year'] ) : '';
	$cc_info['card_address']   = isset( $_POST['card_address'] ) ? sanitize_text_field( $_POST['card_address'] ) : '';
	$cc_info['card_address_2'] = isset( $_POST['card_address_2'] ) ? sanitize_text_field( $_POST['card_address_2'] ) : '';
	$cc_info['card_city']      = isset( $_POST['card_city'] ) ? sanitize_text_field( $_POST['card_city'] ) : '';
	$cc_info['card_state']     = isset( $_POST['card_state'] ) ? sanitize_text_field( $_POST['card_state'] ) : '';
	$cc_info['card_country']   = isset( $_POST['billing_country'] ) ? sanitize_text_field( $_POST['billing_country'] ) : '';
	$cc_info['card_zip']       = isset( $_POST['card_zip'] ) ? sanitize_text_field( $_POST['card_zip'] ) : '';

	// Return cc info
	return $cc_info;
}

/**
 * Validate zip code based on country code
 *
 * @since  1.0
 *
 * @param int    $zip
 * @param string $country_code
 *
 * @return bool|mixed|void
 */
function give_purchase_form_validate_cc_zip( $zip = 0, $country_code = '' ) {
	$ret = false;

	if ( empty( $zip ) || empty( $country_code ) ) {
		return $ret;
	}

	$country_code = strtoupper( $country_code );

	$zip_regex = array(
		"AD" => "AD\d{3}",
		"AM" => "(37)?\d{4}",
		"AR" => "^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$",
		"AS" => "96799",
		"AT" => "\d{4}",
		"AU" => "^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
		"AX" => "22\d{3}",
		"AZ" => "\d{4}",
		"BA" => "\d{5}",
		"BB" => "(BB\d{5})?",
		"BD" => "\d{4}",
		"BE" => "^[1-9]{1}[0-9]{3}$",
		"BG" => "\d{4}",
		"BH" => "((1[0-2]|[2-9])\d{2})?",
		"BM" => "[A-Z]{2}[ ]?[A-Z0-9]{2}",
		"BN" => "[A-Z]{2}[ ]?\d{4}",
		"BR" => "\d{5}[\-]?\d{3}",
		"BY" => "\d{6}",
		"CA" => "^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$",
		"CC" => "6799",
		"CH" => "^[1-9][0-9][0-9][0-9]$",
		"CK" => "\d{4}",
		"CL" => "\d{7}",
		"CN" => "\d{6}",
		"CR" => "\d{4,5}|\d{3}-\d{4}",
		"CS" => "\d{5}",
		"CV" => "\d{4}",
		"CX" => "6798",
		"CY" => "\d{4}",
		"CZ" => "\d{3}[ ]?\d{2}",
		"DE" => "\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
		"DK" => "^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
		"DO" => "\d{5}",
		"DZ" => "\d{5}",
		"EC" => "([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?",
		"EE" => "\d{5}",
		"EG" => "\d{5}",
		"ES" => "^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
		"ET" => "\d{4}",
		"FI" => "\d{5}",
		"FK" => "FIQQ 1ZZ",
		"FM" => "(9694[1-4])([ \-]\d{4})?",
		"FO" => "\d{3}",
		"FR" => "^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
		"GE" => "\d{4}",
		"GF" => "9[78]3\d{2}",
		"GL" => "39\d{2}",
		"GN" => "\d{3}",
		"GP" => "9[78][01]\d{2}",
		"GR" => "\d{3}[ ]?\d{2}",
		"GS" => "SIQQ 1ZZ",
		"GT" => "\d{5}",
		"GU" => "969[123]\d([ \-]\d{4})?",
		"GW" => "\d{4}",
		"HM" => "\d{4}",
		"HN" => "(?:\d{5})?",
		"HR" => "\d{5}",
		"HT" => "\d{4}",
		"HU" => "\d{4}",
		"ID" => "\d{5}",
		"IE" => "((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?",
		"IL" => "\d{5}",
		"IN" => "^[1-9][0-9][0-9][0-9][0-9][0-9]$", //india
		"IO" => "BBND 1ZZ",
		"IQ" => "\d{5}",
		"IS" => "\d{3}",
		"IT" => "^(V-|I-)?[0-9]{5}$",
		"JO" => "\d{5}",
		"JP" => "\d{3}-\d{4}",
		"KE" => "\d{5}",
		"KG" => "\d{6}",
		"KH" => "\d{5}",
		"KR" => "\d{3}[\-]\d{3}",
		"KW" => "\d{5}",
		"KZ" => "\d{6}",
		"LA" => "\d{5}",
		"LB" => "(\d{4}([ ]?\d{4})?)?",
		"LI" => "(948[5-9])|(949[0-7])",
		"LK" => "\d{5}",
		"LR" => "\d{4}",
		"LS" => "\d{3}",
		"LT" => "\d{5}",
		"LU" => "\d{4}",
		"LV" => "\d{4}",
		"MA" => "\d{5}",
		"MC" => "980\d{2}",
		"MD" => "\d{4}",
		"ME" => "8\d{4}",
		"MG" => "\d{3}",
		"MH" => "969[67]\d([ \-]\d{4})?",
		"MK" => "\d{4}",
		"MN" => "\d{6}",
		"MP" => "9695[012]([ \-]\d{4})?",
		"MQ" => "9[78]2\d{2}",
		"MT" => "[A-Z]{3}[ ]?\d{2,4}",
		"MU" => "(\d{3}[A-Z]{2}\d{3})?",
		"MV" => "\d{5}",
		"MX" => "\d{5}",
		"MY" => "\d{5}",
		"NC" => "988\d{2}",
		"NE" => "\d{4}",
		"NF" => "2899",
		"NG" => "(\d{6})?",
		"NI" => "((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?",
		"NL" => "^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
		"NO" => "\d{4}",
		"NP" => "\d{5}",
		"NZ" => "\d{4}",
		"OM" => "(PC )?\d{3}",
		"PF" => "987\d{2}",
		"PG" => "\d{3}",
		"PH" => "\d{4}",
		"PK" => "\d{5}",
		"PL" => "\d{2}-\d{3}",
		"PM" => "9[78]5\d{2}",
		"PN" => "PCRN 1ZZ",
		"PR" => "00[679]\d{2}([ \-]\d{4})?",
		"PT" => "\d{4}([\-]\d{3})?",
		"PW" => "96940",
		"PY" => "\d{4}",
		"RE" => "9[78]4\d{2}",
		"RO" => "\d{6}",
		"RS" => "\d{5}",
		"RU" => "\d{6}",
		"SA" => "\d{5}",
		"SE" => "^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
		"SG" => "\d{6}",
		"SH" => "(ASCN|STHL) 1ZZ",
		"SI" => "\d{4}",
		"SJ" => "\d{4}",
		"SK" => "\d{3}[ ]?\d{2}",
		"SM" => "4789\d",
		"SN" => "\d{5}",
		"SO" => "\d{5}",
		"SZ" => "[HLMS]\d{3}",
		"TC" => "TKCA 1ZZ",
		"TH" => "\d{5}",
		"TJ" => "\d{6}",
		"TM" => "\d{6}",
		"TN" => "\d{4}",
		"TR" => "\d{5}",
		"TW" => "\d{3}(\d{2})?",
		"UA" => "\d{5}",
		"UK" => "^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
		"US" => "^\d{5}([\-]?\d{4})?$",
		"UY" => "\d{5}",
		"UZ" => "\d{6}",
		"VA" => "00120",
		"VE" => "\d{4}",
		"VI" => "008(([0-4]\d)|(5[01]))([ \-]\d{4})?",
		"WF" => "986\d{2}",
		"YT" => "976\d{2}",
		"YU" => "\d{5}",
		"ZA" => "\d{4}",
		"ZM" => "\d{5}"
	);

	if ( ! isset ( $zip_regex[ $country_code ] ) || preg_match( "/" . $zip_regex[ $country_code ] . "/i", $zip ) ) {
		$ret = true;
	}

	return apply_filters( 'give_is_zip_valid', $ret, $zip, $country_code );
}


/**
 * Check the purchase to ensure a banned email is not allowed through
 *
 * @since       1.0
 * @return      void
 */
function give_check_purchase_email( $valid_data, $posted ) {
	$is_banned = false;
	$banned    = give_get_banned_emails();

	if ( empty( $banned ) ) {
		return;
	}

	if ( is_user_logged_in() ) {

		// The user is logged in, check that their account email is not banned
		$user_data = get_userdata( get_current_user_id() );
		if ( give_is_email_banned( $user_data->user_email ) ) {

			$is_banned = true;
		}

		if ( give_is_email_banned( $posted['give_email'] ) ) {
			$is_banned = true;
		}

	} elseif ( isset( $posted['give-purchase-var'] ) && $posted['give-purchase-var'] == 'needs-to-login' ) {

		// The user is logging in, check that their email is not banned
		$user_data = get_user_by( 'login', $posted['give_user_login'] );
		if ( $user_data && give_is_email_banned( $user_data->user_email ) ) {
			$is_banned = true;
		}

	} else {

		// Guest purchase, check that the email is not banned
		if ( give_is_email_banned( $posted['give_email'] ) ) {
			$is_banned = true;
		}

	}

	if ( $is_banned ) {
		// Set an error and give the donor a general error (don't alert them that they were banned)
		give_set_error( 'email_banned', __( 'An internal error has occurred, please try again or contact support.', 'give' ) );
	}
}

add_action( 'give_checkout_error_checks', 'give_check_purchase_email', 10, 2 );
