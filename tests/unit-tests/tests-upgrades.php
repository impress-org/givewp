<?php
/**
 * Class Tests_Upgrades
 */
class Tests_Upgrades extends Give_Unit_Test_Case {

	public function setUp() {
		parent::setUp();
		require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_upgrade_completion() {

		$current_upgrades = give_get_completed_upgrades();
		// Since we mark previous upgrades as complete upon install
		$this->assertTrue( ! empty( $current_upgrades ) );
		$this->assertInternalType( 'array', $current_upgrades );

		$this->assertTrue( give_set_upgrade_complete( 'test-upgrade-action' ) );
		$this->assertTrue( give_has_upgrade_completed( 'test-upgrade-action' ) );
		$this->assertFalse( give_has_upgrade_completed( 'test-upgrade-action-false' ) );

	}


	/**
	 * Test: all db upgrade must be auto complete on fresh install
	 *
	 * @since 2.2.4
	 */
	public function test_auto_complete_update_on_fresh_install() {
		$give_updates = Give_Updates::get_instance();

		// Fire action to register db updates.
		do_action( 'give_register_updates', $give_updates );

		$completed_updates = get_option( 'give_completed_upgrades' );

		// Test_Activation cause of fire 'give_upgrades' action hook multiple time which cause of remove few default updates
		// add these missing updates to completed updates.
		$completed_updates = array_unique(
			array_merge(
				$completed_updates,
				array(
					'v201_upgrades_payment_metadata',
					'v201_add_missing_donors',
					'v201_move_metadata_into_new_table',
					'v201_logs_upgrades',
				)
			)
		);

		$registered_updates = Give_Updates::get_instance()->get_update_ids();

		$this->assertFalse( (bool) count( array_diff( $registered_updates, $completed_updates ) ) );
	}
}
