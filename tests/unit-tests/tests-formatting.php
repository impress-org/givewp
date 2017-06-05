<?php

/**
 * @group formatting
 */
class Tests_Formatting extends Give_Unit_Test_Case {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test function give_get_price_thousand_separator
	 *
	 * @since 1.8
	 *
	 * @cover give_get_price_thousand_separator
	 */
	function test_give_get_price_thousand_separator() {
		$output = give_get_price_thousand_separator();

		$this->assertEquals( ',', $output );
	}

	/**
	 * Test give_get_price_decimal_separator function
	 *
	 * @since 1.8
	 *
	 * @cover give_get_price_decimal_separator
	 */
	function test_give_get_price_decimal_separator() {
		$output = give_get_price_decimal_separator();

		$this->assertEquals( '.', $output );
	}

	/**
	 * Test give_sanitize_amount function.
	 *
	 * @since        1.8
	 *
	 * @param string $amount
	 * @param string $expected
	 * @param bool   $dp
	 * @param bool   $trim_zeros
	 *
	 * @cover        give_sanitize_amount
	 * @dataProvider give_sanitize_amount_provider
	 */
	function test_give_sanitize_amount( $amount, $expected, $dp = false, $trim_zeros = false ) {
		$output = give_sanitize_amount( $amount, $dp, $trim_zeros );

		$this->assertSame(
			$expected,
			$output
		);
	}


	/**
	 * Data provider for give_sanitize_amount function
	 *
	 * @since 1.8
	 * @return array
	 *
	 */
	function give_sanitize_amount_provider() {
		return array(
			array( '1,000,000,000,000.00', '1000000000000.00' ),
			array( '1,000,000,000.00', '1000000000.00' ),
			array( '1,000,000.00', '1000000.00' ),
			array( '10,000.00', '10000.00' ),
			array( '100.00', '100.00' ),
			array( '1,000,000,000,000.00', '1000000000000.000', 3 ),
			array( '1,000,000,000.00', '1000000000.000', 3 ),
			array( '1,000,000.00', '1000000.000', 3 ),
			array( '10,000.00', '10000.000', 3 ),
			array( '100.00', '100.000', 3 ),
			array( '1,000,000,000,000.00', '1000000000000', false, true ),
			array( '1,000,000,000.00', '1000000000', false, true ),
			array( '1,000,000.00', '1000000', false, true ),
			array( '10,000.00', '10000', false, true ),
			array( '100.00', '100', false, true ),
		);
	}


	/**
	 * Test give_format_amount function.
	 *
	 * @since        1.8
	 *
	 * @param string   $amount
	 * @param string   $expected
	 * @param bool $decimal
	 *
	 * @cover        give_format_amount
	 * @dataProvider give_format_amount_provider
	 */
	function test_give_format_amount( $amount, $expected, $decimal = false ) {
		$output = give_format_amount( $amount, $decimal );

		$this->assertSame(
			$expected,
			$output
		);
	}


	/**
	 * Data provider for give_format_amount function
	 *
	 * @since 1.8
	 * @return array
	 *
	 */
	function give_format_amount_provider() {
		return array(
			array( '1000000000000', '1,000,000,000,000' ),
			array( '1000000000', '1,000,000,000' ),
			array( '1000000', '1,000,000' ),
			array( '10000', '10,000' ),
			array( '100', '100' ),
			array( '1000000000000', '1,000,000,000,000.00', true ),
			array( '1000000000', '1,000,000,000.00', true ),
			array( '1000000', '1,000,000.00', true ),
			array( '10000', '10,000.00', true ),
			array( '100', '100.00', true ),
		);
	}


	/**
	 * Test give_human_format_large_amount function.
	 *
	 * @since        1.8
	 *
	 * @param string $amount
	 * @param string $expected
	 *
	 * @cover        give_human_format_large_amount
	 * @dataProvider give_human_format_large_amount_provider
	 */
	function test_give_human_format_large_amount( $amount, $expected ) {
		$output = give_human_format_large_amount( give_format_amount( $amount ) );

		$this->assertSame(
			$expected,
			$output
		);
	}


