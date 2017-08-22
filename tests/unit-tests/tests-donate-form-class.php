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

}
