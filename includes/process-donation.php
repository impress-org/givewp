<?php
/**
 * Process Donation
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
 * Process Donation Form
 *
 * Handles the donation form process.
 *
 * @access private
 * @since  1.0
 *
 * @throws ReflectionException Exception Handling.
 *
 * @return mixed
 */
function give_process_donation_form() {

	// Sanitize Posted Data.
	$post_data = give_clean( $_POST ); // WPCS: input var ok, CSRF ok.

	// Check whether the form submitted via AJAX or not.
	$is_ajax = isset( $post_data['give_ajax'] );

	// Verify donation form nonce.
	if ( ! give_verify_donation_form_nonce( $post_data['give-form-hash'], $post_data['give-form-id'] ) ) {
		if ( $is_ajax ) {
			/**
			 * Fires when AJAX sends back errors from the donation form.
			 *
			 * @since 1.0
			 */
			do_action( 'give_ajax_donation_errors' );
			give_die();
		} else {
			give_send_back_to_checkout();
		}
	}

	/**
	 * Fires before processing the donation form.
	 *
	 * @since 1.0
	 */
	do_action( 'give_pre_process_donation' );

	// Validate the form $_POST data.
	$valid_data = give_donation_form_validate_fields();

	/**
	 * Fires after validating donation form fields.
	 *
	 * Allow you to hook to donation form errors.
	 *
	 * @since 1.0
	 *
	 * @param bool|array $valid_data Validate fields.
	 * @param array $deprecated Deprecated Since 2.0.2. Use $_POST instead.
	 */
	$deprecated = $post_data;
	do_action( 'give_checkout_error_checks', $valid_data, $deprecated );

	// Process the login form.
	if ( isset( $post_data['give_login_submit'] ) ) {
		give_process_form_login();
	}

	// Validate the user.
	$user = give_get_donation_form_user( $valid_data );

	if ( false === $valid_data || ! $user || give_get_errors() ) {
		if ( $is_ajax ) {
			/**
			 * Fires when AJAX sends back errors from the donation form.
			 *
			 * @since 1.0
			 */
			do_action( 'give_ajax_donation_errors' );
			give_die();
		} else {
			return false;
		}
	}

	// If AJAX send back success to proceed with form submission.
	if ( $is_ajax ) {
		echo 'success';
		give_die();
	}

	/**
	 * Fires action after donation form field validated.
	 *
	 * @since 2.2.0
	 */
	do_action( 'give_process_donation_after_validation' );

	// Setup user information.
	$user_info = array(
		'id'         => $user['user_id'],
		'title'      => $user['user_title'],
		'email'      => $user['user_email'],
		'first_name' => $user['user_first'],
		'last_name'  => $user['user_last'],
		'address'    => $user['address'],
	);

	$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

	// Donation form ID.
	$form_id = isset( $post_data['give-form-id'] ) ? absint( $post_data['give-form-id'] ) : 0;

	$price        = isset( $post_data['give-amount'] ) ?
		(float) apply_filters( 'give_donation_total', give_maybe_sanitize_amount( $post_data['give-amount'], array( 'currency' => give_get_currency( $form_id ) ) ) ) :
		'0.00';
	$purchase_key = strtolower( md5( $user['user_email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'give', true ) ) );

	/**
	 * Update donation Purchase key.
	 *
	 * Use this filter to update default donation purchase key
	 * and add prefix in Invoice.
	 *
	 * @since 2.2.4
	 *
	 * @param string $purchase_key
	 * @param string $gateway
	 * @param string $purchase_key
	 *
	 * @return string $purchase_key
	 */
	$purchase_key = apply_filters(
		'give_donation_purchase_key',
		$purchase_key,
		$valid_data['gateway'],
		// Use this purchase key value if you want to generate custom donation purchase key
		// because donation purchase key editable by filters and you may get unedited donation purchase key.
		$purchase_key
	);

	// Setup donation information.
	$donation_data = array(
		'price'        => $price,
		'purchase_key' => $purchase_key,
		'user_email'   => $user['user_email'],
		'date'         => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
		'user_info'    => stripslashes_deep( $user_info ),
		'post_data'    => $post_data,
		'gateway'      => $valid_data['gateway'],
		'card_info'    => $valid_data['cc_info'],
	);

	// Add the user data for hooks.
	$valid_data['user'] = $user;

	/**
	 * Fires before donation form gateway.
	 *
	 * Allow you to hook to donation form before the gateway.
	 *
	 * @since 1.0
	 *
	 * @param array      $post_data  Array of variables passed via the HTTP POST.
	 * @param array      $user_info  Array containing basic user information.
	 * @param bool|array $valid_data Validate fields.
	 */
	do_action( 'give_checkout_before_gateway', $post_data, $user_info, $valid_data );

	// Sanity check for price.
	if ( ! $donation_data['price'] ) {
		// Revert to manual.
		$donation_data['gateway'] = 'manual';
		$_POST['give-gateway']    = 'manual';
	}

	/**
	 * Allow the donation data to be modified before it is sent to the gateway.
	 *
	 * @since 1.7
	 */
	$donation_data = apply_filters( 'give_donation_data_before_gateway', $donation_data, $valid_data );

	// Setup the data we're storing in the donation session.
	$session_data = $donation_data;

	// Make sure credit card numbers are never stored in sessions.
	unset( $session_data['card_info']['card_number'] );
	unset( $session_data['post_data']['card_number'] );

	// Used for showing data to non logged-in users after donation, and for other plugins needing donation data.
	give_set_purchase_session( $session_data );

	// Send info to the gateway for payment processing.
	give_send_to_gateway( $donation_data['gateway'], $donation_data );
	give_die();
}

add_action( 'give_purchase', 'give_process_donation_form' );
add_action( 'wp_ajax_give_process_donation', 'give_process_donation_form' );
add_action( 'wp_ajax_nopriv_give_process_donation', 'give_process_donation_form' );

/**
 * Verify that when a logged in user makes a donation that the email address used doesn't belong to a different customer.
 * Note: only for internal use
 *
 * @see https://github.com/impress-org/give/issues/4025
 *
 * @since  1.7
 * @since  2.4.2 This function runs independently instead of give_checkout_error_checks hook and also edit donor email.
 *
 * @param  array $valid_data Validated data submitted for the donation.
 *
 * @return void
 */
function give_check_logged_in_user_for_existing_email( &$valid_data ) {

	// Verify that the email address belongs to this donor.
	if ( is_user_logged_in() ) {

		$donor = new Give_Donor( get_current_user_id(), true );

		// Bailout: check if wp user is existing donor or not.
		if ( ! $donor->id ) {
			return;
		}

		$submitted_email = strtolower( $valid_data['user_email'] );

		$donor_emails = array_map( 'strtolower', $donor->emails );
		$email_index  = array_search( $submitted_email, $donor_emails, true );

		// If donor matched with email then return set formatted email from database.
		if ( false !== $email_index ) {
			$valid_data['user_email'] = $donor->emails[ $email_index ];

			return;
		}

		// If this email address is not registered with this customer, see if it belongs to any other customer.
		$found_donor = new Give_Donor( $submitted_email );

		if ( $found_donor->id > 0 ) {
			give_set_error(
				'give-customer-email-exists',
				sprintf(
					/* translators: 1. Donor Email, 2. Submitted Email */
					__( 'You are logged in as %1$s, and are submitting a donation as %2$s, which is an existing donor. To ensure that the email address is tied to the correct donor, please submit this donation from a logged-out browser, or choose another email address.', 'give' ),
					$donor->email,
					$submitted_email
				)
			);
		}
	}
}

/**
 * Process the checkout login form
 *
 * @access private
 * @since  1.0
 *
 * @return void
 */
function give_process_form_login() {

	$is_ajax   = ! empty( $_POST['give_ajax'] ) ? give_clean( $_POST['give_ajax'] ) : 0; // WPCS: input var ok, sanitization ok, CSRF ok.
	$referrer  = wp_get_referer();
	$user_data = give_donation_form_validate_user_login();

	if ( give_get_errors() || $user_data['user_id'] < 1 ) {
		if ( $is_ajax ) {
			/**
			 * Fires when AJAX sends back errors from the donation form.
			 *
			 * @since 1.0
			 */
			ob_start();
			do_action( 'give_ajax_donation_errors' );
			$message = ob_get_contents();
			ob_end_clean();
			wp_send_json_error( $message );
		} else {
			wp_safe_redirect( $referrer );
			exit;
		}
	}

	give_log_user_in( $user_data['user_id'], $user_data['user_login'], $user_data['user_pass'] );

	if ( $is_ajax ) {
		$message = Give_Notices::print_frontend_notice(
			sprintf(
				/* translators: %s: user first name */
				esc_html__( 'Welcome %s! You have successfully logged into your account.', 'give' ),
				( ! empty( $user_data['user_first'] ) ) ? $user_data['user_first'] : $user_data['user_login']
			),
			false,
			'success'
		);

		wp_send_json_success( $message );
	} else {
		wp_safe_redirect( $referrer );
	}
}

add_action( 'wp_ajax_give_process_donation_login', 'give_process_form_login' );
add_action( 'wp_ajax_nopriv_give_process_donation_login', 'give_process_form_login' );

/**
 * Donation Form Validate Fields.
 *
 * @access private
 * @since  1.0
 *
 * @return bool|array
 */
function give_donation_form_validate_fields() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Validate Honeypot First.
	if ( ! empty( $post_data['give-honeypot'] ) ) {
		give_set_error( 'invalid_honeypot', esc_html__( 'Honeypot field detected. Go away bad bot!', 'give' ) );
	}

	// Check spam detect.
	if (
		isset( $post_data['action'] )
		&& give_is_spam_donation()
	) {
		give_set_error( 'spam_donation', __( 'The email you are using has been flagged as one used in SPAM comments or donations by our system. Please try using a different email address or contact the site administrator if you have any questions.', 'give' ) );
	}

	// Start an array to collect valid data.
	$valid_data = array(
		'gateway'          => give_donation_form_validate_gateway(), // Gateway fallback (amount is validated here).
		'need_new_user'    => false,     // New user flag.
		'need_user_login'  => false,     // Login user flag.
		'logged_user_data' => array(),   // Logged user collected data.
		'new_user_data'    => array(),   // New user collected data.
		'login_user_data'  => array(),   // Login user collected data.
		'guest_user_data'  => array(),   // Guest user collected data.
		'cc_info'          => give_donation_form_validate_cc(), // Credit card info.
	);

	$form_id = (int) $post_data['give-form-id'];

	// Validate agree to terms.
	if ( give_is_terms_enabled( $form_id ) ) {
		give_donation_form_validate_agree_to_terms();
	}

	if ( is_user_logged_in() ) {

		// Collect logged in user data.
		$valid_data['logged_in_user'] = give_donation_form_validate_logged_in_user();
	} elseif (
		isset( $post_data['give-purchase-var'] )
		&& 'needs-to-register' === $post_data['give-purchase-var']
		&& ! empty( $post_data['give_create_account'] )
	) {

		// Set new user registration as required.
		$valid_data['need_new_user'] = true;

		// Validate new user data.
		$valid_data['new_user_data'] = give_donation_form_validate_new_user();
	} elseif (
		isset( $post_data['give-purchase-var'] )
		&& 'needs-to-login' === $post_data['give-purchase-var']
	) {

		// Set user login as required.
		$valid_data['need_user_login'] = true;

		// Validate users login info.
		$valid_data['login_user_data'] = give_donation_form_validate_user_login();
	} else {

		// Not registering or logging in, so setup guest user data.
		$valid_data['guest_user_data'] = give_donation_form_validate_guest_user();
	}

	// Return collected data.
	return $valid_data;
}

/**
 * Detect spam donation.
 *
 * @since 1.8.14
 *
 * @return bool|mixed
 */
function give_is_spam_donation() {
	$spam = false;

	$user_agent = (string) isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

	if ( strlen( $user_agent ) < 2 ) {
		$spam = true;
	}

	// Allow developer to customized Akismet spam detect API call and it's response.
	return apply_filters( 'give_spam', $spam );
}

/**
 * Donation Form Validate Gateway
 *
 * Validate the gateway and donation amount.
 *
 * @access private
 * @since  1.0
 *
 * @return string
 */
function give_donation_form_validate_gateway() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
	$form_id   = ! empty( $post_data['give-form-id'] ) ? $post_data['give-form-id'] : 0;
	$amount    = ! empty( $post_data['give-amount'] ) ? give_maybe_sanitize_amount( $post_data['give-amount'] ) : 0;
	$gateway   = ! empty( $post_data['give-gateway'] ) ? $post_data['give-gateway'] : 0;

	// Bailout, if payment gateway is not submitted with donation form data.
	if ( empty( $gateway ) ) {

		give_set_error( 'empty_gateway', __( 'The donation form will process with a valid payment gateway.', 'give' ) );

	} elseif ( ! give_is_gateway_active( $gateway ) ) {

		give_set_error( 'invalid_gateway', __( 'The selected payment gateway is not enabled.', 'give' ) );

	} elseif ( empty( $amount ) ) {

		give_set_error( 'invalid_donation_amount', __( 'Please insert a valid donation amount.', 'give' ) );

	} elseif ( ! give_verify_minimum_price( 'minimum' ) ) {

		give_set_error(
			'invalid_donation_minimum',
			sprintf(
				/* translators: %s: minimum donation amount */
				__( 'This form has a minimum donation amount of %s.', 'give' ),
				give_currency_filter(
					give_format_amount(
						give_get_form_minimum_price( $form_id ),
						array(
							'sanitize' => false,
						)
					)
				)
			)
		);
	} elseif ( ! give_verify_minimum_price( 'maximum' ) ) {

		give_set_error(
			'invalid_donation_maximum',
			sprintf(
				/* translators: %s: Maximum donation amount */
				__( 'This form has a maximum donation amount of %s.', 'give' ),
				give_currency_filter(
					give_format_amount(
						give_get_form_maximum_price( $form_id ),
						array(
							'sanitize' => false,
						)
					)
				)
			)
		);
	} // End if().

	return $gateway;

}

/**
 * Donation Form Validate Minimum or Maximum Donation Amount
 *
 * @access private
 * @since  1.3.6
 * @since  2.1 Added support for give maximum amount.
 * @since  2.1.3 Added new filter to modify the return value.
 *
 * @param string $amount_range Which amount needs to verify? minimum or maximum.
 *
 * @return bool
 */
function give_verify_minimum_price( $amount_range = 'minimum' ) {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
	$form_id   = ! empty( $post_data['give-form-id'] ) ? $post_data['give-form-id'] : 0;
	$amount    = ! empty( $post_data['give-amount'] ) ? give_maybe_sanitize_amount( $post_data['give-amount'], array( 'currency' => give_get_currency( $form_id ) ) ) : 0;
	$price_id  = isset( $post_data['give-price-id'] ) ? absint( $post_data['give-price-id'] ) : '';

	$variable_prices = give_has_variable_prices( $form_id );
	$price_ids       = array_map( 'absint', give_get_variable_price_ids( $form_id ) );
	$verified_stat   = false;

	if ( $variable_prices && in_array( $price_id, $price_ids, true ) ) {

		$price_level_amount = give_get_price_option_amount( $form_id, $price_id );

		if ( $price_level_amount == $amount ) {
			$verified_stat = true;
		}
	}

	if ( ! $verified_stat ) {
		switch ( $amount_range ) {
			case 'minimum':
				$verified_stat = ( give_get_form_minimum_price( $form_id ) > $amount ) ? false : true;
				break;
			case 'maximum':
				$verified_stat = ( give_get_form_maximum_price( $form_id ) < $amount ) ? false : true;
				break;
		}
	}

	/**
	 * Filter the verify amount
	 *
	 * @since 2.1.3
	 *
	 * @param bool    $verified_stat Was verification passed or not?
	 * @param string  $amount_range  Type of the amount.
	 * @param integer $form_id       Give Donation Form ID.
	 */
	return apply_filters( 'give_verify_minimum_maximum_price', $verified_stat, $amount_range, $form_id );
}

/**
 * Donation form validate agree to "Terms and Conditions".
 *
 * @access private
 * @since  1.0
 *
 * @return void
 */
function give_donation_form_validate_agree_to_terms() {

	$agree_to_terms = ! empty( $_POST['give_agree_to_terms'] ) ? give_clean( $_POST['give_agree_to_terms'] ) : 0; // WPCS: input var ok, sanitization ok, CSRF ok.

	// Proceed only, if donor agreed to terms.
	if ( ! $agree_to_terms ) {

		// User did not agree.
		give_set_error( 'agree_to_terms', apply_filters( 'give_agree_to_terms_text', __( 'You must agree to the terms and conditions.', 'give' ) ) );
	}
}

/**
 * Donation Form Required Fields.
 *
 * @access private
 * @since  1.0
 *
 * @param  int $form_id Donation Form ID.
 *
 * @return array
 */
function give_get_required_fields( $form_id ) {

	$posted_data  = give_clean( filter_input_array( INPUT_POST ) );
	$payment_mode = give_get_chosen_gateway( $form_id );

	$required_fields = array(
		'give_email' => array(
			'error_id'      => 'invalid_email',
			'error_message' => __( 'Please enter a valid email address.', 'give' ),
		),
		'give_first' => array(
			'error_id'      => 'invalid_first_name',
			'error_message' => __( 'Please enter your first name.', 'give' ),
		),
	);

	$name_title_prefix = give_is_name_title_prefix_required( $form_id );
	if ( $name_title_prefix ) {
		$required_fields['give_title'] = array(
			'error_id'      => 'invalid_title',
			'error_message' => __( 'Please enter your title.', 'give' ),
		);
	}

	// If credit card fields related actions exists then check for the cc fields validations.
	if (
		has_action( "give_{$payment_mode}_cc_form", 'give_get_cc_form' ) ||
		has_action( 'give_cc_form', 'give_get_cc_form' )
	) {

		// Validate card number field for empty check.
		if (
			isset( $posted_data['card_number'] ) &&
			empty( $posted_data['card_number'] )
		) {
			$required_fields['card_number'] = array(
				'error_id'      => 'empty_card_number',
				'error_message' => __( 'Please enter a credit card number.', 'give' ),
			);
		}

		// Validate card cvc field for empty check.
		if (
			isset( $posted_data['card_cvc'] ) &&
			empty( $posted_data['card_cvc'] )
		) {
			$required_fields['card_cvc'] = array(
				'error_id'      => 'empty_card_cvc',
				'error_message' => __( 'Please enter a credit card CVC information.', 'give' ),
			);
		}

		// Validate card name field for empty check.
		if (
			isset( $posted_data['card_name'] ) &&
			empty( $posted_data['card_name'] )
		) {
			$required_fields['card_name'] = array(
				'error_id'      => 'empty_card_name',
				'error_message' => __( 'Please enter a name of your credit card account holder.', 'give' ),
			);
		}

		// Validate card expiry field for empty check.
		if (
			isset( $posted_data['card_expiry'] ) &&
			empty( $posted_data['card_expiry'] )
		) {
			$required_fields['card_expiry'] = array(
				'error_id'      => 'empty_card_expiry',
				'error_message' => __( 'Please enter a credit card expiry date.', 'give' ),
			);
		}
	}

	$require_address = give_require_billing_address( $payment_mode );

	if ( $require_address ) {
		$required_fields['card_address']    = array(
			'error_id'      => 'invalid_card_address',
			'error_message' => __( 'Please enter your primary billing address.', 'give' ),
		);
		$required_fields['card_zip']        = array(
			'error_id'      => 'invalid_zip_code',
			'error_message' => __( 'Please enter your zip / postal code.', 'give' ),
		);
		$required_fields['card_city']       = array(
			'error_id'      => 'invalid_city',
			'error_message' => __( 'Please enter your billing city.', 'give' ),
		);
		$required_fields['billing_country'] = array(
			'error_id'      => 'invalid_country',
			'error_message' => __( 'Please select your billing country.', 'give' ),
		);

		$required_fields['card_state'] = array(
			'error_id'      => 'invalid_state',
			'error_message' => __( 'Please enter billing state / province / County.', 'give' ),
		);

		$country = ! empty( $_POST['billing_country'] ) ? give_clean( $_POST['billing_country'] ) : 0; // WPCS: input var ok, sanitization ok, CSRF ok.

		// Check if billing country already exists.
		if ( $country ) {

			// Check if states is empty or not.
			if ( array_key_exists( $country, give_states_not_required_country_list() ) ) {
				// If states is empty remove the required fields of state in billing cart.
				unset( $required_fields['card_state'] );
			}

			// Check if city is empty or not.
			if ( array_key_exists( $country, give_city_not_required_country_list() ) ) {
				// If states is empty remove the required fields of city in billing cart.
				unset( $required_fields['card_city'] );
			}
		}
	} // End if().

	if ( give_is_company_field_enabled( $form_id ) ) {
		$form_option    = give_get_meta( $form_id, '_give_company_field', true );
		$global_setting = give_get_option( 'company_field' );

		$is_company_field_required = false;

		if ( ! empty( $form_option ) && give_is_setting_enabled( $form_option, array( 'required' ) ) ) {
			$is_company_field_required = true;

		} elseif ( 'global' === $form_option && give_is_setting_enabled( $global_setting, array( 'required' ) ) ) {
			$is_company_field_required = true;

		} elseif ( empty( $form_option ) && give_is_setting_enabled( $global_setting, array( 'required' ) ) ) {
			$is_company_field_required = true;

		}

		if ( $is_company_field_required ) {
			$required_fields['give_company_name'] = array(
				'error_id'      => 'invalid_company',
				'error_message' => __( 'Please enter Company Name.', 'give' ),
			);
		}
	}

	/**
	 * Filters the donation form required field.
	 *
	 * @since 1.7
	 */
	$required_fields = apply_filters( 'give_donation_form_required_fields', $required_fields, $form_id );

	return $required_fields;

}

/**
 * Check if the Billing Address is required
 *
 * @since  1.0.1
 *
 * @param string $payment_mode Payment Mode.
 *
 * @return bool
 */
function give_require_billing_address( $payment_mode ) {

	$return          = false;
	$billing_country = ! empty( $_POST['billing_country'] ) ? give_clean( $_POST['billing_country'] ) : 0; // WPCS: input var ok, sanitization ok, CSRF ok.

	if ( $billing_country || did_action( "give_{$payment_mode}_cc_form" ) || did_action( 'give_cc_form' ) ) {
		$return = true;
	}

	// Let payment gateways and other extensions determine if address fields should be required.
	return apply_filters( 'give_require_billing_address', $return );

}

/**
 * Donation Form Validate Logged In User.
 *
 * @access private
 * @since  1.0
 *
 * @return array
 */
function give_donation_form_validate_logged_in_user() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
	$user_id   = get_current_user_id();
	$form_id   = ! empty( $post_data['give-form-id'] ) ? $post_data['give-form-id'] : 0;

	// Start empty array to collect valid user data.
	$valid_user_data = array(

		// Assume there will be errors.
		'user_id' => - 1,
	);

	// Proceed only, if valid $user_id found.
	if ( $user_id > 0 ) {

		// Get the logged in user data.
		$user_data = get_userdata( $user_id );

		// Validate Required Form Fields.
		give_validate_required_form_fields( $form_id );

		// Verify data.
		if ( is_object( $user_data ) && $user_data->ID > 0 ) {
			// Collected logged in user data.
			$valid_user_data = array(
				'user_id'    => $user_id,
				'user_email' => ! empty( $post_data['give_email'] )
					? sanitize_email( $post_data['give_email'] )
					: $user_data->user_email,
				'user_first' => ! empty( $post_data['give_first'] )
					? $post_data['give_first']
					: $user_data->first_name,
				'user_last'  => ! empty( $post_data['give_last'] )
					? $post_data['give_last']
					: $user_data->last_name,
			);

			// Validate essential form fields.
			give_donation_form_validate_name_fields( $post_data );

			give_check_logged_in_user_for_existing_email( $valid_user_data );

			if ( ! is_email( $valid_user_data['user_email'] ) ) {
				give_set_error( 'email_invalid', esc_html__( 'Invalid email.', 'give' ) );
			}
		} else {

			// Set invalid user information error.
			give_set_error( 'invalid_user', esc_html__( 'The user information is invalid.', 'give' ) );
		}
	}

	// Return user data.
	return $valid_user_data;
}

