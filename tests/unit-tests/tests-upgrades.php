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

}