	/**
	 * Data provider for give_human_format_large_amount function
	 *
	 * @since 1.8
	 * @return array
	 *
	 */
	function give_human_format_large_amount_provider() {
		return array(
			array( '1234000000000', '1.23 trillion' ),
			array( '1000000000000', '1 trillion' ),
			array( '1000000000', '1 billion' ),
			array( '1000000', '1 million' ),
			array( '10000', '10,000.00' ),
			array( '100', '100.00' ),
		);
	}

	/**
	 * Test give_format_decimal function.
	 *
	 * @since        1.8
	 *
	 * @param int $number
	 * @param string $expected
	 * @param int|bool $decimal_place
	 *
	 * @cover        give_format_decimal
	 * @dataProvider give_format_decimal_provider
	 */
	public function test_give_format_decimal( $number, $expected, $decimal_place = false ) {
		$output = (string) give_format_decimal( floatval( $number ), $decimal_place );

		$this->assertSame(
			$expected,
			$output
		);
	}


	/**
	 * Data provider for give_format_decimal function.
	 *
	 * @since 1.8
	 * @return array
	 */
	public function give_format_decimal_provider() {
		return array(
			array( '10.5678', '10.568', 3 ),
			array( '10.56', '10.56', 2 ),
			array( '10.567', '10.6', 1 ),
			array( '10.567', '10.567' ),
		);
	}


	/**
	 * Test give_currency_filter function.
	 *
	 * @since        1.8
	 *
	 * @param string $price
	 * @param string $currency
	 * @param string $currency_position
	 * @param bool   $decode_currency
	 * @param string $expected
	 *
	 * @cover        give_currency_filter
	 * @dataProvider give_currency_filter_provider
	 */
	public function test_give_currency_filter( $price, $currency, $currency_position, $decode_currency, $expected ) {
		give_update_option( 'currency', $currency );
		give_update_option( 'currency_position', $currency_position );

		$output = give_currency_filter( $price, $currency, $decode_currency );

		$this->assertSame(
			$expected,
			$output
		);
	}

	/**
	 * Data provider for give_currency_filter function.
	 *
	 * @since 1.8
	 * @return array
	 */
	public function give_currency_filter_provider() {
		return array(
			array( '10', 'USD', 'after', false, '10&#36;' ),
			array( '10', 'ZAR', 'after', false, '10&#82;' ),
			array( '10', 'NOK', 'after', false, '10 &#107;&#114;.' ),
			array( '10', 'USD', 'before', false, '&#36;10' ),
			array( '10', 'ZAR', 'before', false, '&#82;10' ),
			array( '10', 'NOK', 'before', false, '&#107;&#114;. 10' ),

			array( '10', 'USD', 'after', true, '10$' ),
			array( '10', 'ZAR', 'after', true, '10R' ),
			array( '10', 'NOK', 'after', true, '10 kr.' ),
			array( '10', 'USD', 'before', true, '$10' ),
			array( '10', 'ZAR', 'before', true, 'R10' ),
			array( '10', 'NOK', 'before', true, 'kr. 10' ),
		);
	}


	/**
	 * Test give_get_price_decimals.
	 *
	 * @since  1.8
	 *
	 * @cover  give_get_price_decimals
	 * @cover  give_currency_decimal_filter
	 */
	function test_give_get_price_decimals() {

		/*
		 * Check 1
		 *
		 * Fresh install test.
		 */
		$output_number_of_decimal = give_get_price_decimals();

		// Default number of decimals.
		$this->assertEquals(
			2, // Default number of decimal value.
			$output_number_of_decimal,
			'Number of decimal places should be equal to 2'
		);


		/*
		 * Check 2
		 *
		 * Change number of decimal value.
		 */
		give_update_option( 'number_decimals', 3 );

		// Get updated number of decimal
		$output_number_of_decimal = give_get_price_decimals();

		// Default number of decimals.
		$this->assertEquals(
			3,
			$output_number_of_decimal,
			'Number of decimal places should be equal to 3'
		);

		/*
		 * Check 3.
		 *
		 * Change currency
		 */
		give_update_option( 'currency', 'RIAL' );

		// Get updated number of decimal
		$output_number_of_decimal = give_get_price_decimals();

		// Default number of decimals.
		$this->assertEquals(
			0,
			$output_number_of_decimal,
			'Some currency only have  0 number of decimal places. For example: RIAL, JPY, TWD, HUF'
		);

	}


