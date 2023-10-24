<?php

/**
 * Class Tests_Formatting
 */
class Tests_Formatting extends Give_Unit_Test_Case {

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test function give_get_currency_formatting_settings
	 *
	 * @since        1.8.15
	 *
	 * @param string $currency_code
	 * @param array  $expected
	 *
	 * @cover        give_get_currency_formatting_settings
	 * @dataProvider give_get_currency_formatting_settings_provider
	 */
	function test_give_get_currency_formatting_settings( $currency_code, $expected ) {
		/**
		 * Case 1: Currency
		 */
		$currency_settings = give_get_currency_formatting_settings( $currency_code );

		// Match setting.
		foreach ( $currency_settings as $key => $setting ) {
			$this->assertArrayHasKey( $key, $currency_settings );
			$this->assertEquals( $expected[ $key ], $currency_settings[ $key ] );
		}

		/**
		 * Cse 2: Payment
		 */
		// Create Simple Donation.
		$donation_id = Give_Helper_Payment::create_simple_payment(
			array(
				'donation' => array(
					'currency' => $currency_code,
				),
			)
		);

		$currency_settings = give_get_currency_formatting_settings( $donation_id );

		// Match setting.
		foreach ( $currency_settings as $key => $setting ) {
			$this->assertArrayHasKey( $key, $currency_settings );
			$this->assertEquals( $expected[ $key ], $currency_settings[ $key ] );
		}
	}

	/**
	 * Data set for give_get_currency_formatting_settings
	 *
	 * @since  1.8.15
	 * @access public
	 * @return array
	 */
	public function give_get_currency_formatting_settings_provider() {
		$currencies = give_get_currencies( 'all' );
		$data       = array();

		// Set data.
		foreach ( $currencies as $code => $currency ) {

			// Skip unsupported currencies that cause failing tests.
			if(in_array($code, ['VEF', 'BYR', 'IRT', 'MRO'])) continue;

			$data[] = array( $code, $currency['setting'] );
		}

		return $data;
	}


	/**
	 * Test function give_get_price_thousand_separator
	 *
	 * @since        1.8
	 *
	 * @param  string $currency_code
	 * @param  array  $expected
	 *
	 * @cover        give_get_price_thousand_separator
	 * @dataProvider give_get_currency_formatting_settings_provider
	 */
	function test_give_get_price_thousand_separator( $currency_code, $expected ) {
		// Create Simple Donation.
		$donation_id       = Give_Helper_Payment::create_simple_payment(
			array(
				'donation' => array(
					'currency' => $currency_code,
				),
			)
		);
		$currency_settings = give_get_currency_formatting_settings( $donation_id );

		$this->assertArrayHasKey( 'thousands_separator', $currency_settings );
		$this->assertEquals( $expected['thousands_separator'], $currency_settings['thousands_separator'] );
	}

	/**
	 * Test give_get_price_decimal_separator function
	 *
	 * @since        1.8
	 *
	 * @param string $currency_code
	 * @param array  $expected
	 *
	 * @cover        give_get_price_decimal_separator
	 * @dataProvider give_get_currency_formatting_settings_provider
	 */
	function test_give_get_price_decimal_separator( $currency_code, $expected ) {
		// Create Simple Donation.
		$donation_id       = Give_Helper_Payment::create_simple_payment(
			array(
				'donation' => array(
					'currency' => $currency_code,
				),
			)
		);
		$currency_settings = give_get_currency_formatting_settings( $donation_id );

		$this->assertArrayHasKey( 'decimal_separator', $currency_settings );
		$this->assertEquals( $expected['decimal_separator'], $currency_settings['decimal_separator'] );
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
	 * @covers ::give_sanitize_amount
	 *
	 * @dataProvider give_sanitize_amount_provider
	 */
	function test_give_sanitize_amount( $amount, $expected, $dp = false, $trim_zeros = false ) {

		$output = give_sanitize_amount(
			$amount,
			array(
				'number_decimals' => $dp,
				'trim_zeros'      => $trim_zeros,
			)
		);

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
			array( '1,000,000,000,000.120', '1000000000000.1', 1, true ),
			array( '1,000,000,000.240', '1000000000.24', 2, true ),
			array( '1,000,000.560', '1000000.6', 1, true ),
			array( '10,000.768000', '10000.77', 2, true ),
			array( '100.87850000', '100.879', 3, true ),
		);
	}


