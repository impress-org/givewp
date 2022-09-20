<?php

/**
 * Class Tests_Deprecated_Classes
 *
 * Private class members are preceded by a single underscore.
 */
class Tests_Deprecated_Classes extends Give_Unit_Test_Case {

	/**
	 * @var null
	 */
	protected $_post_id = null;

	/**
	 * @var null
	 */
	protected $_user_id = null;

	/**
	 * @var null
	 */
	protected $_donor_id = null;

	/**
	 * @var null
	 */
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
				'_give_id'     => array(
					'level_id' => '1',
				),
				'_give_amount' => '10.00',
				'_give_text'   => 'Basic Level',
			),
			array(
				'_give_id'     => array(
					'level_id' => '2',
				),
				'_give_amount' => '20.00',
				'_give_text'   => 'Intermediate Level',
			),
			array(
				'_give_id'     => array(
					'level_id' => '3',
				),
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

		$donation_data = array(
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

		$payment_id = give_insert_payment( $donation_data );

		$this->_donor_id = give_get_payment_donor_id( $payment_id );

		give_update_payment_status( $payment_id, 'complete' );

	}

	/**
	 * Tear Down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test to ensure Give_Customer matches Give_Donor
	 */
	public function test_customers_vs_donors_class() {

		if ( ! method_exists( $this, 'assertArraySubset' ) ) {
			$this->markTestSkipped( 'PHPUnit version too outdated to run tests within this class.' );

		}

		$donor    = (array) new Give_Donor( $this->_donor_id );
		$customer = (array) new Give_Customer( $this->_donor_id );

		// Check that the keys match (converted to arrays for testing).
		$this->assertArraySubset( $donor, $customer );

		// Check that the donor / customer values match.
		foreach ( $donor as $key => $donor_val ) {
			$this->assertEquals( $donor_val, $customer[ "{$key}" ] );
		}

		// Test customer create.
		$test_email = 'cooldonor@domain.com';
		$customer2  = new Give_Customer( $test_email );
		$this->assertEquals( 0, $customer2->id );

		$data = array(
			'email' => $test_email,
		);

		$customer2_id = $customer2->create( $data );
		$this->assertTrue( is_numeric( $customer2_id ) );
		$this->assertEquals( $customer2->email, $test_email );
		$this->assertEquals( $customer2->id, $customer2_id );

	}

	/**
	 * Test to ensure Give_Customer matches Give_Donor
	 */
	public function test_db_customers_vs_db_donors_class() {

		if ( ! method_exists( $this, 'assertArraySubset' ) ) {
			$this->markTestSkipped( 'PHPUnit version too outdated to run tests within this class.' );
		}

		$donors_db          = new Give_DB_Donors();
		$customers_db       = new Give_DB_Customers();
		$customers_db_array = (array) $customers_db;

		// Check that the objects match (converted to arrays for testing).
		$this->assertArraySubset( (array) $donors_db, $customers_db_array );

		// Check values match within array.
		foreach ( (array) $donors_db as $key => $donor_db_val ) {
			$this->assertEquals( $donor_db_val, $customers_db_array[ "{$key}" ] );
		}

		// Test get_customers vs get_donors
		$args          = array(
			'number' => - 1,
		);
		$get_donors    = $donors_db->get_donors( $args );
		$get_customers = $customers_db->get_customers( $args );
		$this->assertArraySubset( (array) $get_donors[0], (array) $get_customers[0] );

		// Test get_customer_by vs get_donor_by
		$donor    = $donors_db->get_donor_by( 'email', 'testadmin@domain.com' );
		$customer = $customers_db->get_donor_by( 'email', 'testadmin@domain.com' );
		$this->assertArraySubset( (array) $donor, (array) $customer );

	}

}
