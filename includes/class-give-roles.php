<?php
/**
 * Roles and Capabilities
 *
 * @package     Give
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

/**
 * Give_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 * These roles let us have Give Accountants, Give Workers, etc, each of whom can do
 * certain things within the plugin
 *
 * @since 1.0.0
 */
class Give_Roles {

	/**
	 * Get things going
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_filter( 'give_map_meta_cap', array( $this, 'meta_caps' ), 10, 4 );
	}

	/**
	 * Add new shop roles with default WP caps
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function add_roles() {
		add_role( 'give_manager', __( 'Give Manager', 'give' ), array(
			'read'                   => true,
			'edit_posts'             => true,
			'delete_posts'           => true,
			'unfiltered_html'        => true,
			'upload_files'           => true,
			'export'                 => true,
			'import'                 => true,
			'delete_others_pages'    => true,
			'delete_others_posts'    => true,
			'delete_pages'           => true,
			'delete_private_pages'   => true,
			'delete_private_posts'   => true,
			'delete_published_pages' => true,
			'delete_published_posts' => true,
			'edit_others_pages'      => true,
			'edit_others_posts'      => true,
			'edit_pages'             => true,
			'edit_private_pages'     => true,
			'edit_private_posts'     => true,
			'edit_published_pages'   => true,
			'edit_published_posts'   => true,
			'manage_categories'      => true,
			'manage_links'           => true,
			'moderate_comments'      => true,
			'publish_pages'          => true,
			'publish_posts'          => true,
			'read_private_pages'     => true,
			'read_private_posts'     => true
		) );

		add_role( 'give_accountant', __( 'Give Accountant', 'give' ), array(
		    'read'                   => true,
		    'edit_posts'             => false,
		    'delete_posts'           => false
		) );

		add_role( 'give_worker', __( 'Give Worker', 'give' ), array(
			'read'                   => true,
			'edit_posts'             => false,
			'upload_files'           => true,
			'delete_posts'           => false
		) );

	}

	/**
	 * Add new shop-specific capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_caps() {
		global $wp_roles;

		if ( class_exists('WP_Roles') ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			$wp_roles->add_cap( 'give_manager', 'view_give_reports' );
			$wp_roles->add_cap( 'give_manager', 'view_give_sensitive_data' );
			$wp_roles->add_cap( 'give_manager', 'export_give_reports' );
			$wp_roles->add_cap( 'give_manager', 'manage_give_settings' );

			$wp_roles->add_cap( 'administrator', 'view_give_reports' );
			$wp_roles->add_cap( 'administrator', 'view_give_sensitive_data' );
			$wp_roles->add_cap( 'administrator', 'export_give_reports' );
			$wp_roles->add_cap( 'administrator', 'manage_give_settings' );

			// Add the main post type capabilities
			$capabilities = $this->get_core_caps();
			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->add_cap( 'give_manager', $cap );
					$wp_roles->add_cap( 'administrator', $cap );
					$wp_roles->add_cap( 'give_worker', $cap );
				}
			}

			$wp_roles->add_cap( 'give_accountant', 'edit_give_forms' );
			$wp_roles->add_cap( 'give_accountant', 'read_private_forms' );
			$wp_roles->add_cap( 'give_accountant', 'view_give_reports' );
			$wp_roles->add_cap( 'give_accountant', 'export_give_reports' );
			$wp_roles->add_cap( 'give_accountant', 'edit_give_payments' );

		}
	}

	/**
	 * Gets the core post type capabilities
	 *
	 * @access public
	 * @since  1.0.0
	 * @return array $capabilities Core post type capabilities
	 */
	public function get_core_caps() {
		$capabilities = array();

		$capability_types = array( 'give_forms', 'give_campaigns', 'give_payments' );

		foreach ( $capability_types as $capability_type ) {
			$capabilities[ $capability_type ] = array(
				// Post type
				"edit_{$capability_type}",
				"read_{$capability_type}",
				"delete_{$capability_type}",
				"edit_{$capability_type}s",
				"edit_others_{$capability_type}s",
				"publish_{$capability_type}s",
				"read_private_{$capability_type}s",
				"delete_{$capability_type}s",
				"delete_private_{$capability_type}s",
				"delete_published_{$capability_type}s",
				"delete_others_{$capability_type}s",
				"edit_private_{$capability_type}s",
				"edit_published_{$capability_type}s",

				// Terms
				"manage_{$capability_type}_terms",
				"edit_{$capability_type}_terms",
				"delete_{$capability_type}_terms",
				"assign_{$capability_type}_terms",

				// Custom
				"view_{$capability_type}_stats"
			);
		}

		return $capabilities;
	}

	/**
	 * Map meta caps to primitive caps
	 *
	 * @access public
	 * @since  2.0
	 * @return array $caps
	 */
	public function meta_caps( $caps, $cap, $user_id, $args ) {

		switch( $cap ) {

			case 'view_give_forms_stats' :
				
				if( empty( $args[0] ) ) {
					break;
				}
				
				$form = get_post( $args[0] );
				if ( empty( $form ) ) {
					break;
				}

				if( user_can( $user_id, 'view_give_reports' ) || $user_id == $form->post_author ) {
					$caps = array();
				}

				break;
		}

		return $caps;

	}

	/**
	 * Remove core post type capabilities (called on uninstall)
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function remove_caps() {
		
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}

		if ( is_object( $wp_roles ) ) {
			/** Give Manager Capabilities */
			$wp_roles->remove_cap( 'give_manager', 'view_give_reports' );
			$wp_roles->remove_cap( 'give_manager', 'view_give_sensitive_data' );
			$wp_roles->remove_cap( 'give_manager', 'export_give_reports' );
			$wp_roles->remove_cap( 'give_manager', 'manage_give_settings' );

			/** Site Administrator Capabilities */
			$wp_roles->remove_cap( 'administrator', 'view_give_reports' );
			$wp_roles->remove_cap( 'administrator', 'view_give_sensitive_data' );
			$wp_roles->remove_cap( 'administrator', 'export_give_reports' );
			$wp_roles->remove_cap( 'administrator', 'manage_give_settings' );

			/** Remove the Main Post Type Capabilities */
			$capabilities = $this->get_core_caps();

			foreach ( $capabilities as $cap_group ) {
				foreach ( $cap_group as $cap ) {
					$wp_roles->remove_cap( 'give_manager', $cap );
					$wp_roles->remove_cap( 'administrator', $cap );
					$wp_roles->remove_cap( 'give_worker', $cap );
				}
			}

			/** Shop Accountant Capabilities */
			$wp_roles->remove_cap( 'give_accountant', 'edit_give_forms' );
			$wp_roles->remove_cap( 'give_accountant', 'read_private_forms' );
			$wp_roles->remove_cap( 'give_accountant', 'view_give_reports' );
			$wp_roles->remove_cap( 'give_accountant', 'export_give_reports' );

		}
	}
}
