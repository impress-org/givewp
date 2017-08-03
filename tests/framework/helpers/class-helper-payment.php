<?php

/**
 * Class Give_Helper_Payment.
 *
 * Helper class to create and delete a donation payment easily.
 */
class Give_Helper_Payment extends Give_Unit_Test_Case {

	/**
	 * Delete a payment.
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id ID of the payment to delete.
	 */
	public static function delete_payment( $payment_id ) {
		// Delete the payment.
		give_delete_donation( $payment_id );
	}

	/**
	 * Create a simple donation payment.
	 *
	 * @since 1.0
	 */
	public static function create_simple_payment( $date = '' ) {


		// Sequential order coming soon.
		//		$give_options = give_get_settings();
		//		$give_options['enable_sequential'] = '1'; //Not yet in use
		//		$give_options['sequential_prefix'] = 'GIVE-'; //Not yet in use
		//		update_option( 'give_settings', $give_options );

		$simple_form = Give_Helper_Form::create_simple_form();

		$user      = get_userdata( 1 );
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		$simple_price = give_get_meta( $simple_form->ID, '_give_set_price', true );

		// Set date if not set already.
		if ( empty( $date ) ) {
			$date = date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		}

		$donation_data = array(
			'price'           => number_format( (float) $simple_price, 2 ),
			'give_form_title' => 'Test Donation Form',
			'give_form_id'    => $simple_form->ID,
			'date'            => $date,
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual',
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $donation_data );

		$transaction_id          = 'FIR3SID3';
		$payment                 = new Give_Payment( $payment_id );
		$payment->transaction_id = $transaction_id;
		$payment->save();

		give_insert_payment_note(
			$payment_id,
			sprintf(
			/* translators: %s: Paypal transaction id */
				esc_html__( 'PayPal Transaction ID: %s', 'give' ),
				$transaction_id
			)
		);

		return $payment_id;

	}

	/**
	 * Create a simple payment.
	 *
	 * @since 2.3
	 */
	public static function create_simple_guest_payment() {

		$simple_form = Give_Helper_Form::create_simple_form();

		$user_info = array(
			'id'         => 0,
			'email'      => 'guest@example.org',
			'first_name' => 'Guest',
			'last_name'  => 'User',
			'discount'   => 'none',
		);

		$simple_price = give_get_meta( $simple_form->ID, '_give_set_price', true );

		$donation_data = array(
			'price'           => number_format( (float) $simple_price, 2 ),
			'give_form_title' => 'Test Donation Form',
			'give_form_id'    => $simple_form->ID,
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual',
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $donation_data );

		$transaction_id          = 'FIR3SID3';
		$payment                 = new Give_Payment( $payment_id );
		$payment->transaction_id = $transaction_id;
		$payment->save();

		give_insert_payment_note(
			$payment_id,
			sprintf(
			/* translators: %s: Paypal transaction id */
				esc_html__( 'PayPal Transaction ID: %s', 'give' ),
				$transaction_id
			)
		);

		return $payment_id;

	}

	/**
	 * Creates a multi-level (variable) donation payment.
	 *
	 * @since 1.0
	 */
	public static function create_multilevel_payment() {

		$give_options = give_get_settings();

		// Enable a few options
		//		$give_options['enable_sequential'] = '1'; //Not yet in use
		//		$give_options['sequential_prefix'] = 'GIVE-'; //Not yet in use
		update_option( 'give_settings', $give_options );

		$multilevel_form = Give_Helper_Form::create_multilevel_form();

		// Generate some donations
		$user      = get_userdata( 1 );
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		$multilevel_price = maybe_unserialize( give_get_meta( $multilevel_form->ID, '_give_donation_levels', true ) );

		$donation_data = array(
			'price'           => number_format( (float) $multilevel_price[1]['_give_amount'], 2 ), //$25
			'give_form_title' => $multilevel_form->post_title,
			'give_form_id'    => $multilevel_form->ID,
			'give_price_id'   => $multilevel_price[1]['_give_id']['level_id'],
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual',
		);

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $donation_data );

		$transaction_id          = 'FIR3SID3';
		$payment                 = new Give_Payment( $payment_id );
		$payment->transaction_id = $transaction_id;
		$payment->save();

		give_insert_payment_note(
			$payment_id,
			sprintf(
			/* translators: %s: Paypal transaction id */
				esc_html__( 'PayPal Transaction ID: %s', 'give' ),
				$transaction_id
			)
		);

		return $payment_id;

	}


}
