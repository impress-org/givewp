<?php
/**
 * Install Function
 *
 * @package     Give
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install
 *
 * Runs on plugin install by setting up the post types, custom taxonomies,
 * flushing rewrite rules to initiate the new 'downloads' slug and also
 * creates the plugin and populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the EDD Welcome
 * screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $edd_options
 * @global $wp_version
 * @return void
 */
function give_install() {

	global $give_options;

	// Setup the Downloads Custom Post Type
	give_setup_post_types();

	// Clear the permalinks
	flush_rewrite_rules();

	// Add Upgraded From Option
	$current_version = get_option( 'give_version' );
	if ( $current_version ) {
		update_option( 'give_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Create Give roles
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();


	// Add a temporary option to note that EDD pages have been created
	set_transient( '_give_installed', $options, 30 );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect
	set_transient( '_give_activation_redirect', true, 30 );

}

register_activation_hook( GIVE_PLUGIN_FILE, 'give_install' );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * give_after_install hook.
 *
 * @since 1.0.0
 * @return void
 */
function give_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$give_options = get_transient( '_give_installed' );

	// Exit if not in admin or the transient doesn't exist
	if ( false === $give_options ) {
		return;
	}

	// Create the customers database (this ensures it creates it on multisite instances where it is network activated)
//	@Give()->customers->create_table();

	// Delete the transient
	delete_transient( '_give_installed' );

	do_action( 'give_after_install', $give_options );

}

add_action( 'admin_init', 'give_after_install' );