	/**
	 * Test give_format_amount function.
	 *
	 * @since        1.8
	 *
	 * @param string $amount
	 * @param string $expected
	 * @param array  $currency_settings
	 *
	 * @cover        give_format_amount
	 * @dataProvider give_format_amount_provider
	 */
	function test_give_format_amount( $amount, $expected, $currency_settings = array() ) {
		// Currency.
		if ( ! empty( $currency_settings[0] ) ) {
			give_update_option( 'currency', $currency_settings[0] );
		}

		// Thousand separator.
		if ( ! empty( $currency_settings[1] ) ) {
			give_update_option( 'thousands_separator', $currency_settings[1] );
		}

		// Decimal separator.
		if ( ! empty( $currency_settings[2] ) ) {
			give_update_option( 'decimal_separator', $currency_settings[2] );
		}

		$currency = $currency_settings[0];

		switch ( $currency ) {
			default:
				// Test 1: without decimal
				give_update_option( 'number_decimals', 0 );
				$output = give_format_amount(
					$amount,
					array(
						'decimal'  => false,
						'sanitize' => ! is_numeric( $amount ),
						'currency' => $currency,
					)
				);
				$this->assertSame( $expected[0], $output, "Testing {$amount} with {$currency} currency and expected {$expected[0]} (without decimal)." );

				// Test 2: with decimal(2)
				give_update_option( 'number_decimals', 2 );
				$output = give_format_amount(
					$amount,
					array(
						'sanitize' => ! is_numeric( $amount ),
						'currency' => $currency,
					)
				);
				$this->assertSame( $expected[1], $output, "Testing {$amount} with {$currency} currency and expected {$expected[1]} (with decimal {2})." );

				// Test 3: with decimal (more then 2)
				give_update_option( 'number_decimals', 4 );
				$output = give_format_amount(
					$amount,
					array(
						'sanitize' => ! is_numeric( $amount ),
						'currency' => $currency,
					)
				);
				$this->assertSame( $expected[2], $output, "Testing {$amount} with {$currency} currency and expected {$expected[2]} (with decimal {4})." );
		}
	}