/**
 * Donate Form Validate New User
 *
 * @access private
 * @since  1.0
 *
 * @return array
 */
function give_donation_form_validate_new_user() {
	// Default user data.
	$auto_generated_password = wp_generate_password();
	$default_user_data       = array(
		'give-form-id'           => '',
		'user_id'                => - 1, // Assume there will be errors.
		'user_first'             => '',
		'user_last'              => '',
		'give_user_login'        => false,
		'give_email'             => false,
		'give_user_pass'         => $auto_generated_password,
		'give_user_pass_confirm' => $auto_generated_password,
	);

	// Get data.
	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
	$user_data = wp_parse_args( $post_data, $default_user_data );

	$form_id = absint( $user_data['give-form-id'] );
	$nonce   = ! empty( $post_data['give-form-user-register-hash'] ) ? $post_data['give-form-user-register-hash'] : '';

	// Validate user creation nonce.
	if ( ! wp_verify_nonce( $nonce, "give_form_create_user_nonce_{$form_id}" ) ) {
		give_set_error( 'invalid_nonce', __( 'We\'re unable to recognize your session. Please refresh the screen to try again; otherwise contact your website administrator for assistance.', 'give' ) );
	}

	$registering_new_user = false;

	give_donation_form_validate_name_fields( $user_data );

	// Start an empty array to collect valid user data.
	$valid_user_data = array(

		// Assume there will be errors.
		'user_id'    => - 1,

		// Get first name.
		'user_first' => $user_data['give_first'],

		// Get last name.
		'user_last'  => $user_data['give_last'],

		// Get Password.
		'user_pass'  => $user_data['give_user_pass'],
	);

	// Validate Required Form Fields.
	give_validate_required_form_fields( $form_id );

	// Set Email as Username.
	$valid_user_data['user_login'] = $user_data['give_email'];

	// Check if we have an email to verify.
	if ( give_validate_user_email( $user_data['give_email'], $registering_new_user ) ) {
		$valid_user_data['user_email'] = $user_data['give_email'];
	}

	return $valid_user_data;
}

