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
	 * @since        1.8.17
	 *
	 * @param array  $notice_args
	 * @param string $expected
	 *
	 * @dataProvider print_admin_notices_provider
	 */
	public function test_print_admin_notices( $notice_args, $expected ) {
		$this->assertEquals( $expected, Give()->notices->print_admin_notices( $notice_args ) );
	}

	/**
	 * Data provider for  test_print_admin_notices
	 *
	 * @since 1.8.17
	 * @return array
	 */
	public function print_admin_notices_provider() {
		return array(
			// Check 1 - Info Notice.
			array(
				array(
					'description' => 'This is admin notice 1',
					'id'          => 'give-inline-notice-1',
					'echo'        => false,
					'notice_type' => 'info',
					'dismissible' => false,
				),
				'<div id="give-inline-notice-1" class="notice-info give-notice notice inline"><p>This is admin notice 1</p></div>',
			),

			// Check 2 - Error Notice.
			array(
				array(
					'description' => 'This is admin notice 2',
					'id'          => 'give-inline-notice-2',
					'echo'        => false,
					'dismissible' => false,
				),
				'<div id="give-inline-notice-2" class="notice-warning give-notice notice inline"><p>This is admin notice 2</p></div>',
			),

			// Check 3 - Default Warning Notice.
			array(
				array(
					'description' => 'This is admin notice 3',
					'id'          => 'give-inline-notice-3',
					'echo'        => false,
					'dismissible' => false,
				),
				'<div id="give-inline-notice-3" class="notice-warning give-notice notice inline"><p>This is admin notice 3</p></div>',
			),

			// Check 4 - Success Notice.
			array(
				array(
					'description' => 'This is admin notice 4',
					'id'          => 'give-inline-notice-4',
					'echo'        => false,
					'notice_type' => 'updated',
					'dismissible' => false,
				),
				'<div id="give-inline-notice-4" class="notice-updated give-notice notice inline"><p>This is admin notice 4</p></div>',
			),

			// Check 5 - Dismissible Success Notice.
			array(
				array(
					'description' => 'This is admin notice 5',
					'id'          => 'give-inline-notice-5',
					'echo'        => false,
					'notice_type' => 'updated',
					'dismissible' => true,
				),
				'<div id="give-inline-notice-5" class="notice-updated give-notice notice inline is-dismissible"><p>This is admin notice 5</p></div>',
			),
		);
	}

}
