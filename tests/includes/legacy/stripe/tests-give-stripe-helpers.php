<?php
/**
 * Unit tests for Stripe helper functions
 *
 * @since 2.5.0
 */
class Tests_Give_Stripe_Helpers extends Give_Unit_Test_Case {

	/**
	 * Unit test for function give_stripe_get_secret_key();
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function test_give_stripe_get_secret_key() {

		$accounts = [
			'account_1' => [
				'type'                 => 'connect',
				'live_secret_key'      => 'sk_live_xxxxxxxx',
				'live_publishable_key' => 'pk_live_xxxxxxxx',
				'test_secret_key'      => 'sk_test_xxxxxxxx',
				'test_publishable_key' => 'pk_test_xxxxxxxx',
			],
			'account_2' => [
				'type'                 => 'manual',
				'live_secret_key'      => 'sk_live_xxxxxxxx',
				'live_publishable_key' => 'pk_live_xxxxxxxx',
				'test_secret_key'      => 'sk_test_xxxxxxxx',
				'test_publishable_key' => 'pk_test_xxxxxxxx',
			],
		];
		give_update_option( '_give_stripe_get_all_accounts', $accounts );

		// Set dummy default key.
		give_update_option( '_give_stripe_default_account', 'account_1' );

		$this->assertStringStartsWith( 'sk_test_', give_stripe_get_secret_key() );

		// Set Live mode.
		give_update_option( 'test_mode', 'disabled' );

		$this->assertStringStartsWith( 'sk_live_', give_stripe_get_secret_key() );

	}

	/**
	 * Unit test for function give_stripe_get_publishable_key();
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function test_give_stripe_get_publishable_key() {

		$accounts = [
			'account_1' => [
				'type'                 => 'connect',
				'live_secret_key'      => 'sk_live_xxxxxxxx',
				'live_publishable_key' => 'pk_live_xxxxxxxx',
				'test_secret_key'      => 'sk_test_xxxxxxxx',
				'test_publishable_key' => 'pk_test_xxxxxxxx',
			],
			'account_2' => [
				'type'                 => 'manual',
				'live_secret_key'      => 'sk_live_xxxxxxxx',
				'live_publishable_key' => 'pk_live_xxxxxxxx',
				'test_secret_key'      => 'sk_test_xxxxxxxx',
				'test_publishable_key' => 'pk_test_xxxxxxxx',
			],
		];
		give_update_option( '_give_stripe_get_all_accounts', $accounts );

		// Set dummy default key.
		give_update_option( '_give_stripe_default_account', 'account_2' );

		$this->assertStringStartsWith( 'pk_test_', give_stripe_get_publishable_key() );

		// Set Live mode.
		give_update_option( 'test_mode', 'disabled' );

		$this->assertStringStartsWith( 'pk_live_', give_stripe_get_publishable_key() );
	}

	/**
	 * Unit test for function give_stripe_format_amount();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_format_amount() {

		/**
		 * Case 1: Non zero-decimal currency.
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'USD' );
		$amount = give_stripe_format_amount( 13.24 );
		$this->assertEquals( 1324, $amount );

		/**
		 * Case 2: Zero-decimal currency.
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'JPY' );
		$amount = give_stripe_format_amount( 1324 );
		$this->assertEquals( 1324, $amount );
	}

	/**
	 * Unit test for function give_stripe_dollars_to_cents();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_dollars_to_cents() {

		/**
		 * Case 1: Amount with decimal to `.00`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_dollars_to_cents( 25.00 );
		$this->assertEquals( 2500, $amount );

		/**
		 * Case 2: Amount with decimal to less than `.50`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_dollars_to_cents( 13.24 );
		$this->assertEquals( 1324, $amount );

		/**
		 * Case 3: Amount with decimal to greater than `.50`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_dollars_to_cents( 10.78 );
		$this->assertEquals( 1078, $amount );
	}

	/**
	 * Unit test for function give_stripe_cents_to_dollars();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_cents_to_dollars() {

		/**
		 * Case 1: Amount with decimal to `.00`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_cents_to_dollars( 2500 );
		$this->assertEquals( 25.00, $amount );

		/**
		 * Case 2: Amount with decimal to less than `.50`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_cents_to_dollars( 1324 );
		$this->assertEquals( 13.24, $amount );

		/**
		 * Case 3: Amount with decimal to greater than `.50`.
		 *
		 * @since 2.5.4
		 */
		$amount = give_stripe_cents_to_dollars( 1078 );
		$this->assertEquals( 10.78, $amount );
	}

	/**
	 * Unit test for function give_stripe_get_application_fee_percentage();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_get_application_fee_percentage() {

		/**
		 * Case 1: Non zero-decimal currency.
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'USD' );
		$fee_percentage = give_stripe_get_application_fee_percentage();

		// We're asserting with `0.02` as the percentage are based on units and not sub-units.
		// So, converting the fee percentage to be compatible with units is more sensible.
		$this->assertEquals( 2, $fee_percentage );

		/**
		 * Case 2: Zero-decimal currency.
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'JPY' );
		$fee_percentage = give_stripe_get_application_fee_percentage();
		$this->assertEquals( 2, $fee_percentage );
	}

	/**
	 * Unit test for function give_stripe_get_application_fee_amount();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_get_application_fee_amount() {

		/**
		 * Case 1: Non zero-decimal currency with decimal value.
		 *
		 * Example: $13.24 = 1324 cents
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'USD' );
		$amount = give_stripe_get_application_fee_amount( 1324 );
		$this->assertEquals( 26.0, $amount );

		/**
		 * Case 2: Non zero-decimal currency without decimal value.
		 *
		 * Example: $25.00 = 2500 cents
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'USD' );
		$amount = give_stripe_get_application_fee_amount( 2500 );
		$this->assertEquals( 50.0, $amount );

		/**
		 * Case 3: Zero-decimal currency with decimal value.
		 *
		 * Example: 1324 Yen = 1324 Yen as Yen is sub-unit
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'JPY' );
		$amount = give_stripe_get_application_fee_amount( 1324 );
		$this->assertEquals( 26.0, $amount );

		/**
		 * Case 4: Non zero-decimal currency without decimal value.
		 *
		 * Example: 2500 Yen = 2500 Yen as Yen is sub-unit
		 *
		 * @since 2.5.4
		 */
		give_update_option( 'currency', 'JPY' );
		$amount = give_stripe_get_application_fee_amount( 2500 );
		$this->assertEquals( 50.0, $amount );
	}

	/**
	 * Unit test for function give_stripe_is_source_type();
	 *
	 * @since  2.5.4
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_is_source_type() {

		/**
		 * Case 1: Ensure that the id matches the type of source.
		 *
		 * @since 2.5.4
		 */
		$is_valid = give_stripe_is_source_type( 'src_xxxxxx', 'src' );
		$this->assertTrue( $is_valid );

		/**
		 * Case 1: Ensure that the random id doesn't matches the type of source.
		 *
		 * @since 2.5.4
		 */
		$is_valid = give_stripe_is_source_type( 'pm_xxxxxx', 'src' );
		$this->assertFalse( $is_valid );
	}
}