/**
 * Donation Form Validate User Login
 *
 * @access private
 * @since  1.0
 *
 * @return array
 */
function give_donation_form_validate_user_login() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Start an array to collect valid user data.
	$valid_user_data = array(

		// Assume there will be errors.
		'user_id' => - 1,
	);

	// Bailout, if Username is empty.
	if ( empty( $post_data['give_user_login'] ) ) {
		give_set_error( 'must_log_in', __( 'Please enter your username or email to log in.', 'give' ) );

		return $valid_user_data;
	}

	$give_user_login = strip_tags( $post_data['give_user_login'] );
	if ( is_email( $give_user_login ) ) {
		// Get the user data by email.
		$user_data = get_user_by( 'email', $give_user_login );
	} else {
		// Get the user data by login.
		$user_data = get_user_by( 'login', $give_user_login );
	}

	// Check if user exists.
	if ( $user_data ) {

		// Get password.
		$user_pass = ! empty( $post_data['give_user_pass'] ) ? $post_data['give_user_pass'] : false;

		// Check user_pass.
		if ( $user_pass ) {

			// Check if password is valid.
			if ( ! wp_check_password( $user_pass, $user_data->user_pass, $user_data->ID ) ) {

				$current_page_url = site_url() . '/' . get_page_uri();

				// Incorrect password.
				give_set_error(
					'password_incorrect',
					sprintf(
						'%1$s <a href="%2$s">%3$s</a>',
						__( 'The password you entered is incorrect.', 'give' ),
						wp_lostpassword_url( $current_page_url ),
						__( 'Reset Password', 'give' )
					)
				);

			} else {

				// Repopulate the valid user data array.
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
			// Empty password.
			give_set_error( 'password_empty', __( 'Enter a password.', 'give' ) );
		}
	} else {
		// No username.
		give_set_error( 'username_incorrect', __( 'The username you entered does not exist.', 'give' ) );
	} // End if().

	return $valid_user_data;
}

