<?php

/**
 * Class Tests_Roles
 *
 * @group give_roles
 */
class Tests_Roles extends Give_Unit_Test_Case {

	protected $_roles;

	/**
	 * Setup
	 */
	public function setUp() {
		parent::setUp();

		$this->_roles = new Give_Roles();
		$this->_roles->add_roles();
		$this->_roles->add_caps();
	}

	/**
	 * Test roles.
	 */
	public function test_roles() {

		global $wp_roles;

		$this->assertArrayHasKey( 'give_manager', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'give_accountant', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'give_worker', (array) $wp_roles->role_names );
		$this->assertArrayHasKey( 'give_donor', (array) $wp_roles->role_names );

	}

	/**
	 * Test manager capabilities.
	 */
	public function test_give_manager_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		// Check 1.
		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['read'] );

		// Check 2.
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_posts'] );

		// Check 3.
		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_posts'] );

		// Check 4.
		$this->assertArrayHasKey( 'unfiltered_html', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['unfiltered_html'] );

		// Check 5.
		$this->assertArrayHasKey( 'upload_files', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['upload_files'] );

		// Check 6.
		$this->assertArrayHasKey( 'export', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['export'] );

		// Check 7.
		$this->assertArrayHasKey( 'import', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['import'] );

		// Check 8.
		$this->assertArrayHasKey( 'delete_others_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['delete_others_pages'] );

		// Check 9.
		$this->assertArrayHasKey( 'delete_others_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['delete_others_posts'] );

		// Check 10.
		$this->assertArrayHasKey( 'delete_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_pages'] );

		// Check 11.
		$this->assertArrayHasKey( 'delete_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_private_pages'] );

		// Check 12.
		$this->assertArrayHasKey( 'delete_private_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_private_posts'] );

		// Check 13.
		$this->assertArrayHasKey( 'delete_published_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_published_pages'] );

		// Check 14.
		$this->assertArrayHasKey( 'delete_published_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['delete_published_posts'] );

		// Check 15.
		$this->assertArrayHasKey( 'edit_others_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['edit_others_pages'] );

		// Check 16.
		$this->assertArrayHasKey( 'edit_others_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['edit_others_posts'] );

		// Check 17.
		$this->assertArrayHasKey( 'edit_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_pages'] );

		// Check 18.
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_posts'] );

		// Check 19.
		$this->assertArrayHasKey( 'edit_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_private_pages'] );

		// Check 20.
		$this->assertArrayHasKey( 'edit_private_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_private_posts'] );

		// Check 21.
		$this->assertArrayHasKey( 'edit_published_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_published_pages'] );

		// Check 22.
		$this->assertArrayHasKey( 'edit_published_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['edit_published_posts'] );

		// Check 23.
		$this->assertArrayHasKey( 'manage_categories', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_manager']['capabilities']['manage_categories'] );

		// Check 24.
		$this->assertArrayHasKey( 'manage_links', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['manage_links'] );

		// Check 25.
		$this->assertArrayHasKey( 'moderate_comments', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['moderate_comments'] );

		// Check 26.
		$this->assertArrayHasKey( 'publish_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['publish_pages'] );

		// Check 27.
		$this->assertArrayHasKey( 'publish_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['publish_posts'] );

		// Check 28.
		$this->assertArrayHasKey( 'read_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['read_private_pages'] );

		// Check 29.
		$this->assertArrayHasKey( 'view_give_sensitive_data', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['view_give_sensitive_data'] );

		// Check 30.
		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['export_give_reports'] );

		// Check 31.
		$this->assertArrayHasKey( 'manage_give_settings', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_manager']['capabilities']['manage_give_settings'] );

	}

	/**
	 * Test Administrator User Role.
	 */
	public function test_administrator_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'view_give_reports', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['view_give_reports'] );

		$this->assertArrayHasKey( 'view_give_sensitive_data', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['view_give_sensitive_data'] );

		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['export_give_reports'] );

		$this->assertArrayHasKey( 'manage_give_settings', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['manage_give_settings'] );

		$this->assertArrayHasKey( 'edit_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_give_forms'] );

		$this->assertArrayHasKey( 'delete_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_give_forms'] );

		$this->assertArrayHasKey( 'edit_others_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_others_give_forms'] );

		$this->assertArrayHasKey( 'publish_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['publish_give_forms'] );

		$this->assertArrayHasKey( 'read_private_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['read_private_give_forms'] );

		$this->assertArrayHasKey( 'delete_private_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_private_give_forms'] );

		$this->assertArrayHasKey( 'delete_published_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_published_give_forms'] );

		$this->assertArrayHasKey( 'delete_others_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_others_give_forms'] );

		$this->assertArrayHasKey( 'edit_private_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_private_give_forms'] );

		$this->assertArrayHasKey( 'edit_published_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_published_give_forms'] );

		$this->assertArrayHasKey( 'manage_give_form_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['manage_give_form_terms'] );

		$this->assertArrayHasKey( 'edit_give_form_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_give_form_terms'] );

		$this->assertArrayHasKey( 'delete_give_form_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_give_form_terms'] );

		$this->assertArrayHasKey( 'assign_give_form_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['assign_give_form_terms'] );

		$this->assertArrayHasKey( 'view_give_form_stats', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['view_give_form_stats'] );

		$this->assertArrayHasKey( 'import_give_forms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['import_give_forms'] );

		$this->assertArrayHasKey( 'edit_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_give_payments'] );

		$this->assertArrayHasKey( 'delete_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_give_payments'] );

		$this->assertArrayHasKey( 'edit_others_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_others_give_payments'] );

		$this->assertArrayHasKey( 'edit_private_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_private_give_payments'] );

		$this->assertArrayHasKey( 'edit_published_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_published_give_payments'] );

		$this->assertArrayHasKey( 'read_private_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['read_private_give_payments'] );

		$this->assertArrayHasKey( 'delete_private_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_private_give_payments'] );

		$this->assertArrayHasKey( 'delete_published_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_published_give_payments'] );

		$this->assertArrayHasKey( 'delete_others_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_others_give_payments'] );

		$this->assertArrayHasKey( 'manage_give_payment_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['manage_give_payment_terms'] );

		$this->assertArrayHasKey( 'edit_give_payment_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['edit_give_payment_terms'] );

		$this->assertArrayHasKey( 'delete_give_payment_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['delete_give_payment_terms'] );

		$this->assertArrayHasKey( 'assign_give_payment_terms', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['assign_give_payment_terms'] );

		$this->assertArrayHasKey( 'view_give_payment_stats', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['view_give_payment_stats'] );

		$this->assertArrayHasKey( 'import_give_payments', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['administrator']['capabilities']['import_give_payments'] );
	}

	/**
	 * Tests for Give Accountant User Roles.
	 */
	public function test_give_accountant_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_accountant']['capabilities']['read'] );

		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_accountant']['capabilities']['edit_posts'] );

		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( false, $wp_roles->roles['give_accountant']['capabilities']['delete_posts'] );

		$this->assertArrayHasKey( 'read_private_give_forms', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_accountant']['capabilities']['read_private_give_forms'] );

		$this->assertArrayHasKey( 'view_give_reports', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_accountant']['capabilities']['view_give_reports'] );

		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_accountant']['capabilities']['export_give_reports'] );

		$this->assertArrayHasKey( 'edit_give_payments', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_accountant']['capabilities']['edit_give_payments'] );

	}

	/**
	 * Tests for Give Worker User Role.
	 *
	 * @since 1.8.17
	 */
	public function test_give_worker_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		// Check 1.
		$this->assertArrayHasKey( 'edit_give_payments', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['edit_give_payments'] );

		// Check 2.
		$this->assertArrayHasKey( 'delete_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['delete_give_forms'] );

		// Check 3.
		$this->assertArrayHasKey( 'delete_others_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['delete_others_give_forms'] );

		// Check 4.
		$this->assertArrayHasKey( 'delete_private_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['delete_private_give_forms'] );

		// Check 5.
		$this->assertArrayHasKey( 'delete_published_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['delete_published_give_forms'] );

		// Check 6.
		$this->assertArrayHasKey( 'edit_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['edit_give_forms'] );

		// Check 7.
		$this->assertArrayHasKey( 'edit_others_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['edit_others_give_forms'] );

		// Check 8.
		$this->assertArrayHasKey( 'edit_private_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['edit_private_give_forms'] );

		// Check 9.
		$this->assertArrayHasKey( 'edit_published_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['edit_published_give_forms'] );

		// Check 10.
		$this->assertArrayHasKey( 'publish_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['publish_give_forms'] );

		// Check 11.
		$this->assertArrayHasKey( 'read_private_give_forms', (array) $wp_roles->roles['give_worker']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_worker']['capabilities']['read_private_give_forms'] );

	}

	/**
	 * Tests for Give Donor User Role.
	 *
	 * @since 1.8.17
	 */
	public function test_give_donor_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		// Check 1.
		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['give_donor']['capabilities'] );
		$this->assertEquals( true, $wp_roles->roles['give_donor']['capabilities']['read'] );
	}

}
