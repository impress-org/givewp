<?php


/**
 * @group give_activation
 */
class Tests_Activation extends Give_Unit_Test_Case {

	/**
	 * SetUp test class.
	 *
	 * @since 1.3.2
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * Test if the global settings are set and have settings pages.
	 *
	 * @since 1.3.2
	 */
	public function test_settings() {
		$give_options = give_get_settings();

		$this->assertArrayHasKey( 'history_page', $give_options );
		$this->assertArrayHasKey( 'success_page', $give_options );
		$this->assertArrayHasKey( 'failure_page', $give_options );
	}

	/**
	 * Test the install function, installing pages and setting option values.
	 *
	 * @since 1.3.3
	 */
	public function test_install() {

		$give_options               = give_get_settings();
		$origin_give_options		= $give_options;
		$origin_upgraded_from 		= get_option( 'give_version_upgraded_from' );
		$origin_give_version		= get_option( 'give_version' );

		// Prepare values for testing
		delete_option( 'give_settings' ); 
		update_option( 'give_version', '2.0' );
		$give_options = array();

		give_install();

		// Test the give_version_upgraded_from value
		$this->assertEquals( get_option( 'give_version_upgraded_from' ), '2.0' );

		// Test that new pages are created, and not the same as the already created ones.
		// This is to make sure the test is giving the most accurate results.
		$new_settings = get_option( 'give_settings' );
		$this->assertArrayHasKey( 'success_page', $new_settings );
		$this->assertNotEquals( $origin_give_options['success_page'], $new_settings['success_page'] );
		$this->assertArrayHasKey( 'failure_page', $new_settings );
		$this->assertNotEquals( $origin_give_options['failure_page'], $new_settings['failure_page'] );
		$this->assertArrayHasKey( 'history_page', $new_settings );
		$this->assertNotEquals( $origin_give_options['history_page'], $new_settings['history_page'] );

		$this->assertEquals( GIVE_VERSION, get_option( 'give_version' ) );

		$this->assertInstanceOf( 'WP_Role', get_role( 'give_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_accountant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_worker' ) );

		$this->assertNotFalse( Give_Cache::get( '_give_activation_redirect', true ) );


		// Reset to origin
		wp_delete_post( $new_settings['success_page'], true );
		wp_delete_post( $new_settings['history_page'], true );
		wp_delete_post( $new_settings['failure_page'], true );
		update_option( 'give_version_upgraded_from', $origin_upgraded_from );
		$give_options = $origin_give_options;
		update_option( 'give_version', $origin_give_version );

	}

	/**
	 * Test that the install does not redirect when activating multiple plugins.
	 *
	 * @since 1.3.2
	 */
	public function test_install_bail() {

		$_GET['activate-multi'] = 1;

		give_install();

		$this->assertFalse( get_transient( 'activate-multi' ) );

	}

	/**
	 * Test give_after_install(). Test that the transient gets deleted.
	 *
	 * Since 1.3.2
	 */
	public function test_give_after_install() {

		$give_options = give_get_settings();

		// Prepare for test
		Give_Cache::set( '_give_installed', $give_options, 30, true );

		// Fake admin screen
		set_current_screen( 'dashboard' );

		$this->assertNotFalse( Give_Cache::get( '_give_installed', true ) );

		give_after_install();

		$this->assertFalse( Give_Cache::get( '_give_installed', true ) );

	}

	/**
	 * Test that when not in admin, the function bails.
	 *
	 * @since 1.3.2
	 */
	public function test_give_after_install_bail_no_admin() {

		$give_options = give_get_settings();

		// Prepare for test
		set_current_screen( 'front' );
		Give_Cache::set( '_give_installed', $give_options, 30, true );

		give_after_install();
		$this->assertNotFalse( Give_Cache::get( '_give_installed', true ) );

	}


	/**
	 * Test that give_after_install() bails when transient doesn't exist.
	 * Kind of a useless test, but for coverage :-)
	 *
	 * @since 1.3.2
	 */
	public function test_give_after_install_bail_transient() {

		$give_options = give_get_settings();

		// Fake admin screen
		set_current_screen( 'dashboard' );

		Give_Cache::delete( Give_Cache::get_key( '_give_installed' ) );

		$this->assertNull( give_after_install() );

		// Reset to origin
		Give_Cache::set( '_give_installed', $give_options, 30, true );
	}

	/**
	 * Test that give_install_roles_on_network() bails when $wp_roles is no object.
	 * Kind of a useless test, but for coverage :-)
	 *
	 * @since 1.3.2
	 *
	 * @global WP_Roles $wp_roles
	 */
	public function test_give_install_roles_on_network_bail_object() {

		global $wp_roles;

		$origin_roles = $wp_roles;

		$wp_roles = null;

		$this->assertNull( give_install_roles_on_network() );

		// Reset to origin
		$wp_roles = $origin_roles;

	}

	/**
	 * Test that give_install_roles_on_network() bails when $wp_roles is no object.
	 *
	 * @since 1.3.2
	 *
	 * @global WP_Roles $wp_roles
	 */
	public function test_give_install_roles_on_network() {

		global $wp_roles;

		$origin_roles = $wp_roles;

		// Prepare variables for test
		unset( $wp_roles->roles['give_manager'] );

		give_install_roles_on_network();

		// Test that the roles are created
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_accountant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_worker' ) );

		// Reset to origin
		$wp_roles = $origin_roles;

	}

}