/**
 * Donation Form Validate Guest User
 *
 * @access private
 * @since  1.0
 *
 * @return array
 */
function give_donation_form_validate_guest_user() {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.
	$form_id   = ! empty( $post_data['give-form-id'] ) ? $post_data['give-form-id'] : 0;

	// Start an array to collect valid user data.
	$valid_user_data = array(
		// Set a default id for guests.
		'user_id' => 0,
	);

	// Validate name fields.
	give_donation_form_validate_name_fields( $post_data );

	// Validate Required Form Fields.
	give_validate_required_form_fields( $form_id );

	// Get the guest email.
	$guest_email = ! empty( $post_data['give_email'] ) ? $post_data['give_email'] : false;

	// Check email.
	if ( $guest_email && strlen( $guest_email ) > 0 ) {

		// Validate email.
		if ( ! is_email( $guest_email ) ) {

			// Invalid email.
			give_set_error( 'email_invalid', __( 'Invalid email.', 'give' ) );

		} else {

			// All is good to go.
			$valid_user_data['user_email'] = $guest_email;

			// Get user_id from donor if exist.
			$donor = new Give_Donor( $guest_email );

			if ( $donor->id ) {
				$donor_email_index = array_search(
					strtolower( $guest_email ),
					array_map( 'strtolower', $donor->emails ),
					true
				);

				$valid_user_data['user_id'] = $donor->user_id;

				// Set email to original format.
				// @see https://github.com/impress-org/give/issues/4025
				$valid_user_data['user_email'] = $donor->emails[ $donor_email_index ];
			}
		}
	} else {
		// No email.
		give_set_error( 'email_empty', __( 'Enter an email.', 'give' ) );
	}

	return $valid_user_data;
}

