<?php


/**
 * Class Tests_Activation
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
	 * @since   1.3.2
	 * @updated 1.8.11 Testing actual settings options are set properly on install.
	 */
	public function test_settings() {
		$give_options = give_get_settings();

		$this->assertArrayHasKey( 'base_country', $give_options );
		$this->assertEquals( 'US', $give_options['base_country'] );

		$this->assertArrayHasKey( 'test_mode', $give_options );
		$this->assertEquals( 'enabled', $give_options['test_mode'] );

		$this->assertArrayHasKey( 'currency', $give_options );
		$this->assertEquals( 'USD', $give_options['currency'] );

		$this->assertArrayHasKey( 'currency_position', $give_options );
		$this->assertEquals( 'before', $give_options['currency_position'] );

		$this->assertArrayHasKey( 'session_lifetime', $give_options );
		$this->assertEquals( '604800', $give_options['session_lifetime'] );

		$this->assertArrayHasKey( 'email_access', $give_options );
		$this->assertEquals( 'enabled', $give_options['email_access'] );

		$this->assertArrayHasKey( 'thousands_separator', $give_options );
		$this->assertEquals( ',', $give_options['thousands_separator'] );

		$this->assertArrayHasKey( 'decimal_separator', $give_options );
		$this->assertEquals( '.', $give_options['decimal_separator'] );

		$this->assertArrayHasKey( 'number_decimals', $give_options );
		$this->assertEquals( 2, $give_options['number_decimals'] );

		$this->assertArrayHasKey( 'css', $give_options );
		$this->assertEquals( 'enabled', $give_options['css'] );

		$this->assertArrayHasKey( 'floatlabels', $give_options );
		$this->assertEquals( 'disabled', $give_options['floatlabels'] );

		$this->assertArrayHasKey( 'forms_singular', $give_options );
		$this->assertEquals( 'enabled', $give_options['forms_singular'] );

		$this->assertArrayHasKey( 'forms_archives', $give_options );
		$this->assertEquals( 'enabled', $give_options['forms_archives'] );

		$this->assertArrayHasKey( 'forms_excerpt', $give_options );
		$this->assertEquals( 'enabled', $give_options['forms_excerpt'] );

		$this->assertArrayHasKey( 'form_featured_img', $give_options );
		$this->assertEquals( 'enabled', $give_options['form_featured_img'] );

		$this->assertArrayHasKey( 'form_sidebar', $give_options );
		$this->assertEquals( 'enabled', $give_options['form_sidebar'] );

		$this->assertArrayHasKey( 'categories', $give_options );
		$this->assertEquals( 'disabled', $give_options['categories'] );

		$this->assertArrayHasKey( 'tags', $give_options );
		$this->assertEquals( 'disabled', $give_options['tags'] );

		$this->assertArrayHasKey( 'terms', $give_options );
		$this->assertEquals( 'disabled', $give_options['terms'] );

		$this->assertArrayHasKey( 'uninstall_on_delete', $give_options );
		$this->assertEquals( 'disabled', $give_options['uninstall_on_delete'] );

		$this->assertArrayHasKey( 'donor_default_user_role', $give_options );
		$this->assertEquals( 'give_donor', $give_options['donor_default_user_role'] );

		$this->assertArrayHasKey( 'the_content_filter', $give_options );
		$this->assertEquals( 'enabled', $give_options['the_content_filter'] );

		$this->assertArrayHasKey( 'scripts_footer', $give_options );
		$this->assertEquals( 'disabled', $give_options['scripts_footer'] );

		$this->assertArrayHasKey( 'agree_to_terms_label', $give_options );
		$this->assertEquals( __( 'Agree to Terms?', 'give' ), $give_options['agree_to_terms_label'] );

		$this->assertArrayHasKey( 'agreement_text', $give_options );
		$this->assertEquals( $give_options['agreement_text'], give_get_default_agreement_text() );

		$this->assertArrayHasKey( 'name_title_prefix', $give_options );
		$this->assertEquals( 'disabled', $give_options['name_title_prefix'] );

	}

	/**
	 * Test give_create_pages()
	 *
	 * @covers ::give_create_pages
	 *
	 * @since  1.8.11
	 */
	public function test_give_create_pages() {

		give_create_pages();

		$give_options = give_get_settings();

		$this->assertArrayHasKey( 'success_page', $give_options );
		$this->assertArrayHasKey( 'failure_page', $give_options );

	}

	/**
	 * Test the install function, installing pages and setting option values.
	 *
	 * @since 1.3.3
	 */
	public function test_install() {
		$origin_upgraded_from = get_option( 'give_version_upgraded_from' );
		$origin_give_version  = get_option( 'give_version' );

		// Prepare values for testing
		delete_option( 'give_settings' );
		delete_option( 'give_version' );

		give_install();

		// Test the give_version_upgraded_from value
		$this->assertFalse( get_option( 'give_version_upgraded_from' ) );

		$this->assertEquals( GIVE_VERSION, get_option( 'give_version' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_manager' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_accountant' ) );
		$this->assertInstanceOf( 'WP_Role', get_role( 'give_worker' ) );

		$this->assertNotFalse( Give_Cache::get( '_give_activation_redirect', true ) );

		// Reset to origin.
		update_option( 'give_version_upgraded_from', $origin_upgraded_from );
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
