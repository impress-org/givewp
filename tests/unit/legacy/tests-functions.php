<?php
/**
 * Class Tests_Functions
 */
class Tests_Functions extends Give_Unit_Test_Case {

	/**
	 * @since  2.1
	 * @access protected
	 *
	 * @var Give_Donate_Form
	 */
	protected $_simple_form;

	/**
	 * @since  2.1
	 * @access protected
	 *
	 * @var Give_Donate_Form
	 */
	protected $_multi_form;

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();

		// Create form.
		$this->_simple_form = Give_Helper_Form::create_simple_form();
		$this->_multi_form  = Give_Helper_Form::create_multilevel_form();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Function to test give_goal_progress_stats()
	 *
	 * @since 2.1
	 */
	public function test_give_goal_progress_stats() {

		give_update_meta( $this->_simple_form->ID, '_give_goal_option', 'enabled' );
		give_update_meta( $this->_simple_form->ID, '_give_set_goal', '100.00' );
		$goal_stats = give_goal_progress_stats( $this->_simple_form->ID );

		$this->assertArrayHasKey( 'progress', $goal_stats );
		$this->assertArrayHasKey( 'actual', $goal_stats );
		$this->assertArrayHasKey( 'goal', $goal_stats );
		$this->assertArrayHasKey( 'format', $goal_stats );
		$this->assertTrue( is_string( $goal_stats['format'] ) );
		$this->assertTrue( is_string( $goal_stats['actual'] ) ); // String due to currency symbol.
		$this->assertTrue( is_string( $goal_stats['goal'] ) ); // String due to currency symbol.
		$this->assertTrue( is_double( $goal_stats['progress'] ) );
	}

	/**
	 * Function to test give_is_default_level_id()
	 *
	 * @since 2.2.0
	 */
	public function test_give_is_default_level_id() {
		// Test it by price array.
		$this->assertFalse( give_is_default_level_id( $this->_multi_form->prices[0] ) );
		$this->assertTrue( give_is_default_level_id( $this->_multi_form->prices[1] ) );

		// Test it by level id.
		$this->assertTrue( give_is_default_level_id( 2, $this->_multi_form->ID ) );
		$this->assertFalse( give_is_default_level_id( 2 ) );
	}

	/**
	 * Function to test give_form_get_default_level()
	 *
	 * @since 2.2.0
	 */
	public function test_give_form_get_default_level() {
		// Should return default price ID array.
		$this->assertEquals( give_form_get_default_level( $this->_multi_form->ID ), $this->_multi_form->prices[1] );

		// When passing invalid form id, it should return null.
		$this->assertEquals( give_form_get_default_level( 123 ), null );
	}
}