	/**
	 * Test give_date_format.
	 *
	 * @since        1.8
	 *
	 * @cover        give_date_format
	 * @dataProvider give_date_format_provider
	 *
	 * @param string $date_context
	 * @param string $expected
	 * @param string $message
	 */
	function test_give_date_format( $date_context, $expected, $message ) {
		add_filter( 'give_date_format_contexts', array( $this, 'add_new_date_contexts' ) );
		$output = give_date_format( $date_context );
		remove_filter( 'give_date_format_contexts', array( $this, 'add_new_date_contexts' ) );

		$this->assertEquals(
			$expected,
			$output,
			$message
		);
	}


	/**
	 * Add date formats.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function add_new_date_contexts() {
		return array(
			'checkout' => 'F j, Y',
			'report'   => 'Y-m-d',
			'email'    => 'm/d/Y',
		);
	}


	/**
	 * Data provider for give_date_format function.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function give_date_format_provider() {
		// Default date format.
		$wp_default_date_format = get_option( 'date_format' );

		return array(
			array( '', $wp_default_date_format, "Date format should be equal to {$wp_default_date_format}" ),
			array( 'checkout', 'F j, Y', "Date format should be equal to F j, Y" ),
			array( 'report', 'Y-m-d', "Date format should be equal to Y-m-d" ),
			array( 'email', 'm/d/Y', "Date format should be equal to m/d/y" ),
		);
	}


	/**
	 * Test give_get_cache_key function.
	 *
	 * @since 1.8
	 *
	 * @cover give_get_cache_key
	 */
	function test_give_get_cache_key() {
		// Action.
		$input_action = 'get_log_count';

		// Basic query param.
		$input_query_args = array(
			'post_parent'    => 1024,
			'post_type'      => 'give_log',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$output = give_get_cache_key( $input_action, $input_query_args );

		$this->assertEquals( 'give_cache_get_log_count_01f5c4012ed8142', $output );
	}

	/**
	 * Test give_clean() - note this is a basic type test as WP core already.
	 * has coverage for sanitized_text_field().
	 *
	 * @since 1.8
	 * @cover give_clean
	 */
	public function test_give_clean() {
		$this->assertEquals( 'cleaned', give_clean( '<script>alert();</script>cleaned' ) );
	}


	/**
	 * Test give_let_to_num function.
	 *
	 * @since        1.8
	 *
	 * @cover        give_let_to_num
	 * @dataProvider give_let_to_num_provider
	 *
	 * @param  string $size
	 * @param  int    $expected
	 * @param  string $message
	 */
	public function test_give_let_to_num( $size, $expected, $message ) {
		$output = give_let_to_num( $size );
		$this->assertSame(
			$expected,
			$output,
			$message
		);
	}


	/**
	 * Data provider for give_let_to_num function.
	 *
	 * @since 1.8
	 * @return array
	 */
	public function give_let_to_num_provider() {
		return array(
			array( '1P', 1125899906842624, '1P should be equal to 1125899906842624' ),
			array( '1T', 1099511627776, '1T should be equal to 1099511627776' ),
			array( '1G', 1073741824, '1G should be equal to 1073741824' ),
			array( '1M', 1048576, '1M should be equal to 1048576' ),
			array( '1K', 1024, '1K should be equal to 1024' ),
		);
	}

	/**
	 * Test give_validate_nonce function
	 *
	 * @since  1.8
	 *
	 * @cover  give_validate_nonce
	 */
	// function test_give_validate_nonce() {
	// 	$input_nonce = wp_create_nonce( 'give_gateway' );
	//
	// 	/*
	// 	 * If nonce does not validate successfully then WPDieException throw.
	// 	 */
	// 	$this->expectException( 'WPDieException' );
	// 	give_validate_nonce( $input_nonce );
	// }
}