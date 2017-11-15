<?php


/**
 * @group give_mime
 */
class Tests_Donate_Form_Class extends Give_Unit_Test_Case {

	protected $_post;

	/**
	 * Set it Up
	 */
	public function setUp() {
		parent::setUp();

		$this->_simple_form = Give_Helper_Form::create_simple_form();
		$this->_multi_form  = Give_Helper_Form::create_multilevel_form();

	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
		Give_Helper_Form::delete_form( $this->_simple_form->ID );
		Give_Helper_Form::delete_form( $this->_multi_form->ID );
	}

	/**
	 * Test Get Form
	 *
	 * @covers Give_Donate_Form
	 */
	public function test_get_form() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );
		$this->assertEquals( $this->_simple_form->ID, $simple_form->ID );
		$this->assertEquals( $this->_simple_form->ID, $simple_form->get_ID() );


		$multi_form = new Give_Donate_Form( $this->_multi_form->ID );
		$this->assertEquals( $this->_multi_form->ID, $multi_form->ID );
		$this->assertEquals( $this->_multi_form->ID, $multi_form->get_ID() );
	}

	/**
	 * Test Get Price
	 *
	 * @covers Give_Donate_Form::get_price
	 */
	public function test_get_price() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );

		$this->assertTrue( $simple_form->is_single_price_mode() );
		$this->assertFalse( $simple_form->has_variable_prices() );
		$this->assertEquals( '20.00', $simple_form->get_price() );
	}

	/**
	 * Test Get Prices
	 *
	 * @covers Give_Donate_Form::get_prices
	 */
	public function test_get_prices() {
		$multi_form = new Give_Donate_Form( $this->_multi_form->ID );

		$this->assertTrue( $multi_form->has_variable_prices() );
		$this->assertFalse( $multi_form->is_single_price_mode() );

		$prices = $multi_form->get_prices();
		$this->assertEquals( 4, count( $prices ) );
	}

	/**
	 * Test Min Price
	 *
	 * @covers Give_Donate_Form::get_minimum_price
	 */
	public function test_minimum_price() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );

		$this->assertEquals( '1.00', $simple_form->get_minimum_price() );
	}

	/**
	 * Test Set Goal
	 *
	 * @covers Give_Donate_Form::get_goal
	 */
	public function test_get_goal() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );
		$this->assertEquals( 0, $simple_form->get_goal() );
		give_update_meta( $simple_form->ID, '_give_set_goal', give_sanitize_amount_for_db( 5000 ) );

		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );
		$this->assertEquals( 5000, $simple_form->get_goal() );
	}

	/**
	 * Test get form wrap classes
	 *
	 * @covers Give_Donate_Form::get_form_wrap_classes
	 */
	public function test_get_form_wrap_classes() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );
		$donation_form_close = $simple_form->is_close_donation_form();

		// Testing donation form is closed class
		$class = $simple_form->get_form_wrap_classes( array() );
		if ( true === $donation_form_close ) {
			$this->assertContains('give-form-closed', $class);
		} else {
			$this->assertNotContains('give-form-closed', $class);
		}

		// Check the model display type class that is pass
		$class = $simple_form->get_form_wrap_classes( array( 'display_style' => 'modal' ) );
		$this->assertContains('give-display-modal', $class);

		// Checking the button display type class that is pass through it
		$class = $simple_form->get_form_wrap_classes( array( 'display_style' => 'button' ) );
		$this->assertContains('give-display-button', $class);
		$this->assertContains('give-display-button-only', $class);

		// Update the DB and  checking if get_form_wrap_classes is fetching the proper class from the give meta.
		$payment_display = give_get_meta( $this->_simple_form->ID, '_give_payment_display', true );
		give_update_meta( $this->_simple_form->ID, '_give_payment_display', 'modal' );
		$class = $simple_form->get_form_wrap_classes( array() );
		$this->assertContains('give-display-modal', $class);
		$payment_display = give_update_meta( $this->_simple_form->ID, '_give_payment_display', $payment_display );
	}

}
