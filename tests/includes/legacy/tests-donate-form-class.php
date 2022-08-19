<?php


/**
 * @group give_mime
 */
class Tests_Donate_Form_Class extends Give_Unit_Test_Case {
	/**
	 * @since  1.0
	 * @access protected
	 * @var Give_Donate_Form
	 */
	protected $_simple_form;

	/**
	 * @since  1.0
	 * @access protected
	 * @var Give_Donate_Form
	 */
	protected $_multi_form;

	/**
	 * Set it Up
	 */
	public function setUp() {
		parent::setUp();

		// Create form.
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

		// Enable Goal.
		Give()->form_meta->update_meta(
			$simple_form->ID,
			'_give_goal_option',
			'enabled'
		);

		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );
		$this->assertEquals( 5000, $simple_form->get_goal() );
	}

	/**
	 * Test Has Goal
	 *
	 * @covers Give_Donate_Form::get_goal
	 */
	public function test_has_goal() {
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );

		$this->assertFalse( $simple_form->has_goal() );

		// Enable Goal.
		Give()->form_meta->update_meta(
			$simple_form->ID,
			'_give_goal_option',
			'enabled'
		);

		$this->assertTrue( $simple_form->has_goal() );
	}

	/**
	 * Test get form wrap classes
	 *
	 * @since        1.8.17
	 *
	 * @param string $display_styles
	 * @param string $expected
	 *
	 * @covers       Give_Donate_Form::get_form_wrap_classes
	 *
	 * @dataProvider get_form_wrap_classes_provider
	 */
	public function test_get_form_wrap_classes( $display_styles, $expected ) {
		// Disable goal.
		give_update_meta( $this->_simple_form->ID, '_give_goal_option', 'disabled' );

		/* @var Give_Donate_Form $simple_form */
		$simple_form = new Give_Donate_Form( $this->_simple_form->ID );

		/**
		 * Case 1: without goal completed
		 */
		$this->assertSame( $expected, $simple_form->get_form_wrap_classes( array( 'display_style' => $display_styles ) ) );

		// Update display style in DB.
		give_update_meta( $this->_simple_form->ID, '_give_payment_display', $display_styles );
		$this->assertSame( $expected, $simple_form->get_form_wrap_classes( array() ) );

		/**
		 * Case 2: with goal completed
		 */

		// Enable goal.
		give_update_meta( $this->_simple_form->ID, '_give_goal_option', 'enabled' );

		// Default earning for form is 40.00, so set donation goal to less then earnings.
		give_update_meta( $this->_simple_form->ID, '_give_set_goal', '30.00' );
		give_update_meta( $this->_simple_form->ID, '_give_close_form_when_goal_achieved', 'enabled' );
		give_update_meta( $this->_simple_form->ID, '_give_form_status', 'closed' );

		$this->assertSame( 'give-form-wrap give-form-closed', $simple_form->get_form_wrap_classes( array() ) );
	}


	/**
	 * Data provider for get_form_wrap_classes
	 *
	 * @since 1.8.17
	 * @return array
	 */
	public function get_form_wrap_classes_provider() {
		return array(
			array( 'onpage', 'give-form-wrap give-display-onpage' ),
			array( 'modal', 'give-form-wrap give-display-modal' ),
			array( 'reveal', 'give-form-wrap give-display-reveal' ),
			array( 'button', 'give-form-wrap give-display-button give-display-button-only' ),
		);
	}
}
