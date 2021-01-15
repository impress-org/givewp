<?php
/**
 * Unit tests for Stripe admin helper functions.
 *
 * @since 2.5.5
 */
class Tests_Give_Stripe_Admin_Helpers extends Give_Unit_Test_Case {

	/**
	 * List of enabled gateways.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @var $gateways
	 */
	public $gateways;

	/**
	 * Setup required variables.
	 *
	 * @since  2.5.5
	 * @access public
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->gateways = give_get_option( 'gateways', array() );
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
		$this->assertTrue( in_array( 'stripe_becs', $supported_payment_methods, true ) );
		$this->assertTrue( in_array( 'stripe_sepa', $supported_payment_methods, true ) );

	}

	/**
	 * This unit test function will check whether any of the Stripe supported payment method is active or not.
	 *
	 * @since  2.5.5
	 * @since 2.8.0 Stripe is now disabled by default, see #5138, #5139
	 * @access public
	 *
	 * @return void
	 */
	public function test_give_stripe_is_any_payment_method_active() {

		/**
		 * Case 1: By default Stripe CC is disabled, so this fn will return false.
		 */
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertFalse( $is_stripe_active );

		/**
		 * Case 2: Ensure Stripe is active when Stripe ACH is enabled.
		 */
		unset( $this->gateways['stripe'] );
		$gateways = array_merge(
			$this->gateways,
			array(
				'stripe_ach' => 1,
			)
		);
		give_update_option( 'gateways', $gateways );
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertTrue( $is_stripe_active );

		/**
		 * Case 3: Ensure Stripe is active when Stripe iDEAL is enabled.
		 */
		unset( $this->gateways['stripe'] );
		$gateways = array_merge(
			$this->gateways,
			array(
				'stripe_ideal' => 1,
			)
		);
		give_update_option( 'gateways', $gateways );
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertTrue( $is_stripe_active );

		/**
		 * Case 4: Ensure Stripe is active when Stripe Google Pay is enabled.
		 */
		unset( $this->gateways['stripe'] );
		$gateways = array_merge(
			$this->gateways,
			array(
				'stripe_google_pay' => 1,
			)
		);
		give_update_option( 'gateways', $gateways );
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertTrue( $is_stripe_active );

		/**
		 * Case 5: Ensure Stripe is active when Stripe Apple Pay is enabled.
		 */
		unset( $this->gateways['stripe'] );
		$gateways = array_merge(
			$this->gateways,
			array(
				'stripe_apple_pay' => 1,
			)
		);
		give_update_option( 'gateways', $gateways );
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertTrue( $is_stripe_active );

		/**
		 * Case 6: Ensure Stripe is not active when all the Stripe supported payment methods are disabled.
		 */
		unset( $this->gateways['stripe'] );
		give_update_option( 'gateways', $this->gateways );
		$is_stripe_active = give_stripe_is_any_payment_method_active();
		$this->assertFalse( $is_stripe_active );
	}
}
