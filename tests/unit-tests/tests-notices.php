<?php
/**
 * Class Tests_Notices
 */
class Tests_Notices extends Give_Unit_Test_Case {

	/**
	 * SetUp test class.
	 *
	 * @since 1.8.17
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Tests for print_admin_notice()
	 *
	 * @since 1.8.17
	 */
	public function test_print_admin_notices() {

		// Check 1 - Info Notice.
		$args = array(
			'echo'        => false,
			'notice_type' => 'info',
			'dismissible' => false
		);
		$notice = Give()->notices->print_admin_notices( 'This is admin notice', $args );
		$this->assertEquals( '<div id="give-inline-notice" class="notice-info give-notice notice inline ">This is admin notice</div>', trim($notice) );

		// Check 2 - Error Notice.
		$args = array(
			'echo'        => false,
			'notice_type' => 'error',
			'dismissible' => false
		);
		$notice = Give()->notices->print_admin_notices( 'This is admin notice', $args );
		$this->assertEquals( '<div id="give-inline-notice" class="notice-error give-notice notice inline ">This is admin notice</div>', trim($notice) );

		// Check 3 - Default Warning Notice.
		$args = array(
			'echo'        => false,
			'dismissible' => false
		);
		$notice = Give()->notices->print_admin_notices( 'This is admin notice', $args );
		$this->assertEquals( '<div id="give-inline-notice" class="notice-warning give-notice notice inline ">This is admin notice</div>', trim($notice) );

		// Check 4 - Success Notice.
		$args = array(
			'echo'        => false,
			'notice_type' => 'updated',
			'dismissible' => false
		);
		$notice = Give()->notices->print_admin_notices( 'This is admin notice', $args );
		$this->assertEquals( '<div id="give-inline-notice" class="notice-updated give-notice notice inline ">This is admin notice</div>', trim($notice) );

		// Check 5 - Dismissible Success Notice.
		$args = array(
			'echo'        => false,
			'notice_type' => 'updated',
			'dismissible' => true
		);
		$notice = Give()->notices->print_admin_notices( 'This is admin notice', $args );
		$this->assertEquals( '<div id="give-inline-notice" class="notice-updated give-notice notice inline is-dismissible">This is admin notice</div>', trim($notice) );

	}

}