/**
 * Register And Login New User
 *
 * @param array $user_data User Data.
 *
 * @access  private
 * @since   1.0
 *
 * @return  integer
 */
function give_register_and_login_new_user( $user_data = array() ) {
	// Verify the array.
	if ( empty( $user_data ) ) {
		return - 1;
	}

	if ( give_get_errors() ) {
		return - 1;
	}

	$user_args = apply_filters(
		'give_insert_user_args',
		array(
			'user_login'      => isset( $user_data['user_login'] ) ? $user_data['user_login'] : '',
			'user_pass'       => isset( $user_data['user_pass'] ) ? $user_data['user_pass'] : '',
			'user_email'      => isset( $user_data['user_email'] ) ? $user_data['user_email'] : '',
			'first_name'      => isset( $user_data['user_first'] ) ? $user_data['user_first'] : '',
			'last_name'       => isset( $user_data['user_last'] ) ? $user_data['user_last'] : '',
			'user_registered' => date( 'Y-m-d H:i:s' ),
			'role'            => give_get_option( 'donor_default_user_role', 'give_donor' ),
		),
		$user_data
	);

	// Insert new user.
	$user_id = wp_insert_user( $user_args );

	// Validate inserted user.
	if ( is_wp_error( $user_id ) ) {
		return - 1;
	}

	// Allow themes and plugins to filter the user data.
	$user_data = apply_filters( 'give_insert_user_data', $user_data, $user_args );

	/**
	 * Fires after inserting user.
	 *
	 * @since 1.0
	 *
	 * @param int $user_id User id.
	 * @param array $user_data Array containing user data.
	 */
	do_action( 'give_insert_user', $user_id, $user_data );

	/**
	 * Filter allow user to alter if user when to login or not when user is register for the first time.
	 *
	 * @since 1.8.13
	 *
	 * return bool True if login with registration and False if only want to register.
	 */
	if ( true === (bool) apply_filters( 'give_log_user_in_on_register', true ) ) {
		// Login new user.
		give_log_user_in( $user_id, $user_data['user_login'], $user_data['user_pass'] );
	}

	// Return user id.
	return $user_id;
}

