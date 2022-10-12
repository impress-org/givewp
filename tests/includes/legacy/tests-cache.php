<?php

/**
 * @group give_cache
 */
class Tests_Cache extends Give_Unit_Test_Case {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}


	/**
	 * Test function Give_Cache::get_key
	 *
	 * @since        1.8.7
	 *
	 * @param string $action
	 * @param mixed  $query_args
	 * @param string $expected
	 *
	 * @cover        Give_Cache::get_key
	 * @dataProvider give_get_key_provider
	 */
	function test_get_key( $action, $query_args, $expected ) {
		$cache_key = Give_Cache::get_key( $action, $query_args );

		$this->assertEquals( $expected, $cache_key );
	}


	/**
	 * Data provider for test_get_key.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	function give_get_key_provider() {
		return array(
			array(
				'get_forms',
				array(
					'post_type'      => 'give_forms',
					'post_status'    => 'public',
					'posts_per_page' => - 1,
				),
				'give_cache_get_forms_ee39aff779a33ee',
			),
			array( 'get_log', '', 'give_cache_get_log' ),
			array( 'get_log_count', 1670, 'give_cache_get_log_count_2f4881ceee5909a' ),
		);
	}


	/**
	 * Test function Give_Cache::set
	 *
	 * @since        1.8.7
	 *
	 * @param string $cache_key
	 * @param bool   $expected
	 *
	 * @cover        Give_Cache::get
	 * @dataProvider give_get_provider
	 */
	function test_get( $cache_key, $expected ) {
		$result = is_wp_error( Give_Cache::get( $cache_key ) );

		$this->assertEquals( $expected, $result );
	}


	/**
	 * Data provider for test_get.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	function give_get_provider() {
		return array(
			array( 'give_cache_get_forms', false ),
			array( 'give_cache_get_payments', false ),
			array( 'give_get_payments', true ),
		);
	}

	/**
	 * Test function Give_Cache::set
	 *
	 * @since        1.8.7
	 *
	 * @param string $cache_key
	 * @param mixed  $data
	 * @param int    $expiration
	 * @param bool   $expected
	 *
	 * @cover        Give_Cache::set
	 * @dataProvider give_set_provider
	 */
	function test_set( $cache_key, $data, $expiration = 0, $expected ) {
		Give_Cache::set( $cache_key, $data, $expiration );
		$result = (bool) Give_Cache::get( $cache_key );

		$this->assertEquals( $expected, $result );
	}


	/**
	 * Data provider for test_set.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	function give_set_provider() {
		return array(
			array( 'give_cache_get_reports', true, HOUR_IN_SECONDS, true ),
			array( 'give_cache_get_forms', true, null, true ),
			array( 'give_cache_get_logs', 1647, - 1, false ),
		);
	}

	/**
	 * Test function Give_Cache::delete
	 *
	 * @since        1.8.7
	 *
	 * @param string $cache_key
	 * @param bool   $expected
	 *
	 * @cover        Give_Cache::delete
	 * @dataProvider give_delete_provider
	 */
	function test_delete( $cache_key, $expected ) {
		$result = is_wp_error( Give_Cache::delete( $cache_key ) );

		$this->assertEquals( $expected, $result );
	}


	/**
	 * Data provider for test_delete.
	 *
	 * @since 1.8.7
	 *
	 * @return array
	 */
	function give_delete_provider() {
		return array(
			array( 'give_cache_get_forms', false ),
			array( 'give_cache_get_payments', false ),
			array( 'give_get_payments', true ),
		);
	}

	/**
	 * Delete all logging cache.
	 *
	 * @since        1.8.7
	 * @access       public
	 *
	 * @cover        Give_Cache::delete_all_expired
	 * @return bool
	 */
	public function test_delete_all_expired() {
		global $wpdb;

		// Set options
		$options = array(
			array( 'give_cache_get_reports', array( 1647, 1550 ), HOUR_IN_SECONDS, false ),
			array( 'give_cache_get_forms', array( 1647, 1550 ), null, false ),
			array( 'give_cache_get_logs', array( 1647, 1550 ), - 1, false ),
			array( 'give_cache_get_payments', array( 1547, 1650 ), - 3600, false ),
		);

		foreach ( $options as $option ) {
			Give_Cache::set( $option[0], $option[1], $option[2] );
		}

		// Add option.
		add_option( 'give_cache_get_payment_logs', array( 1547, 1650 ) );

		// Delete options
		Give_Cache::delete_all_expired();

		// Get remaining options.
		$options = wp_list_pluck(
			$wpdb->get_results(
				$wpdb->prepare(
					"SELECT option_name
						FROM {$wpdb->options}
						Where option_name
						LIKE '%s'",
					'%give_cache%'
				),
				ARRAY_A
			),
			'option_name'
		);

		$remaining_options = array( 'give_cache_get_reports', 'give_cache_get_forms', 'give_cache_get_payment_logs' );

		$this->assertTrue( in_array( $remaining_options[0], $options ), 'pass0' );
		$this->assertTrue( in_array( $remaining_options[1], $options ) );
		$this->assertTrue( in_array( $remaining_options[2], $options ) );
		$this->assertFalse( in_array( 'give_cache_get_logs', $options ) );
		$this->assertFalse( in_array( 'give_cache_get_payments', $options ) );

		// Delete all options
		Give_Cache::delete_all_expired( true );

		// Get remaining options.
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name
						FROM {$wpdb->options}
						Where option_name
						LIKE '%s'",
				'%give_cache%'
			),
			ARRAY_A
		);

		$this->assertEquals( 0, count( $options ) );
	}


	/**
	 * Test function get_options_like
	 *
	 * @since        1.8.7
	 *
	 * @cover        Give_Cache::get_options_like
	 */
	public function test_get_options_like() {
		$options = array(
			array( 'get_reports', array( 1647, 1550 ), HOUR_IN_SECONDS, false ),
			array( 'get_reports_count', array( 1647, 1550 ), null, false ),
			array( 'get_logs', array( 1647, 1550 ), - 1, false ),
			array( 'get_payments', array( 1547, 1650 ), - 3600, false ),
		);

		foreach ( $options as $option ) {
			Give_Cache::set( $option[0], $option[1], $option[2], true );
		}

		$report_options_list = Give_Cache::get_options_like( 'get_reports' );

		$this->assertEquals( 2, count( $report_options_list ) );
		$this->assertEquals( true, in_array( 'give_cache_get_reports', $report_options_list ) );
		$this->assertEquals( true, in_array( 'give_cache_get_reports', $report_options_list ) );
	}


	/**
	 * Test function Give_Cache::set
	 *
	 * @since        1.8.7
	 *
	 * @cover        Give_Cache::is_valid_cache_key
	 */
	function test_is_valid_cache_key() {
		$this->assertTrue( Give_Cache::is_valid_cache_key( 'give_cache_get_forms_ee39aff779a33ee' ) );
		$this->assertFalse( Give_Cache::is_valid_cache_key( 'give_get_forms_list' ) );
	}
}
