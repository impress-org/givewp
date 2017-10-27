<?php

/**
 * @group formatting
 */
class Tests_MISC_Functions extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * test for give_get_currency_name
	 *
	 * @since         1.8.8
	 * @access        public
	 *
	 * @param string $value
	 * @param string $expected
	 *
	 * @cover         give_get_currency_name
	 * @dataProvider  give_get_currency_name_data_provider
	 */
	public function test_give_get_currency_name( $value, $expected ) {
		$this->assertEquals( $expected, $value );
	}


	/**
	 * Data Provider
	 *
	 * @todo  Add more currencies for testing.
	 *
	 * @since 1.8.8
	 * @return array
	 */
	public function give_get_currency_name_data_provider() {
		return array(
			array( give_get_currency_name( 'USD' ), __( 'US Dollars', 'give' ) ),
			array( give_get_currency_name( 'GBP' ), __( 'Pounds Sterling', 'give' ) ),
			array( give_get_currency_name( 'TWD' ), __( 'Taiwan New Dollars', 'give' ) ),
			array( give_get_currency_name( 'Wrong_Currency_Symbol' ), '' ),
		);
	}

	/**
	 * test for give post type meta related functions
	 *
	 * @since         1.8.8
	 * @access        public
	 *
	 * @param int $form_or_donation_id
	 *
	 * @cover         give_get_meta
	 * @cover         give_update_meta
	 * @cover         give_delete_meta
	 *
	 * @dataProvider  give_meta_helpers_provider
	 */
	public function test_give_meta_helpers( $form_or_donation_id ) {
		$value = give_get_meta( $form_or_donation_id, 'testing_meta', true, 'TEST1' );
		$this->assertEquals( 'TEST1', $value );

		$status = give_update_meta( $form_or_donation_id, 'testing_meta', 'TEST' );
		$this->assertEquals( true, (bool) $status );

		$status = give_update_meta( $form_or_donation_id, 'testing_meta', 'TEST' );
		$this->assertEquals( false, (bool) $status );

		$value = give_get_meta( $form_or_donation_id, 'testing_meta', true );
		$this->assertEquals( 'TEST', $value );

		$status = give_delete_meta( $form_or_donation_id, 'testing_meta', 'TEST2' );
		$this->assertEquals( false, $status );

		$status = give_delete_meta( $form_or_donation_id, 'testing_meta' );
		$this->assertEquals( true, $status );
	}
	
	
	/**
	 * Data provider for test_give_meta_helpers
	 *
	 * @since 2.0
	 * @access private
	 */
	public function give_meta_helpers_provider(){
		return array(
			array( Give_Helper_Payment::create_simple_payment() ),
			array( Give_Helper_Form::create_simple_form()->id ),
		);
	}

	/**
	 * Test for building Item Title of Payment Gateway.
	 *
	 * @since 1.8.14
	 * @access public
	 *
	 * @cover give_payment_gateway_item_title
	 */
	public function test_give_payment_gateway_item_title() {

		// Setup Simple Donation Form.
		$donation = Give_Helper_Form::setup_simple_donation_form();

		// Simple Donation Form using Payment Gateway Item Title.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form', $title );

		// Setup Simple Donation Form with Custom Amount.
		$donation = Give_Helper_Form::setup_simple_donation_form( true );

		// Simple Donation Form using Payment Gateway Item Title with Custom Amount.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form - Would you like to set a custom amount?', $title );

		// Setup MultiLevel Donation Form.
		$donation = Give_Helper_Form::setup_multi_level_donation_form();

		// MultiLevel Donation Form using Payment Gateway Item Title.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form - Mid-size Gift', $title );

		// Setup MultiLevel Donation Form with Custom Amount.
		$donation = Give_Helper_Form::setup_multi_level_donation_form( true );

		// MultiLevel Donation Form using Payment Gateway Item Title with Custom Amount.
		$title = give_payment_gateway_item_title( $donation );
		$this->assertEquals( 'Test Donation Form - Custom Amount', $title );

	}
}