/**
 * Get Donation Form User
 *
 * @param array $valid_data Valid Data.
 *
 * @access  private
 * @since   1.0
 *
 * @return  array|bool
 */
function give_get_donation_form_user( $valid_data = array() ) {

	// Initialize user.
	$user      = false;
	$is_ajax   = defined( 'DOING_AJAX' ) && DOING_AJAX;
	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	if ( $is_ajax ) {

		// Do not create or login the user during the ajax submission (check for errors only).
		return true;
	} elseif ( is_user_logged_in() ) {

		// Set the valid user as the logged in collected data.
		$user = $valid_data['logged_in_user'];
	} elseif ( true === $valid_data['need_new_user'] || true === $valid_data['need_user_login'] ) {

		// New user registration.
		if ( true === $valid_data['need_new_user'] ) {

			// Set user.
			$user = $valid_data['new_user_data'];

			// Register and login new user.
			$user['user_id'] = give_register_and_login_new_user( $user );

		} elseif ( true === $valid_data['need_user_login'] && ! $is_ajax ) {

			/**
			 * The login form is now processed in the give_process_donation_login() function.
			 * This is still here for backwards compatibility.
			 * This also allows the old login process to still work if a user removes the checkout login submit button.
			 *
			 * This also ensures that the donor is logged in correctly if they click "Donation" instead of submitting the login form, meaning the donor is logged in during the donation process.
			 */
			$user = $valid_data['login_user_data'];

			// Login user.
			give_log_user_in( $user['user_id'], $user['user_login'], $user['user_pass'] );
		}
	} // End if().

	// Check guest checkout.
	if ( false === $user && false === give_logged_in_only( $post_data['give-form-id'] ) ) {

		// Set user.
		$user = $valid_data['guest_user_data'];
	}

	// Verify we have an user.
	if ( false === $user || empty( $user ) ) {
		return false;
	}

	// Get user first name.
	if ( ! isset( $user['user_first'] ) || strlen( trim( $user['user_first'] ) ) < 1 ) {
		$user['user_first'] = isset( $post_data['give_first'] ) ? strip_tags( trim( $post_data['give_first'] ) ) : '';
	}

	// Get user last name.
	if ( ! isset( $user['user_last'] ) || strlen( trim( $user['user_last'] ) ) < 1 ) {
		$user['user_last'] = isset( $post_data['give_last'] ) ? strip_tags( trim( $post_data['give_last'] ) ) : '';
	}

	// Add Title Prefix to user information.
	if ( empty( $user['user_title'] ) || strlen( trim( $user['user_title'] ) ) < 1 ) {
		$user['user_title'] = ! empty( $post_data['give_title'] ) ? strip_tags( trim( $post_data['give_title'] ) ) : '';
	}

	// Get the user's billing address details.
	$user['address']            = array();
	$user['address']['line1']   = ! empty( $post_data['card_address'] ) ? $post_data['card_address'] : false;
	$user['address']['line2']   = ! empty( $post_data['card_address_2'] ) ? $post_data['card_address_2'] : false;
	$user['address']['city']    = ! empty( $post_data['card_city'] ) ? $post_data['card_city'] : false;
	$user['address']['state']   = ! empty( $post_data['card_state'] ) ? $post_data['card_state'] : false;
	$user['address']['zip']     = ! empty( $post_data['card_zip'] ) ? $post_data['card_zip'] : false;
	$user['address']['country'] = ! empty( $post_data['billing_country'] ) ? $post_data['billing_country'] : false;

	if ( empty( $user['address']['country'] ) ) {
		$user['address'] = false;
	} // End if().

	// Return valid user.
	return $user;
}

/**
 * Validates the credit card info.
 *
 * @access  private
 * @since   1.0
 *
 * @return  array
 */
function give_donation_form_validate_cc() {

	$card_data = give_get_donation_cc_info();

	// Validate the card zip.
	if ( ! empty( $card_data['card_zip'] ) ) {
		if ( ! give_donation_form_validate_cc_zip( $card_data['card_zip'], $card_data['card_country'] ) ) {
			give_set_error( 'invalid_cc_zip', __( 'The zip / postal code you entered for your billing address is invalid.', 'give' ) );
		}
	}

	// Ensure no spaces.
	if ( ! empty( $card_data['card_number'] ) ) {
		$card_data['card_number'] = str_replace( '+', '', $card_data['card_number'] ); // no "+" signs.
		$card_data['card_number'] = str_replace( ' ', '', $card_data['card_number'] ); // No spaces.
	}

	// This should validate card numbers at some point too.
	return $card_data;
}

/**
 * Get credit card info.
 *
 * @access private
 * @since  1.0
 *
 * @return array
 */
function give_get_donation_cc_info() {

	// Sanitize the values submitted with donation form.
	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	$cc_info                   = array();
	$cc_info['card_name']      = ! empty( $post_data['card_name'] ) ? $post_data['card_name'] : '';
	$cc_info['card_number']    = ! empty( $post_data['card_number'] ) ? $post_data['card_number'] : '';
	$cc_info['card_cvc']       = ! empty( $post_data['card_cvc'] ) ? $post_data['card_cvc'] : '';
	$cc_info['card_exp_month'] = ! empty( $post_data['card_exp_month'] ) ? $post_data['card_exp_month'] : '';
	$cc_info['card_exp_year']  = ! empty( $post_data['card_exp_year'] ) ? $post_data['card_exp_year'] : '';
	$cc_info['card_address']   = ! empty( $post_data['card_address'] ) ? $post_data['card_address'] : '';
	$cc_info['card_address_2'] = ! empty( $post_data['card_address_2'] ) ? $post_data['card_address_2'] : '';
	$cc_info['card_city']      = ! empty( $post_data['card_city'] ) ? $post_data['card_city'] : '';
	$cc_info['card_state']     = ! empty( $post_data['card_state'] ) ? $post_data['card_state'] : '';
	$cc_info['card_country']   = ! empty( $post_data['billing_country'] ) ? $post_data['billing_country'] : '';
	$cc_info['card_zip']       = ! empty( $post_data['card_zip'] ) ? $post_data['card_zip'] : '';

	// Return cc info.
	return $cc_info;
}

