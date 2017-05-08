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
}