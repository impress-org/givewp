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
	 * @cover         give_get_meta
	 * @cover         give_update_meta
	 * @cover         give_delete_meta
	 */
	public function test_give_meta_helpers() {
		$payment = Give_Helper_Payment::create_simple_payment();

		$value = give_get_meta( $payment, 'testing_meta', true, 'TEST1' );
		$this->assertEquals( 'TEST1', $value );

		$status = give_update_meta( $payment, 'testing_meta', 'TEST' );
		$this->assertEquals( true, (bool) $status );

		$status = give_update_meta( $payment, 'testing_meta', 'TEST' );
		$this->assertEquals( false, (bool) $status );

		$value = give_get_meta( $payment, 'testing_meta', true );
		$this->assertEquals( 'TEST', $value );

		$status = give_delete_meta( $payment, 'testing_meta', 'TEST2' );
		$this->assertEquals( false, $status );

		$status = give_delete_meta( $payment, 'testing_meta' );
		$this->assertEquals( true, $status );
	}
}
