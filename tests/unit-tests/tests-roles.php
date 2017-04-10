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

		$this->_roles = new Give_Roles;
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

	}

	/**
	 * Test manager capabilities.
	 */
	public function test_give_manager_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'unfiltered_html', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'upload_files', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'export', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'import', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_others_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_others_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_private_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_published_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'delete_published_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_others_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_others_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_private_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_published_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'edit_published_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_categories', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_links', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'moderate_comments', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'publish_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'publish_posts', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'read_private_pages', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'view_give_sensitive_data', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['give_manager']['capabilities'] );
		$this->assertArrayHasKey( 'manage_give_settings', (array) $wp_roles->roles['give_manager']['capabilities'] );

	}

	/**
	 * Test admin capabilities.
	 */
	public function test_administrator_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'view_give_sensitive_data', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['administrator']['capabilities'] );
		$this->assertArrayHasKey( 'manage_give_settings', (array) $wp_roles->roles['administrator']['capabilities'] );

	}

	public function test_give_accountant_caps() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new \WP_Roles();
			}
		}

		$this->assertArrayHasKey( 'read', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'edit_posts', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'delete_posts', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'read_private_give_forms', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'view_give_reports', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'export_give_reports', (array) $wp_roles->roles['give_accountant']['capabilities'] );
		$this->assertArrayHasKey( 'edit_give_payments', (array) $wp_roles->roles['give_accountant']['capabilities'] );
	}

}
