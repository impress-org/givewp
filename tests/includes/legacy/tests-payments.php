<?php

/**
 * Class Tests_Payments
 */
class Tests_Payments extends Give_Unit_Test_Case {

	/**
	 * @var null
	 */
	protected $_payment_id = null;

	/**
	 * @var null
	 */
	protected $_key = null;

	/**
	 * @var null
	 */
	protected $_post = null;

	/**
	 * @var null
	 */
	protected $_payment_key = null;

	/**
	 * @var null
	 */
	protected $_transaction_id = null;

	/**
	 * Set it up.
	 */
	public function setUp() {

		parent::setUp();

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$this->_payment_key    = give_get_payment_key( $payment_id );
		$this->_payment_id     = $payment_id;
		$this->_key            = $this->_payment_key;
		$this->_transaction_id = 'FIR3SID3';

		give_set_payment_transaction_id( $payment_id, $this->_transaction_id );
		give_insert_payment_note( $payment_id, sprintf( __( 'PayPal Transaction ID: %s', 'give' ), $this->_transaction_id ) );

		// Make sure we're working off a clean object caching in WP Core.
		// Prevents some payment_meta from not being present.
		clean_post_cache( $payment_id );
		update_postmeta_cache( array( $payment_id ) );
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {

		parent::tearDown();
		Give_Helper_Payment::delete_payment( $this->_payment_id );
		wp_cache_flush();

	}

	/**
	 * Test getting payments.
	 */
	public function test_get_payments() {
		$out = give_get_payments();
		$this->assertTrue( is_array( (array) $out[0] ) );
		$this->assertArrayHasKey( 'ID', (array) $out[0] );
		$this->assertArrayHasKey( 'post_type', (array) $out[0] );
		$this->assertEquals( 'give_payment', $out[0]->post_type );
	}

	/**
	 * Test give_payments query.
	 */
	public function test_payments_query_give_payments() {
		$payments = new Give_Payments_Query(
			array(
				'output' => 'give_payments',
			)
		);
		$out      = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'date' ) );
		$this->assertTrue( property_exists( $out[0], 'form_id' ) );
		$this->assertTrue( property_exists( $out[0], 'form_title' ) );
		$this->assertTrue( property_exists( $out[0], 'mode' ) );
		$this->assertTrue( property_exists( $out[0], 'first_name' ) );
		$this->assertTrue( property_exists( $out[0], 'last_name' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	/**
	 * Test payments query.
	 */
	public function test_payments_query_payments() {
		$payments = new Give_Payments_Query(
			array(
				'output' => 'payments',
			)
		);
		$out      = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'date' ) );
		$this->assertTrue( property_exists( $out[0], 'form_id' ) );
		$this->assertTrue( property_exists( $out[0], 'form_title' ) );
		$this->assertTrue( property_exists( $out[0], 'mode' ) );
		$this->assertTrue( property_exists( $out[0], 'first_name' ) );
		$this->assertTrue( property_exists( $out[0], 'last_name' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	/**
	 * Test default query.
	 */
	public function test_payments_query_default() {
		$payments = new Give_Payments_Query();
		$out      = $payments->get_payments();
		$this->assertTrue( is_object( $out[0] ) );
		$this->assertTrue( property_exists( $out[0], 'ID' ) );
		$this->assertTrue( property_exists( $out[0], 'date' ) );
		$this->assertTrue( property_exists( $out[0], 'form_id' ) );
		$this->assertTrue( property_exists( $out[0], 'form_title' ) );
		$this->assertTrue( property_exists( $out[0], 'mode' ) );
		$this->assertTrue( property_exists( $out[0], 'first_name' ) );
		$this->assertTrue( property_exists( $out[0], 'last_name' ) );
		$this->assertTrue( property_exists( $out[0], 'user_info' ) );
	}

	/**
	 * Test get payment by.
	 */
	public function test_give_get_payment_by() {
		$payment = give_get_payment_by( 'id', $this->_payment_id );
		$this->assertObjectHasAttribute( 'ID', $payment );

		$payment = give_get_payment_by( 'key', $this->_key );
		$this->assertObjectHasAttribute( 'ID', $payment );
	}

	/**
	 * Test inserting a bad payment.
	 */
	public function test_fake_insert_payment() {
		$this->assertFalse( give_insert_payment() );
	}

	/**
	 * Test that the completed flag.
	 */
	public function test_payment_completed_flag_not_exists() {

		$completed_date = give_get_payment_completed_date( $this->_payment_id );
		$this->assertEmpty( $completed_date );

	}

	/**
	 * Test updating a payment's status.
	 */
	public function test_update_payment_status() {
		give_update_payment_status( $this->_payment_id, 'publish' );

		$out = give_get_payments();
		$this->assertEquals( 'publish', $out[0]->post_status );
	}

	/**
	 * Test updating a payment with a bad ID.
	 */
	public function test_update_payment_status_with_invalid_id() {
		$updated = give_update_payment_status( 1212121212121212121212112, 'publish' );
		$this->assertFalse( $updated );
	}

	/**
	 * Test checking an existing payment.
	 */
	public function test_check_for_existing_payment() {
		give_update_payment_status( $this->_payment_id, 'publish' );
		$this->assertTrue( give_check_for_existing_payment( $this->_payment_id ) );
	}

	/**
	 * Test getting a payment by status.
	 */
	public function test_get_payment_status() {
		$this->assertEquals( 'pending', give_get_payment_status( $this->_payment_id ) );
		$this->assertEquals( 'pending', give_get_payment_status( get_post( $this->_payment_id ) ) );
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 'pending', give_get_payment_status( $payment ) );
		$this->assertFalse( give_get_payment_status( 1212121212121 ) );
	}

	public function test_get_payment_status_label() {
		$this->assertEquals( 'Pending', give_get_payment_status( $this->_payment_id, true ) );
		$this->assertEquals( 'Pending', give_get_payment_status( get_post( $this->_payment_id ), true ) );
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( 'Pending', give_get_payment_status( $payment, true ) );
	}

	/**
	 * Test get payment statuses.
	 */
	public function test_get_payment_statuses() {
		$out = give_get_payment_statuses();

		$expected = array(
			'pending'     => 'Pending',
			'publish'     => 'Complete',
			'refunded'    => 'Refunded',
			'processing'  => 'Processing',
			'cancelled'   => 'Cancelled',
			'preapproval' => 'Pre-Approved',
			'failed'      => 'Failed',
			'revoked'     => 'Revoked',
			'abandoned'   => 'Abandoned',
		);

		$this->assertEquals( $expected, $out );
	}

	/**
	 * Test get payment status keys.
	 */
	public function test_get_payment_status_keys() {
		$out = give_get_payment_status_keys();

		$expected = array(
			'abandoned',
			'cancelled',
			'failed',
			'pending',
			'preapproval',
			'processing',
			'publish',
			'refunded',
			'revoked',
		);

		$this->assertInternalType( 'array', $out );
		$this->assertEquals( $expected, $out );
	}

	/**
	 * Test deleting a donation.
	 */
	public function test_delete_donation() {
		give_delete_donation( $this->_payment_id );
		// This returns an empty array(), so empty makes it false.
		$cart = give_get_payments();
		$this->assertTrue( empty( $cart ) );
	}

	/**
	 * Test getting a payment's completed date.
	 */
	public function test_get_payment_completed_date() {

		give_update_payment_status( $this->_payment_id, 'publish' );
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertInternalType( 'string', $payment->completed_date );
		$this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $payment->completed_date ) ) );

	}

	/**
	 * Test the helper functions.
	 */
	public function test_get_payment_completed_date_functions() {

		give_update_payment_status( $this->_payment_id, 'publish' );
		$completed_date = give_get_payment_completed_date( $this->_payment_id );
		$this->assertInternalType( 'string', $completed_date );
		$this->assertEquals( date( 'Y-m-d' ), date( 'Y-m-d', strtotime( $completed_date ) ) );

	}

	/**
	 * Test getting the payment number.
	 */
	public function test_get_payment_number() {
		// Reset all items and start from scratch.
		Give_Helper_Payment::delete_payment( $this->_payment_id );
		wp_cache_flush();

		/**
		 * Case 1: enable sequential donation
		 */
		$payment_id = Give_Helper_Payment::create_simple_payment();

		$payment = new Give_Payment( $payment_id );
		$this->assertEquals( Give()->seq_donation_number->get_serial_number( $payment_id ), $payment->number );

		/**
		 * Case 2: enable sequential donation with prefix and suffix
		 */
		give_update_option( 'sequential-ordering_number_prefix', 'Give-' );
		give_update_option( 'sequential-ordering_number_suffix', '-WP' );

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$payment = new Give_Payment( $payment_id );
		$this->assertEquals( Give()->seq_donation_number->get_serial_code( $payment_id ), $payment->number );

		// Reset option.
		give_update_option( 'sequential-ordering_number_prefix', '' );
		give_update_option( 'sequential-ordering_number_suffix', '' );

		/**
		 * Case 3: enable sequential donation with prefix and suffix with date tag
		 */
		give_update_option( 'sequential-ordering_number_prefix', 'Give-' );
		give_update_option( 'sequential-ordering_number_suffix', '-WP-{YYYY}-{MM}-{DD}' );

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$payment      = new Give_Payment( $payment_id );
		$current_time = current_time( 'timestamp' );
		$this->assertEquals(
			'Give-' . Give()->seq_donation_number->get_serial_number( $payment_id ) . '-WP-' . date( 'Y', $current_time ) . '-' . date( 'm', $current_time ) . '-' . date( 'd', $current_time ),
			$payment->number
		);

		// Reset option.
		give_update_option( 'sequential-ordering_number_prefix', '' );
		give_update_option( 'sequential-ordering_number_suffix', '' );

		/**
		 * Case 3: enable sequential donation with prefix, suffix and custom starting number
		 */
		give_update_option( 'sequential-ordering_number_prefix', 'Give-' );
		give_update_option( 'sequential-ordering_number_suffix', '-WP' );
		update_option( '_give_reset_sequential_number', 1 );
		give_update_option( 'sequential-ordering_number', 400 );

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$payment = new Give_Payment( $payment_id );
		$this->assertEquals( Give()->seq_donation_number->get_serial_code( $payment_id ), $payment->number );

		// Reset option.
		give_update_option( 'sequential-ordering_number_prefix', '' );
		give_update_option( 'sequential-ordering_number_suffix', '' );
		give_update_option( 'sequential-ordering_number', 400 );

		/**
		 * Case 2: disable sequential donation.
		 */
		give_update_option( 'sequential-ordering_status', 'disabled' );

		// Now disable sequential and ensure values come back as expected
		$payment_id = Give_Helper_Payment::create_simple_payment();
		$payment    = new Give_Payment( $payment_id );
		$this->assertEquals( $payment_id, $payment->number );

		give_update_option( 'sequential-ordering_status', 'enabled' );
	}

	/**
	 * Test getting the transaction ID.
	 */
	public function test_get_payment_transaction_id() {
		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( $this->_transaction_id, $payment->transaction_id );
	}

	/**
	 * Test getting a payment transaction ID by function.
	 */
	public function test_get_payment_transaction_id_function() {
		$this->assertEquals( $this->_transaction_id, give_get_payment_transaction_id( $this->_payment_id ) );
	}


	/**
	 * Test get payment meta using Give_Payment.
	 */
	public function test_get_payment_meta() {

		$payment = new Give_Payment( $this->_payment_id );

		// Test by getting the payment key with three different methods
		$this->assertEquals( $this->_payment_key, $payment->get_meta( '_give_payment_purchase_key' ) );
		$this->assertEquals( $this->_payment_key, give_get_payment_meta( $this->_payment_id, '_give_payment_purchase_key', true ) );
		$this->assertEquals( $this->_payment_key, $payment->key );

		// Try and retrieve the transaction ID
		$this->assertEquals( $this->_transaction_id, $payment->get_meta( '_give_payment_transaction_id' ) );

		$this->assertEquals( $payment->email, $payment->get_meta( '_give_payment_donor_email' ) );

	}

	/**
	 * Test get payment meta using functions.
	 */
	public function test_get_payment_meta_functions() {

		// Test by getting the payment key with three different methods
		$this->assertEquals( $this->_payment_key, give_get_payment_meta( $this->_payment_id, '_give_payment_purchase_key' ) );
		$this->assertEquals( $this->_payment_key, give_get_payment_meta( $this->_payment_id, '_give_payment_purchase_key', true ) );
		$this->assertEquals( $this->_payment_key, give_get_payment_key( $this->_payment_id ) );

		// Try and retrieve the transaction ID
		$this->assertEquals( $this->_transaction_id, give_get_payment_meta( $this->_payment_id, '_give_payment_transaction_id' ) );

		$user_info = give_get_payment_meta_user_info( $this->_payment_id );
		$this->assertEquals( $user_info['email'], give_get_payment_meta( $this->_payment_id, '_give_payment_donor_email' ) );

	}

	/**
	 * Test updating payment meta using Give_Payment.
	 */
	public function test_update_payment_meta() {

		$payment = new Give_Payment( $this->_payment_id );
		$this->assertEquals( $payment->key, $payment->get_meta( '_give_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $payment->key, $new_value );

		$payment->key = $new_value;
		$ret          = $payment->save();

		$this->assertTrue( $ret );
		$this->assertEquals( $new_value, $payment->key );

		$payment->email = 'test@test.com';
		$ret            = $payment->save();

		$this->assertTrue( $ret );

		$this->assertEquals( 'test@test.com', $payment->email );

	}

	/**
	 * Test payment meta using functions.
	 */
	public function test_update_payment_meta_functions() {

		$old_value = $this->_payment_key;
		$this->assertEquals( $old_value, give_get_payment_meta( $this->_payment_id, '_give_payment_purchase_key' ) );

		$new_value = 'test12345';
		$this->assertNotEquals( $old_value, $new_value );

		$ret = give_update_payment_meta( $this->_payment_id, '_give_payment_purchase_key', $new_value );

		$this->assertTrue( $ret );

		$this->assertEquals( $new_value, give_get_payment_meta( $this->_payment_id, '_give_payment_purchase_key' ) );

		$ret = give_update_payment_meta( $this->_payment_id, '_give_payment_donor_email', 'test@test.com' );

		$this->assertTrue( $ret );

		$user_info = give_get_payment_meta_user_info( $this->_payment_id );
		$this->assertEquals( 'test@test.com', give_get_payment_meta( $this->_payment_id, '_give_payment_donor_email' ) );

	}

	/**
	 * Test update payment data.
	 */
	public function test_update_payment_data() {

		$payment       = new Give_Payment( $this->_payment_id );
		$payment->date = date( 'Y-m-d H:i:s' );
		$payment->save();
		$meta = $payment->get_meta();

		// substr to ensure travis CI tests don't fail based off of seconds delay.
		$this->assertSame( substr( $payment->date, 0, 15 ), substr( $meta['date'], 0, 15 ) );

	}

	/**
	 * Test currency using Give_Payment class.
	 */
	public function test_get_payment_currency_code() {

		$payment = new Give_Payment( $this->_payment_id );

		$this->assertEquals( 'USD', $payment->currency );
		$this->assertEquals( 'US Dollars', give_get_payment_currency( $payment->ID ) );

		$total1 = give_currency_filter( give_format_amount( $payment->total ), array( 'currency_code' => $payment->currency ) );
		$total2 = give_currency_filter( give_format_amount( $payment->total ) );

		$this->assertEquals( '&#36;20.00', $total1 );
		$this->assertEquals( '&#36;20.00', $total2 );

	}

	/**
	 * Test the currency helper functions.
	 */
	public function test_get_payment_currency_code_functions() {

		$this->assertEquals( 'USD', give_get_payment_currency_code( $this->_payment_id ) );
		$this->assertEquals( 'US Dollars', give_get_payment_currency( $this->_payment_id ) );

		$total1 = give_donation_amount( $this->_payment_id, true );
		$total2 = give_donation_amount( $this->_payment_id, true );
		$total3 = give_donation_amount(
			$this->_payment_id,
			array(
				'currency' => true,
				'amount'   => true,
				'type'     => 'stats',
			)
		);
		$total4 = give_donation_amount(
			$this->_payment_id,
			array(
				'currency' => true,
				'amount'   => true,
				'type'     => 'stats',
			)
		);

		$this->assertEquals( '&#36;20.00', $total1 );
		$this->assertEquals( '&#36;20.00', $total2 );
		$this->assertEquals( '&#36;20.00', $total3 );
		$this->assertEquals( '&#36;20.00', $total4 );

	}

	/**
	 * Test a guest donation payment.
	 */
	public function test_is_guest_payment() {

		// setUp defines a payment with a known user, use this
		$this->assertFalse( give_is_guest_payment( $this->_payment_id ) );

		// Create a guest payment
		$guest_payment_id = Give_Helper_Payment::create_simple_guest_payment();
		$this->assertTrue( give_is_guest_payment( $guest_payment_id ) );
	}

	/**
	 * Test payment date query.
	 */
	public function test_payments_date_query() {
		$payment_id_1 = Give_Helper_Payment::create_simple_payment( date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ) );

		$args           = array(
			'start_date' => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'end_date'   => date( 'Y-m-d H:i:s' ),
		);
		$payments_query = new Give_Payments_Query( $args );
		$payments       = $payments_query->get_payments();

		$this->assertEquals( 2, count( $payments ) );
		$this->assertEquals( $payment_id_1, $payments[0]->ID );
		$this->assertEquals( $this->_payment_id, $payments[1]->ID );
	}

	/**
	 * @covers ::give_get_price_id
	 */
	public function test_give_get_price_id() {
		$form = Give_Helper_Form::create_multilevel_form(
			array(
				'meta' => array(
					'_give_set_price'             => '0.00', // Multi-level Pricing; not set
					'_give_display_style'         => 'buttons',
					'_give_donation_levels'       => array(
						array(
							'_give_id'     => array( 'level_id' => '1' ),
							'_give_amount' => '10',
							'_give_text'   => 'Small Gift',
						),
						array(
							'_give_id'      => array( 'level_id' => '2' ),
							'_give_amount'  => '25',
							'_give_text'    => 'Mid-size Gift',
							'_give_default' => 'default',
						),
						array(
							'_give_id'     => array( 'level_id' => '3' ),
							'_give_amount' => '50',
							'_give_text'   => 'Large Gift',
						),
						array(
							'_give_id'     => array( 'level_id' => '4' ),
							'_give_amount' => '100',
							'_give_text'   => 'Big Gift',
						),
					),
					'_give_custom_amount'         => 'enabled',
					'_give_custom_amount_minimum' => give_maybe_sanitize_amount( 1 ),
				),
			)
		);

		$amount_with_levels = array(
			1        => give_maybe_sanitize_amount( 10 ),
			2        => give_maybe_sanitize_amount( 25 ),
			3        => give_maybe_sanitize_amount( 50 ),
			4        => give_maybe_sanitize_amount( 100 ),
			'custom' => give_maybe_sanitize_amount( 1.22 ),
		);

		foreach ( $amount_with_levels as $level_id => $amount ) {
			$this->assertEquals( $level_id, give_get_price_id( $form->ID, $amount ) );
		}
	}

	/**
	 * Test for give_donation_amount fn
	 *
	 * @since        1.8.17
	 * @access       public
	 *
	 * @param bool|array $format_args
	 * @param string     $expected1
	 * @param string     $expected2
	 *
	 * @dataProvider give_donation_amount_provider
	 */
	public function test_give_donation_amount( $format_args, $expected1, $expected2 ) {
		$donation = Give_Helper_Payment::create_simple_payment( array( 'donation' => array( 'price' => 2873892713.34468 ) ) );

		$donation = new Give_Payment( $donation );

		$this->assertSame( $expected1, give_donation_amount( $donation->ID, $format_args ) );

		// Change payment data.
		$donation->currency = 'INR';
		$donation->save();

		if ( is_array( $format_args ) && ( isset( $format_args['currency'] ) && is_array( $format_args['currency'] ) ) ) {
			$format_args['currency']['currency_code'] = 'INR';
		}

		if ( is_array( $format_args ) && ( isset( $format_args['amount'] ) && is_array( $format_args['amount'] ) ) ) {
			$format_args['amount']['currency'] = 'INR';
		}

		$this->assertSame( $expected2, give_donation_amount( $donation->ID, $format_args ) );

	}


	/**
	 * Data provider for test_give_donation_amount
	 *
	 * @since  1.8.17
	 * @access public
	 * @return array
	 */
	public function give_donation_amount_provider() {
		$global_currency_code = give_get_option( 'currency' );

		return array(
			array( false, '2873892713.34', '2873892713.34' ),
			array( true, '&#36;2,873,892,713.34', '&#8377;2,87,38,92,713.34' ),
			array(
				array(
					'currency' => true,
					'amount'   => false,
				),
				'&#36;2873892713.34',
				'&#8377;2873892713.34',
			),
			array(
				array(
					'currency' => false,
					'amount'   => true,
				),
				'2,873,892,713.34',
				'2,87,38,92,713.34',
			),
			array(
				array(
					'currency' => true,
					'amount'   => true,
				),
				'&#36;2,873,892,713.34',
				'&#8377;2,87,38,92,713.34',
			),
			array(
				array(
					'currency' => false,
					'amount'   => false,
				),
				'2873892713.34',
				'2873892713.34',
			),

			array( array(), '2873892713.34', '2873892713.34' ),

			array(
				array(
					'currency' => array(
						'decode_currency' => true,
						'currency_code'   => $global_currency_code,
					),
					'amount'   => false,
				),
				'$2873892713.34',
				'₹2873892713.34',
			),

			array(
				array(
					'currency' => false,
					'amount'   => array(
						'decimal'  => false,
						'currency' => $global_currency_code,
					),
				),
				'2,873,892,713',
				'2,87,38,92,713',
			),

			array(
				array(
					'currency' => array(
						'decode_currency' => true,
						'currency_code'   => $global_currency_code,
					),
					'amount'   => array(
						'decimal'  => false,
						'currency' => $global_currency_code,
					),
				),
				'$2,873,892,713',
				'₹2,87,38,92,713',
			),
		);
	}
}
