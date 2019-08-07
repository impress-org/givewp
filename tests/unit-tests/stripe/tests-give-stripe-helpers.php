<?php
/**
 * Unit tests for Stripe helper functions
 *
 * @since 2.5.0
 */
class Tests_Give_Stripe_Helpers extends Give_Unit_Test_Case {

	/**
	 * Unit test for function give_stripe_is_connected();
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function test_give_stripe_is_connected() {

		// Should return false when stripe not connected.
		$this->assertFalse( give_stripe_is_connected() );

		// Ensure that Stripe is connected.
		give_update_option( 'give_stripe_connected', '1' );
		give_update_option( 'give_stripe_user_id', 'acct_xxxxxx' );
		give_update_option( 'live_secret_key', 'sk_xxxxxx' );
		give_update_option( 'test_secret_key', 'sk_test_xxxxxx' );
		give_update_option( 'live_publishable_key', 'pk_xxxxxx' );
		give_update_option( 'test_publishable_key', 'pk_test_xxxxxx' );
		give_update_option( 'stripe_user_api_keys', 'disabled' );

		// Should return true when stripe is connected.
		$this->assertTrue( give_stripe_is_connected() );

	}

	/**
	 * Unit test for function give_stripe_get_secret_key();
	 *
	 * @since 2.5.0
	 *
	 * @return void
	 */
	public function test_give_stripe_get_secret_key() {

		// Set dummy secret key.
		give_update_option( 'test_secret_key', 'sk_test_xxxxxx' );
		give_update_option( 'live_secret_key', 'sk_live_xxxxxx' );

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

		// Set dummy publishable key.
		give_update_option( 'test_publishable_key', 'pk_test_xxxxxx' );
		give_update_option( 'live_publishable_key', 'pk_live_xxxxxx' );

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
}