/**
 * Validate zip code based on country code
 *
 * @since  1.0
 *
 * @param int    $zip          ZIP Code.
 * @param string $country_code Country Code.
 *
 * @return bool|mixed
 */
function give_donation_form_validate_cc_zip( $zip = 0, $country_code = '' ) {
	$ret = false;

	if ( empty( $zip ) || empty( $country_code ) ) {
		return $ret;
	}

	$country_code = strtoupper( $country_code );

	$zip_regex = array(
		'AD' => 'AD\d{3}',
		'AM' => '(37)?\d{4}',
		'AR' => '^([A-Z]{1}\d{4}[A-Z]{3}|[A-Z]{1}\d{4}|\d{4})$',
		'AS' => '96799',
		'AT' => '\d{4}',
		'AU' => '^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$',
		'AX' => '22\d{3}',
		'AZ' => '\d{4}',
		'BA' => '\d{5}',
		'BB' => '(BB\d{5})?',
		'BD' => '\d{4}',
		'BE' => '^[1-9]{1}[0-9]{3}$',
		'BG' => '\d{4}',
		'BH' => '((1[0-2]|[2-9])\d{2})?',
		'BM' => '[A-Z]{2}[ ]?[A-Z0-9]{2}',
		'BN' => '[A-Z]{2}[ ]?\d{4}',
		'BR' => '\d{5}[\-]?\d{3}',
		'BY' => '\d{6}',
		'CA' => '^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$',
		'CC' => '6799',
		'CH' => '^[1-9][0-9][0-9][0-9]$',
		'CK' => '\d{4}',
		'CL' => '\d{7}',
		'CN' => '\d{6}',
		'CR' => '\d{4,5}|\d{3}-\d{4}',
		'CS' => '\d{5}',
		'CV' => '\d{4}',
		'CX' => '6798',
		'CY' => '\d{4}',
		'CZ' => '\d{3}[ ]?\d{2}',
		'DE' => '\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b',
		'DK' => '^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$',
		'DO' => '\d{5}',
		'DZ' => '\d{5}',
		'EC' => '([A-Z]\d{4}[A-Z]|(?:[A-Z]{2})?\d{6})?',
		'EE' => '\d{5}',
		'EG' => '\d{5}',
		'ES' => '^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$',
		'ET' => '\d{4}',
		'FI' => '\d{5}',
		'FK' => 'FIQQ 1ZZ',
		'FM' => '(9694[1-4])([ \-]\d{4})?',
		'FO' => '\d{3}',
		'FR' => '^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$',
		'GE' => '\d{4}',
		'GF' => '9[78]3\d{2}',
		'GL' => '39\d{2}',
		'GN' => '\d{3}',
		'GP' => '9[78][01]\d{2}',
		'GR' => '\d{3}[ ]?\d{2}',
		'GS' => 'SIQQ 1ZZ',
		'GT' => '\d{5}',
		'GU' => '969[123]\d([ \-]\d{4})?',
		'GW' => '\d{4}',
		'HM' => '\d{4}',
		'HN' => '(?:\d{5})?',
		'HR' => '\d{5}',
		'HT' => '\d{4}',
		'HU' => '\d{4}',
		'ID' => '\d{5}',
		'IE' => '((D|DUBLIN)?([1-9]|6[wW]|1[0-8]|2[024]))?',
		'IL' => '\d{5}',
		'IN' => '^[1-9][0-9][0-9][0-9][0-9][0-9]$', // India.
		'IO' => 'BBND 1ZZ',
		'IQ' => '\d{5}',
		'IS' => '\d{3}',
		'IT' => '^(V-|I-)?[0-9]{5}$',
		'JO' => '\d{5}',
		'JP' => '\d{3}-\d{4}',
		'KE' => '\d{5}',
		'KG' => '\d{6}',
		'KH' => '\d{5}',
		'KR' => '\d{3}[\-]\d{3}',
		'KW' => '\d{5}',
		'KZ' => '\d{6}',
		'LA' => '\d{5}',
		'LB' => '(\d{4}([ ]?\d{4})?)?',
		'LI' => '(948[5-9])|(949[0-7])',
		'LK' => '\d{5}',
		'LR' => '\d{4}',
		'LS' => '\d{3}',
		'LT' => '\d{5}',
		'LU' => '\d{4}',
		'LV' => '\d{4}',
		'MA' => '\d{5}',
		'MC' => '980\d{2}',
		'MD' => '\d{4}',
		'ME' => '8\d{4}',
		'MG' => '\d{3}',
		'MH' => '969[67]\d([ \-]\d{4})?',
		'MK' => '\d{4}',
		'MN' => '\d{6}',
		'MP' => '9695[012]([ \-]\d{4})?',
		'MQ' => '9[78]2\d{2}',
		'MT' => '[A-Z]{3}[ ]?\d{2,4}',
		'MU' => '(\d{3}[A-Z]{2}\d{3})?',
		'MV' => '\d{5}',
		'MX' => '\d{5}',
		'MY' => '\d{5}',
		'NC' => '988\d{2}',
		'NE' => '\d{4}',
		'NF' => '2899',
		'NG' => '(\d{6})?',
		'NI' => '((\d{4}-)?\d{3}-\d{3}(-\d{1})?)?',
		'NL' => '^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$',
		'NO' => '\d{4}',
		'NP' => '\d{5}',
		'NZ' => '\d{4}',
		'OM' => '(PC )?\d{3}',
		'PF' => '987\d{2}',
		'PG' => '\d{3}',
		'PH' => '\d{4}',
		'PK' => '\d{5}',
		'PL' => '\d{2}-\d{3}',
		'PM' => '9[78]5\d{2}',
		'PN' => 'PCRN 1ZZ',
		'PR' => '00[679]\d{2}([ \-]\d{4})?',
		'PT' => '\d{4}([\-]\d{3})?',
		'PW' => '96940',
		'PY' => '\d{4}',
		'RE' => '9[78]4\d{2}',
		'RO' => '\d{6}',
		'RS' => '\d{5}',
		'RU' => '\d{6}',
		'SA' => '\d{5}',
		'SE' => '^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$',
		'SG' => '\d{6}',
		'SH' => '(ASCN|STHL) 1ZZ',
		'SI' => '\d{4}',
		'SJ' => '\d{4}',
		'SK' => '\d{3}[ ]?\d{2}',
		'SM' => '4789\d',
		'SN' => '\d{5}',
		'SO' => '\d{5}',
		'SZ' => '[HLMS]\d{3}',
		'TC' => 'TKCA 1ZZ',
		'TH' => '\d{5}',
		'TJ' => '\d{6}',
		'TM' => '\d{6}',
		'TN' => '\d{4}',
		'TR' => '\d{5}',
		'TW' => '\d{3}(\d{2})?',
		'UA' => '\d{5}',
		'UK' => '^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$',
		'US' => '^\d{5}([\-]?\d{4})?$',
		'UY' => '\d{5}',
		'UZ' => '\d{6}',
		'VA' => '00120',
		'VE' => '\d{4}',
		'VI' => '008(([0-4]\d)|(5[01]))([ \-]\d{4})?',
		'WF' => '986\d{2}',
		'YT' => '976\d{2}',
		'YU' => '\d{5}',
		'ZA' => '\d{4}',
		'ZM' => '\d{5}',
	);

	if ( ! isset( $zip_regex[ $country_code ] ) || preg_match( '/' . $zip_regex[ $country_code ] . '/i', $zip ) ) {
		$ret = true;
	}

	return apply_filters( 'give_is_zip_valid', $ret, $zip, $country_code );
}

