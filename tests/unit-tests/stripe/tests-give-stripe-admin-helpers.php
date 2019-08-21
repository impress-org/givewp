<?php
/**
 * Unit tests for Stripe admin helper functions.
 *
 * @since 2.5.5
 */
class Tests_Give_Stripe_Admin_Helpers extends Give_Unit_Test_Case {

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
	 * This unit test function will check whether the supported payment method list for Stripe is changed or not.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_supported_payment_methods() {

		$supported_payment_methods = give_stripe_supported_payment_methods();

		$this->assertTrue( in_array( 'stripe', $supported_payment_methods, true ) );
		$this->assertTrue( in_array( 'stripe_ach', $supported_payment_methods, true ) );
		$this->assertTrue( in_array( 'stripe_ideal', $supported_payment_methods, true ) );
		$this->assertTrue( in_array( 'stripe_google_pay', $supported_payment_methods, true ) );
		$this->assertTrue( in_array( 'stripe_apple_pay', $supported_payment_methods, true ) );

	}
}
