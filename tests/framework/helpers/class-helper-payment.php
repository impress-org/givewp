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
		give_delete_donation( $payment_id );
	}

	/**
	 * Create a simple donation payment.
	 *
	 * @since 1.0
	 *
	 * @param array $donation
	 *
	 * @return int|Give_Payment
	 */
	public static function create_simple_payment( $donation = array() ) {
		// Setup donation
		$user      = get_userdata( 1 );
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		// setup form.
		$simple_form  = Give_Helper_Form::create_simple_form();
		$simple_price = give_get_meta( $simple_form->ID, '_give_set_price', true );

		// Set date if not set already.
		if ( empty( $date ) ) {
			$date = date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		}

		$donation = wp_parse_args(
			( ! empty( $donation['donation'] ) ? $donation['donation'] : array() ),
			array(
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
			)
		);

		return self::create_payment( array( 'donation' => $donation ) );

	}

	/**
	 * Create a simple payment.
	 *
	 * @since 2.3
	 *
	 * @param array $donation
	 *
	 * @return int|Give_Payment
	 */
	public static function create_simple_guest_payment( $donation = array() ) {
		// Setup user info.
		$user_info = array(
			'id'         => 0,
			'email'      => 'guest@example.org',
			'first_name' => 'Guest',
			'last_name'  => 'User',
			'discount'   => 'none',
		);

		// Setup simple donation form.
		$simple_form  = Give_Helper_Form::create_simple_form();
		$simple_price = give_get_meta( $simple_form->ID, '_give_set_price', true );

		$donation = wp_parse_args(
			( ! empty( $args['donation'] ) ? $args['donation'] : array() ),
			array(
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
			)
		);

		return self::create_payment( array( 'donation' => $donation ) );

	}

	/**
	 * Creates a multi-level (variable) donation payment.
	 *
	 * @since 1.0
	 *
	 * @param array $donation
	 *
	 * @return int|Give_Payment
	 */
	public static function create_multilevel_payment( $donation = array() ) {
		// Setup user info.
		$user      = get_userdata( 1 );
		$user_info = array(
			'id'         => $user->ID,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		// Setup multi-level donation form
		$multilevel_form = Give_Helper_Form::create_multilevel_form();

		// Get donation levels
		$multilevel_price = give_get_meta( $multilevel_form->ID, '_give_donation_levels', true );

		// Setup donation data.
		$donation = wp_parse_args(
			( ! empty( $args['donation'] ) ? $args['donation'] : array() ),
			array(
				'price'           => number_format( (float) $multilevel_price[1]['_give_amount'], 2 ), // $25
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
			)
		);

		return self::create_payment( array( 'donation' => $donation ) );
	}

	/**
	 * Creates a multi-level (variable) donation payment.
	 *
	 * @since 2.0
	 *
	 * @param array $donation Donation arguments.
	 *
	 * @return int|Give_Payment
	 */
	public static function create_payment( $donation = array() ) {
		$payment_id    = 0;
		$donation_args = ! empty( $donation['donation'] ) ? $donation['donation'] : array();
		$meta          = ! empty( $donation['meta'] ) ? $donation['meta'] : array();
		$result_type   = ! empty( $donation['result_type'] ) ? $donation['result_type'] : 'int';

		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		try {
			// Insert form.
			if ( ! empty( $donation_args ) ) {
				$payment_id = give_insert_payment( $donation_args );
			} else {
				throw new Exception( __( 'Empty donation argument is not valid to setup donation.', 'give' ) );
			}

			if ( ! is_wp_error( $payment_id ) && ! empty( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					give_update_meta( $payment_id, $key, $value );
				}
			}
		} catch ( Exception $e ) {
			echo "\n{$e->getMessage()}";
		}

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

		return 'int' === $result_type ?
			$payment_id :
			new Give_Payment( $payment_id );
	}

}