/**
 * Validate donation amount and auto set correct donation level id on basis of amount.
 *
 * Note: If amount does not match to donation level amount then level id will be auto select to first match level id on basis of amount.
 *
 * @param array $valid_data List of Valid Data.
 *
 * @return bool
 */
function give_validate_donation_amount( $valid_data ) {

	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	/* @var Give_Donate_Form $form */
	$form = new Give_Donate_Form( $post_data['give-form-id'] );

	// Get the form currency.
	$form_currency = give_get_currency( $post_data['give-form-id'] );

	$donation_level_matched = false;

	if ( $form->is_set_type_donation_form() ) {

		// Sanitize donation amount.
		$post_data['give-amount'] = give_maybe_sanitize_amount( $post_data['give-amount'], array( 'currency' => $form_currency ) );

		// Backward compatibility.
		if ( $form->is_custom_price( $post_data['give-amount'] ) ) {
			$post_data['give-price-id'] = 'custom';
		}

		$donation_level_matched = true;

	} elseif ( $form->is_multi_type_donation_form() ) {

		$variable_prices = $form->get_prices();

		// Bailout.
		if ( ! $variable_prices ) {
			return false;
		}

		// Sanitize donation amount.
		$post_data['give-amount']     = give_maybe_sanitize_amount( $post_data['give-amount'], array( 'currency' => $form_currency ) );
		$variable_price_option_amount = give_maybe_sanitize_amount( give_get_price_option_amount( $post_data['give-form-id'], $post_data['give-price-id'] ), array( 'currency' => $form_currency ) );
		$new_price_id                 = '';

		if ( $post_data['give-amount'] === $variable_price_option_amount ) {
			return true;
		}

		if ( $form->is_custom_price( $post_data['give-amount'] ) ) {
			$new_price_id = 'custom';
		} else {

			// Find correct donation level from all donation levels.
			foreach ( $variable_prices as $variable_price ) {

				// Sanitize level amount.
				$variable_price['_give_amount'] = give_maybe_sanitize_amount( $variable_price['_give_amount'] );

				// Set first match donation level ID.
				if ( $post_data['give-amount'] === $variable_price['_give_amount'] ) {
					$new_price_id = $variable_price['_give_id']['level_id'];
					break;
				}
			}
		}

		// If donation amount is not find in donation levels then check if form has custom donation feature enable or not.
		// If yes then set price id to custom if amount is greater then custom minimum amount (if any).
		if ( $post_data['give-price-id'] === $new_price_id ) {
			$donation_level_matched = true;
		}
	} // End if().

	if ( ! $donation_level_matched ) {
		give_set_error(
			'invalid_donation_amount',
			sprintf(
				/* translators: %s: invalid donation amount */
				__( 'Donation amount %s is invalid.', 'give' ),
				give_currency_filter(
					give_format_amount( $post_data['give-amount'], array( 'sanitize' => false ) )
				)
			)
		);
	}
}

add_action( 'give_checkout_error_checks', 'give_validate_donation_amount', 10, 1 );

/**
 * Validate Required Form Fields.
 *
 * @param int $form_id Form ID.
 *
 * @since 2.0
 */
function give_validate_required_form_fields( $form_id ) {

	// Sanitize values submitted with donation form.
	$post_data = give_clean( $_POST ); // WPCS: input var ok, sanitization ok, CSRF ok.

	// Loop through required fields and show error messages.
	foreach ( give_get_required_fields( $form_id ) as $field_name => $value ) {

		// Clean Up Data of the input fields.
		$field_value = $post_data[ $field_name ];

		// Check whether the required field is empty, then show the error message.
		if ( in_array( $value, give_get_required_fields( $form_id ), true ) && empty( $field_value ) ) {
			give_set_error( $value['error_id'], $value['error_message'] );
		}
	}
}

/**
 * Validates and checks if name fields are valid or not.
 *
 * @param array $post_data List of post data.
 *
 * @since 2.1
 *
 * @return void
 */
function give_donation_form_validate_name_fields( $post_data ) {

	$is_alpha_first_name = ( ! is_email( $post_data['give_first'] ) && ! preg_match( '~[0-9]~', $post_data['give_first'] ) );
	$is_alpha_last_name  = ( ! is_email( $post_data['give_last'] ) && ! preg_match( '~[0-9]~', $post_data['give_last'] ) );

	if ( ! $is_alpha_first_name || ( ! empty( $post_data['give_last'] ) && ! $is_alpha_last_name ) ) {
		give_set_error( 'invalid_name', esc_html__( 'The First Name and Last Name fields cannot contain an email address or numbers.', 'give' ) );
	}
}
