<?php

/**
 * Class Tests_Donors_DB
 */
class Tests_Donors_DB extends Give_Unit_Test_Case {

	protected $_post_id = null;

	protected $_user_id = null;

	protected $_customer_id = null;

	/**
	 * Set it Up.
	 */
	public function setUp() {
		parent::setUp();

		$this->_post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Donation',
				'post_type'   => 'give_forms',
				'post_status' => 'publish',
			)
		);

		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10.00',
				'_give_text'   => 'Basic Level',
			),
			array(
				'_give_id'     => array( 'level_id' => '2' ),
				'_give_amount' => '20.00',
				'_give_text'   => 'Intermediate Level',
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '40.00',
				'_give_text'   => 'Advanced Level',
			),
		);

		$meta = array(
			'give_price'            => '0.00',
			'_give_price_option'    => 'multi',
			'_give_donation_levels' => array_values( $_multi_level_donations ),
			'give_product_notes'    => 'Donation Notes',
			'_give_product_type'    => 'default',
		);

		foreach ( $meta as $key => $value ) {
			give_update_meta( $this->_post_id, $key, $value );
		}

		/** Generate some donations */
		$this->_user_id = $this->factory->user->create(
			array(
				'role'       => 'administrator',
				'first_name' => 'Admin',
				'last_name'  => 'User',
			)
		);

		$user = get_userdata( $this->_user_id );

		$user_info = array(
			'id'         => $user->ID,
			'email'      => 'testadmin@domain.com',
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		$donation_details = array(
			array(
				'id'      => $this->_post_id,
				'options' => array(
					'price_id' => 1,
				),
			),
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
			'gateway'         => 'manual',
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $purchase_data );

		give_update_payment_status( $payment_id, 'complete' );

	}

	/**
	 * Tear Down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test get customer columns.
	 */
	public function test_get_columns() {
		$columns = array(
			'id'              => '%d',
			'user_id'         => '%d',
			'name'            => '%s',
			'email'           => '%s',
			'payment_ids'     => '%s',
			'purchase_value'  => '%f',
			'purchase_count'  => '%d',
			'date_created'    => '%s',
			'token'           => '%s',
			'verify_key'      => '%s',
			'verify_throttle' => '%s',
		);

		$this->assertEquals( $columns, Give()->donors->get_columns() );
	}

	/**
	 * Test Get By.
	 */
	public function test_get_by() {

		$donor = Give()->donors->get_donor_by( 'email', 'testadmin@domain.com' );

		$this->assertInternalType( 'object', $donor );
		$this->assertObjectHasAttribute( 'email', $donor );

	}

	/**
	 * Test Get Column By.
	 */
	public function test_get_column_by() {

		$customer_id = Give()->donors->get_column_by( 'id', 'email', 'testadmin@domain.com' );

		$this->assertGreaterThan( 0, $customer_id );

	}

	/**
	 * Test Exists Method.
	 */
	public function test_exists() {

		$this->assertTrue( Give()->donors->exists( 'testadmin@domain.com' ) );

	}

	/**
	 * Test Legacy Attach Payment.
	 */
	public function test_legacy_attach_payment() {

		$customer = new Give_Donor( 'testadmin@domain.com' );
		Give()->donors->attach_payment( $customer->id, 999999 );

		$updated_customer = new Give_Donor( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertTrue( in_array( 999999, $payment_ids ) );

	}

	/**
	 * Test Legacy Remove Payment.
	 */
	public function test_legacy_remove_payment() {

		$customer = new Give_Donor( 'testadmin@domain.com' );
		Give()->donors->attach_payment( $customer->id, 91919191 );

		$updated_customer = new Give_Donor( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );
		$this->assertTrue( in_array( 91919191, $payment_ids ) );

		Give()->donors->remove_payment( $updated_customer->id, 91919191 );
		$updated_customer = new Give_Donor( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertFalse( in_array( 91919191, $payment_ids ) );

	}

	/**
	 * Test Legacy Increment Stats.
	 */
	public function test_legacy_increment_stats() {

		$customer = new Give_Donor( 'testadmin@domain.com' );

		$this->assertEquals( '20', $customer->get_total_donation_amount() );
		$this->assertEquals( '1', $customer->purchase_count );

		Give()->donors->increment_stats( $customer->id, 10 );

		$updated_customer = new Give_Donor( 'testadmin@domain.com' );

		$this->assertEquals( '30', $updated_customer->get_total_donation_amount() );
		$this->assertEquals( '2', $updated_customer->purchase_count );
	}

	/**
	 * Test Legacy Decrement Stats.
	 */
	public function test_legacy_decrement_stats() {

		$customer = new Give_Donor( 'testadmin@domain.com' );

		$this->assertEquals( '20', $customer->get_total_donation_amount() );
		$this->assertEquals( '1', $customer->purchase_count );

		Give()->donors->decrement_stats( $customer->id, 10 );

		$updated_customer = new Give_Donor( 'testadmin@domain.com' );

		$this->assertEquals( '10', $updated_customer->get_total_donation_amount() );
		$this->assertEquals( '0', $updated_customer->purchase_count );
	}

	/**
	 * Test Get Customers.
	 */
	public function test_get_donors() {

		$donors = Give()->donors->get_donors();

		$this->assertEquals( 2, count( $donors ) );

	}

	/**
	 * Test Count Customers.
	 */
	public function test_count_customers() {

		$this->assertEquals( 2, intval( Give()->donors->count() ) );

		$args = array(
			'date' => array(
				'start' => 'January 1 ' . ( date( 'Y' ) + 1 ),
				'end'   => 'January 1 ' . ( date( 'Y' ) + 2 ),
			),
		);

		$this->assertEquals( 0, Give()->donors->count( $args ) );

	}

}
