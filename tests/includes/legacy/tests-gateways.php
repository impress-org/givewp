<?php

/**
 * Class Test_Gateways
 */
class Test_Gateways extends Give_Unit_Test_Case {

	/**
	 * @var object $_simple_form
	 */
	private $_simple_form;

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();
		$this->_simple_form = Give_Helper_Form::create_simple_form();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test payment gateways.
	 */
	public function test_payment_gateways() {
        $out = give_get_payment_gateways();
        $this->assertArrayHasKey('paypal', $out);
        $this->assertArrayHasKey('manual', $out);

        $this->assertEquals('PayPal Standard', $out['paypal']['admin_label']);
        $this->assertEquals('PayPal', $out['paypal']['checkout_label']);

        $this->assertEquals('Test Donation', $out['manual']['admin_label']);
        $this->assertEquals('Test Donation', $out['manual']['checkout_label']);
    }

	/**
	 * Test enabled gateways.
	 */
	public function test_enabled_gateways() {

		$give_options = give_get_settings();

		// Test that default gateways are enabled out-of-the-box.
		$default_gateways = give_get_enabled_payment_gateways();
		$this->assertArrayHasKey( 'manual', $default_gateways );
		$this->assertArrayHasKey( 'offline', $default_gateways );
		$this->assertArrayNotHasKey( 'paypal', $default_gateways ); // But, not PayPal (it's not enabled by default).

		// Enable PayPal Standard.
        $options['gateways']['paypal'] = 1;
        update_option('give_settings', array_merge($give_options, $options));
        $default_gateways = give_get_enabled_payment_gateways();
        $this->assertArrayHasKey('paypal', $default_gateways);

        // Change back to default.
        update_option('give_settings', $give_options);
    }

    /**
     * Test give_is_gateway_active
     */
    public function test_is_gateway_active()
    {
        $this->assertFalse(give_is_gateway_active('paypal'));
    }

    /**
     * Test give_get_default_gateway.
     */
    public function test_default_gateway()
    {
        // Manual aka "Test Payment" is default.
        $this->assertEquals('manual', give_get_default_gateway($this->_simple_form->ID));
    }

    /**
     * Test give_get_gateway_admin_label
     */
    public function test_get_gateway_admin_label()
    {
        $this->assertEquals('PayPal Standard', give_get_gateway_admin_label('paypal'));
        $this->assertEquals('Test Donation', give_get_gateway_admin_label('manual'));
    }

    /**
     * Test give_get_gateway_checkout_label
     */
    public function test_get_gateway_checkout_label()
    {
        $this->assertEquals('PayPal', give_get_gateway_checkout_label('paypal'));
        $this->assertEquals('Test Donation', give_get_gateway_checkout_label('manual'));
    }

    /**
     * Test give_get_chosen_gateway
     */
    public function test_chosen_gateway()
    {
        $this->assertEquals('manual', give_get_chosen_gateway($this->_simple_form->ID));
    }

    /**
     * Test give_no_gateway_error.
     *
     * @since 2.0.7 gateway setting can be empty array but if fetch it via give function than default payment gateways will return,
     *              check this add_filter( 'give_get_option_gateways', '__give_validate_active_gateways', 10, 1 );
     */
    public function test_no_gateway_error()
    {
        $give_options = give_get_settings();

        give_update_option('gateways', array());

        give_no_gateway_error();

		$errors = (array) give_get_errors();

		$this->assertArrayNotHasKey( 'no_gateways', $errors );
		// $this->assertEquals( 'You must enable a payment gateway to use Give.', $errors['no_gateways'] );

		// Change back to default.
		update_option( 'give_settings', $give_options );

	}

}