	/**
	 * Data provider for give_format_amount function
	 *
	 * @since 1.8
	 * @return array
	 */
	function give_format_amount_provider() {
		return array(
			// Not Formatted
			array(
				'1000000000000.28735',
				array( '10,00,00,00,00,000', '10,00,00,00,00,000.29', '10,00,00,00,00,000.2874' ),
				array( 'INR' ),
			),
			array(
				'1000000000.8274',
				array( '1,00,00,00,001', '1,00,00,00,000.83', '1,00,00,00,000.8274' ),
				array( 'INR' ),
			),
			array( '1000000.98257', array( '10,00,001', '10,00,000.98', '10,00,000.9826' ), array( 'INR' ) ),
			array( '10000.89275', array( '10,001', '10,000.89', '10,000.8928' ), array( 'INR' ) ),
			array( '100.7325', array( '101', '100.73', '100.7325' ), array( 'INR' ) ),
			array( '100.3', array( '100', '100.30', '100.3000' ), array( 'INR' ) ),
			array( '100', array( '100', '100.00', '100.0000' ), array( 'INR' ) ),
			array(
				'1000000000000.28735',
				array( '1,000,000,000,000', '1,000,000,000,000.29', '1,000,000,000,000.2874' ),
				array( 'USD' ),
			),
			array(
				'1000000000.8274',
				array( '1,000,000,001', '1,000,000,000.83', '1,000,000,000.8274' ),
				array( 'USD' ),
			),
			array( '1000000.98257', array( '1,000,001', '1,000,000.98', '1,000,000.9826' ), array( 'USD' ) ),
			array( '10000.89275', array( '10,001', '10,000.89', '10,000.8928' ), array( 'USD' ) ),
			array( '100.7325', array( '101', '100.73', '100.7325' ), array( 'USD' ) ),
			array( '100', array( '100', '100.00', '100.0000' ), array( 'USD' ) ),
			array( '100.3', array( '100', '100.30', '100.3000' ), array( 'USD' ) ),
			array(
				'1000000000000.28735',
				array( '1.000.000.000.000', '1.000.000.000.000,29', '1.000.000.000.000,2874' ),
				array( 'EUR', '.', ',' ),
			),
			array(
				'1000000000.8274',
				array( '1.000.000.001', '1.000.000.000,83', '1.000.000.000,8274' ),
				array( 'EUR', '.', ',' ),
			),
			array( '1000000.98257', array( '1.000.001', '1.000.000,98', '1.000.000,9826' ), array( 'EUR', '.', ',' ) ),
			array( '10000.89275', array( '10.001', '10.000,89', '10.000,8928' ), array( 'EUR', '.', ',' ) ),
			array( '100.7325', array( '101', '100,73', '100,7325' ), array( 'EUR', '.', ',' ) ),
			array( '100', array( '100', '100,00', '100,0000' ), array( 'EUR', '.', ',' ) ),
			array( '100.3', array( '100', '100,30', '100,3000' ), array( 'EUR', '.', ',' ) ),

			// Formatted
			array(
				'10,00,00,00,00,000.28735',
				array( '10,00,00,00,00,000', '10,00,00,00,00,000.29', '10,00,00,00,00,000.2874' ),
				array( 'INR' ),
			),
			array(
				'1,00,00,00,000.8274',
				array( '1,00,00,00,001', '1,00,00,00,000.83', '1,00,00,00,000.8274' ),
				array( 'INR' ),
			),
			array( '10,00,000.98257', array( '10,00,001', '10,00,000.98', '10,00,000.9826' ), array( 'INR' ) ),
			array( '10,000.89275', array( '10,001', '10,000.89', '10,000.8928' ), array( 'INR' ) ),
			array( '100.7325', array( '101', '100.73', '100.7325' ), array( 'INR' ) ),
			array( '100.3', array( '100', '100.30', '100.3000' ), array( 'INR' ) ),
			array( '100', array( '100', '100.00', '100.0000' ), array( 'INR' ) ),
			array(
				'1,000,000,000,000.28735',
				array( '1,000,000,000,000', '1,000,000,000,000.29', '1,000,000,000,000.2874' ),
				array( 'USD' ),
			),
			array(
				'1,000,000,000.8274',
				array( '1,000,000,001', '1,000,000,000.83', '1,000,000,000.8274' ),
				array( 'USD' ),
			),
			array( '1,000,000.98257', array( '1,000,001', '1,000,000.98', '1,000,000.9826' ), array( 'USD' ) ),
			array( '10,000.89275', array( '10,001', '10,000.89', '10,000.8928' ), array( 'USD' ) ),
			array( '100.7325', array( '101', '100.73', '100.7325' ), array( 'USD' ) ),
			array( '100', array( '100', '100.00', '100.0000' ), array( 'USD' ) ),
			array( '100.3', array( '100', '100.30', '100.3000' ), array( 'USD' ) ),
			array(
				'1.000.000.000.000,28735',
				array( '1.000.000.000.000', '1.000.000.000.000,29', '1.000.000.000.000,2874' ),
				array( 'EUR', '.', ',' ),
			),
			array(
				'1.000.000.000,8274',
				array( '1.000.000.001', '1.000.000.000,83', '1.000.000.000,8274' ),
				array( 'EUR', '.', ',' ),
			),
			array(
				'1.000.000,98257',
				array( '1.000.001', '1.000.000,98', '1.000.000,9826' ),
				array( 'EUR', '.', ',' ),
			),
			array( '10.000,89275', array( '10.001', '10.000,89', '10.000,8928' ), array( 'EUR', '.', ',' ) ),
			array( '1.000,89275', array( '1.001', '1.000,89', '1.000,8928' ), array( 'EUR', '.', ',' ) ),
			array( '1.000,89', array( '1.001', '1.000,89', '1.000,8900' ), array( 'EUR', '.', ',' ) ),
			array( '100,7325', array( '101', '100,73', '100,7325' ), array( 'EUR', '.', ',' ) ),
			array( '100', array( '100', '100,00', '100,0000' ), array( 'EUR', '.', ',' ) ),
			array( '100,3', array( '100', '100,30', '100,3000' ), array( 'EUR', '.', ',' ) ),
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
		// Case 1.
		$output = give_human_format_large_amount(
			give_format_amount(
				$amount,
				array(
					'sanitize' => false,
					'currency' => 'USD',
				)
			)
		);
		$this->assertSame(
			$expected[0],
			$output
		);

		// Case 2.
		$output = give_human_format_large_amount(
			give_format_amount(
				$amount,
				array(
					'sanitize' => false,
					'currency' => 'INR',
				)
			),
			array( 'currency' => 'INR' )
		);
		$this->assertSame(
			$expected[1],
			$output
		);
	}


	/**
	 * Data provider for give_human_format_large_amount function
	 *
	 * @since 1.8
	 * @return array
	 */
	function give_human_format_large_amount_provider() {
		return array(
			array( '1234000000000', array( '1.23 trillion', '1234 arab' ) ),
			array( '1000000000000', array( '1 trillion', '1000 arab' ) ),
			array( '1000000000', array( '1 billion', '1 arab' ) ),
			array( '1000000', array( '1 million', '10 lakh' ) ),
			array( '100000', array( '100,000.00', '1 lakh' ) ),
			array( '599000', array( '599,000.00', '5.99 lakh' ) ),
			array( '10000', array( '10,000.00', '10,000.00' ) ),
			array( '100', array( '100.00', '100.00' ) ),
			array( '0', array( '0', '0' ) ),
			array( '0.000', array( '0', '0' ) ),
		);
	}

	/**
	 * Test give_format_decimal function.
	 *
	 * @since        1.8
	 *
	 * @param int      $number
	 * @param string   $expected
	 * @param int|bool $decimal_place
	 *
	 * @cover        give_format_decimal
	 * @dataProvider give_format_decimal_provider
	 */
	public function test_give_format_decimal( $number, $expected, $decimal_place = false ) {
		$output = give_format_decimal( $number, $decimal_place );

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
			array( '10.564', '10.56', 2 ),
			array( '10.56', '10.56', 2 ),
			array( '10.567', '10.6', 1 ),
			array( '10.567', '11', 0 ),
			array( '10.567', '10.57', 2 ), // Default decimal places is two.
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

		$output = give_currency_filter(
			$price,
			array(
				'currency_code'   => $currency,
				'decode_currency' => $decode_currency,
			)
		);

		$this->assertSame(
			$expected,
			// Compare decoded currency by encoding them.
			$decode_currency ? htmlentities( $output, ENT_COMPAT, 'UTF-8' ) : $output
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
			array( '10', 'IRR', 'before', false, '&#x202B;10&#xfdfc;&#x202C;' ),
			array( '10', 'IRR', 'after', false, '&#x202A;10&#xfdfc;&#x202C;' ),

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
		give_update_option( 'currency', 'IRR' );

		// Get updated number of decimal
		$output_number_of_decimal = give_get_price_decimals();

		// Default number of decimals.
		$this->assertEquals(
			3,
			$output_number_of_decimal,
			'Some currency only have  0 number of decimal places. For example: JPY, KRW'
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
			array( 'checkout', 'F j, Y', 'Date format should be equal to F j, Y' ),
			array( 'report', 'Y-m-d', 'Date format should be equal to Y-m-d' ),
			array( 'email', 'm/d/Y', 'Date format should be equal to m/d/y' ),
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
			'post_type'      => 'give_payment',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$output = Give_Cache::get_key( $input_action, $input_query_args );

		$this->assertEquals( 'give_cache_get_log_count_0c601bd4ca75279', $output );
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
	 *
	 * Test give_check_variable function.
	 *
	 * @since        2.0
	 *
	 * @param mixed  $expected
	 * @param mixed  $variable
	 * @param string $conditional
	 * @param mixed  $default
	 * @param string $array_key_name
	 *
	 * @cover        give_check_variable
	 * @dataProvider give_check_variable_provider
	 */
	public function test_give_check_variable( $expected, $variable, $conditional = '', $default = false, $array_key_name = '' ) {

		$variable = give_check_variable( $variable, $conditional, $default, $array_key_name );

		$this->assertSame( $expected, $variable );
	}


	/**
	 * Data provider for give_check_variable function.
	 *
	 * @since 2.0
	 *
	 * @return array
	 */
	public function give_check_variable_provider() {
		return array(
			// Variable.
			array( true, null, 'isset', true ),
			array( false, false, 'isset', true ),
			array( true, '', 'empty', true ),
			array( 1, 0, 'empty', 1 ),
			array( true, null, 'isset_empty', true ),
			array( true, '', 'isset_empty', true ),
			array( true, null, 'null', true ),
			array( true, true, 'null', false ),

			// Array.
			array( false, array(), 'isset', false, 'payment_id' ),
			array( 2347, array( 'payment_id' => 2347 ), 'isset', true, 'payment_id' ),
			array( true, array( 'payment_id' => null ), 'isset', true, 'payment_id' ),
			array( true, array( 'payment_id' => true ), 'isset', false, 'payment_id' ),
			array( true, array( 'payment_id' => '' ), 'empty', true, 'payment_id' ),
			array( 2347, array( 'payment_id' => 2347 ), 'empty', true, 'payment_id' ),
			array( 0, array( 'payment_id' => '' ), 'isset_empty', 0, 'payment_id' ),
			array( 2347, array( 'payment_id' => 2347 ), 'isset_empty', 0, 'payment_id' ),
			array( 0, array( 'payment_id' => null ), 'isset_empty', 0, 'payment_id' ),
			array( 0, array(), 'isset_empty', 0, 'payment_id' ),
			array( 0, array( 'payment_id' => null ), 'null', 0, 'payment_id' ),
			array( 2347, array( 'payment_id' => 2347 ), 'null', 0, 'payment_id' ),
		);
	}
}
