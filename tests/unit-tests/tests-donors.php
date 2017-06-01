<?php

/**
 * Class Give_Tests_Donors
 */
class Give_Tests_Donors extends Give_Unit_Test_Case {

	protected $_post_id = null;

	protected $_user_id = null;

	protected $_customer_id = null;

	/**
	 * Set it up
	 */
	public function setUp() {
		parent::setUp();

		//Create a Donation Form
		$this->_post_id = $this->factory->post->create( array(
			'post_title'  => 'Test Form',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10.00',
				'_give_text'   => 'Basic Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '2' ),
				'_give_amount' => '20.00',
				'_give_text'   => 'Intermediate Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '40.00',
				'_give_text'   => 'Advanced Level'
			),
		);

		$meta = array(
			'give_price'            => '0.00',
			'_give_price_option'    => 'multi',
			'_give_donation_levels' => array_values( $_multi_level_donations ),
			'give_product_notes'    => 'Donation Notes',
			'_give_product_type'    => 'default'
		);
		foreach ( $meta as $key => $value ) {
			give_update_meta( $this->_post_id, $key, $value );
		}

		//Generate Donations
		$this->_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user           = get_userdata( $this->_user_id );

		$user_info = array(
			'id'         => $user->ID,
			'email'      => 'testadmin@domain.com',
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name
		);

		$donation_details = array(
			array(
				'id'      => $this->_post_id,
				'options' => array(
					'price_id' => 1
				)
			)
		);

		$total = 0;

		$prices     = give_get_meta( $donation_details[0]['id'], '_give_donation_levels', true );
		$item_price = $prices[1]['_give_amount'];

		$total += $item_price;

		$purchase_data = array(
			'price'           => number_format( (float) $total, 2 ),
			'give_form_title' => get_the_title( $this->_post_id ),
			'give_form_id'    => $this->_post_id,
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $purchase_data );

		give_update_payment_status( $payment_id, 'complete' );

	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test Add Customer
	 *
	 * @covers Give_Donor::create
	 */
	public function test_add_customer() {

		$test_email = 'testaccount@domain.com';

		$donor = new Give_Donor( $test_email );
		$this->assertEquals( 0, $donor->id );

		$data = array( 'email' => $test_email );

		$donor_id = $donor->create( $data );
		$this->assertTrue( is_numeric( $donor_id ) );
		$this->assertEquals( $donor->email, $test_email );
		$this->assertEquals( $donor->id, $donor_id );

	}

	/**
	 * Test Update Customer
	 *
	 * @covers Give_Donor::update
	 */
	public function test_update_customer() {

		$test_email = 'testaccount2@domain.com';

		$donor    = new Give_Donor( $test_email );
		$donor_id = $donor->create( array( 'email' => $test_email ) );
		$this->assertEquals( $donor_id, $donor->id );

		$data_to_update = array( 'email' => 'testaccountupdated@domain.com', 'name' => 'Test Account' );
		$donor->update( $data_to_update );
		$this->assertEquals( 'testaccountupdated@domain.com', $donor->email );
		$this->assertEquals( 'Test Account', $donor->name );

		// Verify if we have an empty array we get false
		$this->assertFalse( $donor->update() );

	}

	/**
	 * Test Magic Get Method
	 *
	 * @covers Give_Donor::__get
	 */
	public function test_magic_get_method() {

		$donor = new Give_Donor( 'testadmin@domain.com' );
		$this->assertEquals( 'testadmin@domain.com', $donor->email );
		$this->assertTrue( is_wp_error( $donor->__get( 'asdf' ) ) );

	}

	public function test_attach_payment() {

		$donor = new Give_Donor( 'testadmin@domain.com' );
		$donor->attach_payment( 5222222 );

		$payment_ids = array_map( 'absint', explode( ',', $donor->payment_ids ) );

		$this->assertTrue( in_array( 5222222, $payment_ids ) );

		// Verify if we don't send a payment, we get false
		$this->assertFalse( $donor->attach_payment() );

	}

	public function test_attach_duplicate_payment() {

		// Verify that if we pass a payment that's already attached we do not change stats
		$donor = new Give_Donor( 'testadmin@domain.com' );
		$payments = array_map( 'absint', explode( ',', $donor->payment_ids ) );

		$expected_purcahse_count = $donor->purchase_count;
		$expected_purcahse_value = $donor->purchase_value;

		$donor->attach_payment( $payments[0] );
		$this->assertEquals( $expected_purcahse_count, $donor->purchase_count );
		$this->assertEquals( $expected_purcahse_value, $donor->purchase_value );

	}

	public function test_remove_payment() {

		$donor = new Give_Donor( 'testadmin@domain.com' );
		$donor->attach_payment( 5222223, false );

		$payment_ids = array_map( 'absint', explode( ',', $donor->payment_ids ) );
		$this->assertTrue( in_array( 5222223, $payment_ids ) );

		$donor->remove_payment( 5222223, false );

		$payment_ids = array_map( 'absint', explode( ',', $donor->payment_ids ) );
		$this->assertFalse( in_array( 5222223, $payment_ids ) );
	}

	public function test_increment_stats() {

		$donor = new Give_Donor( 'testadmin@domain.com' );

		$this->assertEquals( '20', $donor->purchase_value );
		$this->assertEquals( '1', $donor->purchase_count );

		$donor->increase_purchase_count();
		$donor->increase_value( 10 );

		$this->assertEquals( '30', $donor->purchase_value );
		$this->assertEquals( '2', $donor->purchase_count );

		$this->assertEquals( give_count_purchases_of_customer( $this->_user_id ), '2' );
		$this->assertEquals( give_purchase_total_of_user( $this->_user_id ), '30' );

		// Make sure we hit the false conditions
		$this->assertFalse( $donor->increase_purchase_count( - 1 ) );
		$this->assertFalse( $donor->increase_purchase_count( 'abc' ) );

	}

	/**
	 * Test stats decrement.
	 */
	public function test_decrement_stats() {

		$donor = new Give_Donor( 'testadmin@domain.com' );

		$donor->decrease_donation_count();
		$donor->decrease_value( 10 );

		$this->assertEquals( $donor->purchase_value, '10' );
		$this->assertEquals( $donor->purchase_count, '0' );

		$this->assertEquals( give_count_purchases_of_customer( $this->_user_id ), '0' );
		$this->assertEquals( give_purchase_total_of_user( $this->_user_id ), '10' );

		// Make sure we hit the false conditions
		$this->assertFalse( $donor->decrease_donation_count( - 1 ) );
		$this->assertFalse( $donor->decrease_donation_count( 'abc' ) );

		$donor->decrease_donation_count( 100 );
		$donor->decrease_value( 100000 );

		$this->assertEquals( intval( $donor->purchase_value ), intval( '0' ) );
		$this->assertEquals( intval( $donor->purchase_count ), intval( '0' ) );

	}

	public function test_donor_notes() {

		$donor = new Give_Donor( 'testadmin@domain.com' );

		$this->assertInternalType( 'array', $donor->notes );
		$this->assertEquals( 0, $donor->get_notes_count() );

		$note_1 = $donor->add_note( 'Testing' );
		$this->assertEquals( 0, array_search( $note_1, $donor->notes ) );
		$this->assertEquals( 1, $donor->get_notes_count() );

		$note_2 = $donor->add_note( 'Test 2nd Note' );
		$this->assertEquals( 1, array_search( $note_1, $donor->notes ) );
		$this->assertEquals( 0, array_search( $note_2, $donor->notes ) );
		$this->assertEquals( 2, $donor->get_notes_count() );

		// Verify we took out all empty rows
		$this->assertEquals( count( $donor->notes ), count( array_values( $donor->notes ) ) );

		// Test 1 note per page, page 1
		$newest_note = $donor->get_notes( 1 );
		$this->assertEquals( 1, count( $newest_note ) );
		$this->assertEquals( $newest_note[0], $note_2 );

		// Test 1 note per page, page 2
		$second_note = $donor->get_notes( 1, 2 );
		$this->assertEquals( 1, count( $second_note ) );
		$this->assertEquals( $second_note[0], $note_1 );
	}

	public function test_users_purchases() {

		$out = give_get_users_purchases( $this->_user_id );

		$this->assertInternalType( 'object', $out[0] );
		$this->assertEquals( 'give_payment', $out[0]->post_type );
		$this->assertTrue( give_has_purchases( $this->_user_id ) );
		$this->assertEquals( 1, give_count_purchases_of_customer( $this->_user_id ) );

		$no_user = give_get_users_purchases( 0 );
		$this->assertFalse( $no_user );

		$no_user_count = give_count_purchases_of_customer();
		$this->assertEquals( 0, $no_user_count );

	}

	public function test_give_get_users_completed_donations() {

		$out2 = give_get_users_completed_donations( $this->_user_id );

		$this->assertInternalType( 'array', $out2 );
		$this->assertEquals( 1, count( $out2 ) );
		$this->assertInternalType( 'object', $out2[0] );
		$this->assertEquals( $out2[0]->post_type, 'give_forms' );

	}

	public function test_get_purchase_stats_by_user() {

		$purchase_stats = give_get_purchase_stats_by_user( $this->_user_id );

		$this->assertInternalType( 'array', $purchase_stats );
		$this->assertEquals( 2, count( $purchase_stats ) );
		$this->assertTrue( isset( $purchase_stats['purchases'] ) );
		$this->assertTrue( isset( $purchase_stats['total_spent'] ) );

	}

	public function test_get_donation_total_of_user() {

		$donation_total = give_purchase_total_of_user( $this->_user_id );

		$this->assertEquals( 20, $donation_total );
	}

	public function test_validate_username() {
		$this->assertTrue( give_validate_username( 'giveuser' ) );
		$this->assertFalse( give_validate_username( 'give12345$%&+-!@£%^&()(*&^%$£@!' ) );
	}
}