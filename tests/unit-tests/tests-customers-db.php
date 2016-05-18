<?php

/**
 * @group give_customers
 */
class Tests_Customers_DB extends Give_Unit_Test_Case {

	protected $_post_id = null;

	protected $_user_id = null;

	protected $_customer_id = null;

	public function setUp() {
		parent::setUp();

		$this->_post_id = $this->factory->post->create( array(
			'post_title'  => 'Test Donation',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10',
				'_give_text'   => 'Basic Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '2' ),
				'_give_amount' => '20',
				'_give_text'   => 'Intermediate Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '40',
				'_give_text'   => 'Advanced Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '4' ),
				'_give_amount' => '100',
				'_give_text'   => 'Super Level'
			),
		);

		$meta = array(
			'give_price'               => '0.00',
			'_give_price_option'       => 'multi',
			'_give_price_options_mode' => 'on',
			'_give_donation_levels'    => array_values( $_multi_level_donations ),
			'_give_product_type'       => 'default',
			'_give_form_earnings'      => 129.43,
			'_give_form_sales'         => 59,
		);

		foreach ( $meta as $key => $value ) {
			update_post_meta( $this->_post_id, $key, $value );
		}

		//Generate some donations
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
					'price_id' => 4
				)
			)
		);

		$payment_details = array(
			array(
				'name'     => 'Test Donation',
				'id'       => $this->_post_id,
				'options'  => array(
					'price_id' => 4
				),
				'price'    => 100,
				'quantity' => 1,
			)
		);

		$total = 0;

		$prices     = get_post_meta( $donation_details[0]['id'], '_give_donation_levels', true );
		$item_price = $prices[3]['_give_amount'];

		$total += $item_price;

		$purchase_data = array(
			'price'           => number_format( (float) $total, 2 ),
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'donations'       => $donation_details,
			'payment_details' => $payment_details,
			'status'          => 'pending',
			'gateway'         => 'manual'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';
		$_SERVER['SERVER_NAME'] = 'give_virtual';

		$payment_id = give_insert_payment( $purchase_data );

		give_update_payment_status( $payment_id, 'complete' );

	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_installed() {
		$this->assertTrue( Give()->customers->installed() );
	}

	public function test_get_customer_columns() {
		$columns = array(
			'id'             => '%d',
			'user_id'        => '%d',
			'name'           => '%s',
			'email'          => '%s',
			'payment_ids'    => '%s',
			'purchase_value' => '%f',
			'purchase_count' => '%d',
			'notes'          => '%s',
			'date_created'   => '%s',
		);

		$this->assertEquals( $columns, Give()->customers->get_columns() );
	}

	public function test_get_by() {

		$customer = Give()->customers->get_customer_by( 'email', 'testadmin@domain.com' );

		$this->assertInternalType( 'object', $customer );
		$this->assertObjectHasAttribute( 'email', $customer );

	}

	public function test_get_column_by() {

		$customer_id = Give()->customers->get_column_by( 'id', 'email', 'testadmin@domain.com' );

		$this->assertGreaterThan( 0, $customer_id );

	}

	public function test_exists() {

		$this->assertTrue( Give()->customers->exists( 'testadmin@domain.com' ) );

	}

	public function test_legacy_attach_payment() {

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$customer = new Give_Customer( 'testadmin@domain.com' );
		Give()->customers->attach_payment( $customer->id, $payment_id );

		$updated_customer = new Give_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertTrue( in_array( $payment_id, $payment_ids ) );

		Give_Helper_Payment::delete_payment( $payment_id );

	}

	public function test_legacy_remove_payment() {

		$payment_id = Give_Helper_Payment::create_simple_payment();

		$customer = new Give_Customer( 'testadmin@domain.com' );
		Give()->customers->attach_payment( $customer->id, $payment_id );

		$updated_customer = new Give_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );
		$this->assertTrue( in_array( $payment_id, $payment_ids ) );

		Give()->customers->remove_payment( $updated_customer->id, $payment_id );
		$updated_customer = new Give_Customer( 'testadmin@domain.com' );
		$payment_ids      = array_map( 'absint', explode( ',', $updated_customer->payment_ids ) );

		$this->assertFalse( in_array( $payment_id, $payment_ids ) );

		Give_Helper_Payment::delete_payment( $payment_id );

	}

	public function test_legacy_increment_stats() {

		$customer = new Give_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '100', $customer->purchase_value );
		$this->assertEquals( '1', $customer->purchase_count );

		Give()->customers->increment_stats( $customer->id, 10 );

		$updated_customer = new Give_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '110', $updated_customer->purchase_value );
		$this->assertEquals( '2', $updated_customer->purchase_count );
	}

	public function test_legacy_decrement_stats() {

		$customer = new Give_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '100', $customer->purchase_value );
		$this->assertEquals( '1', $customer->purchase_count );

		Give()->customers->decrement_stats( $customer->id, 10 );

		$updated_customer = new Give_Customer( 'testadmin@domain.com' );

		$this->assertEquals( '90', $updated_customer->purchase_value );
		$this->assertEquals( '0', $updated_customer->purchase_count );
	}

	public function test_get_customers() {

		$customers = Give()->customers->get_customers();

		$this->assertEquals( 1, count( $customers ) );

	}

	public function test_count_customers() {

		$this->assertEquals( 1, Give()->customers->count() );

		$args = array(
			'date' => array(
				'start' => 'January 1 ' . date( 'Y' ) + 1,
				'end'   => 'January 1 ' . date( 'Y' ) + 2,
			)
		);

		$this->assertEquals( 0, Give()->customers->count( $args ) );

	}

	public function test_update_customer_email_on_user_update() {

		$user_id = wp_insert_user( array(
			'user_login' => 'john12345',
			'user_email' => 'john1234@test.com',
			'user_pass'  => wp_generate_password()
		) );

		$customer = new Give_Customer;
		$customer->create( array(
			'email'   => 'john1234@test.com',
			'user_id' => $user_id
		) );

		wp_update_user( array(
			'ID'         => $user_id,
			'user_email' => 'john12345@test.com'
		) );

		$updated_customer = new Give_Customer( 'john12345@test.com' );

		$this->assertEquals( $customer->id, $updated_customer->id );

	}

}
