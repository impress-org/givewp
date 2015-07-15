<?php
/**
 * Install Function
 *
 * @package     Give
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2015, WordImpress
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
 * Runs on plugin install by setting up the post types, custom taxonomies, flushing rewrite rules to initiate the new 'donations' slug and also creates the plugin and populates the settings fields for those plugin pages. After successful install, the user is redirected to the Give Welcome screen.
 *
 * @since 1.0
 * @global $wpdb
 * @global $wp_version
 * @return void
 */
function give_install() {

	global $give_options;

	// Setup the Give Custom Post Types
	give_setup_post_types();

	// Clear the permalinks
	flush_rewrite_rules( false );

	// Add Upgraded From Option
	$current_version = get_option( 'give_version' );
	if ( $current_version ) {
		update_option( 'give_version_upgraded_from', $current_version );
	}

	// Setup some default options
	$options = array();

	// Checks if the Success Page option exists AND that the page exists
	if ( ! get_post( give_get_option( 'success_page' ) ) ) {

		// Purchase Confirmation (Success) Page
		$success = wp_insert_post(
			array(
				'post_title'     => __( 'Donation Confirmation', 'give' ),
				'post_content'   => __( 'Thank you for your donation! [give_receipt]', 'give' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		// Store our page IDs
		$options['success_page'] = $success;
	}

	// Checks if the Failure Page option exists AND that the page exists
	if ( ! get_post( give_get_option( 'failure_page' ) ) ) {

		// Failed Purchase Page
		$failed = wp_insert_post(
			array(
				'post_title'     => __( 'Transaction Failed', 'give' ),
				'post_content'   => __( 'Your transaction failed, please try again or contact site support.', 'give' ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['failure_page'] = $failed;
	}

	// Checks if the History Page option exists AND that the page exists
	if ( ! get_post( give_get_option( 'history_page' ) ) ) {
		// Purchase History (History) Page
		$history = wp_insert_post(
			array(
				'post_title'     => __( 'Donation History', 'give' ),
				'post_content'   => '[donation_history]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options['history_page'] = $history;
	}

	//Fresh Install? Setup Test Mode, Base Country (US), Test Gateway, Currency
	if ( empty( $current_version ) ) {
		$options['base_country']       = 'US';
		$options['test_mode']          = 1;
		$options['currency']           = 'USD';
		$options['session_lifetime']   = '604800';
		$options['gateways']['manual'] = 1;
		$options['default_gateway']    = 'manual'; //default is manual

		//Offline Gateway Setup
		$options['gateways']['offline']             = 1;
		$options['global_offline_donation_content'] = give_get_default_offline_donation_content();

		//Emails
		$options['donation_notification'] = give_get_default_donation_notification_email();
	}

	// Populate some default values
	update_option( 'give_settings', array_merge( $give_options, $options ) );
	update_option( 'give_version', GIVE_VERSION );

	//Update Version Number
	if ( $current_version ) {
		update_option( 'give_version_upgraded_from', $current_version );
	}

	// Create Give roles
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

	$api = new Give_API();
	update_option( 'give_default_api_version', 'v' . $api->get_version() );

	// Create the customers database
	@Give()->customers->create_table();

	// Check for PHP Session support, and enable if available
	Give()->session->use_php_sessions();

	// Add a temporary option to note that Give pages have been created
	set_transient( '_give_installed', $options, 30 );

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	if ( ! $current_version ) {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		// When new upgrade routines are added, mark them as complete on fresh install
		$upgrade_routines = array(
			'upgrade_donor_payments_association'
		);

		foreach ( $upgrade_routines as $upgrade ) {
			give_set_upgrade_complete( $upgrade );
		}
	}

	// Add the transient to redirect
	set_transient( '_give_activation_redirect', true, 30 );

}

register_activation_hook( GIVE_PLUGIN_FILE, 'give_install' );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the give_after_install hook.
 *
 * @since 1.0
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

	// Create the donors database (this ensures it creates it on multisite instances where it is network activated)
	@Give()->customers->create_table();

	// Delete the transient
	delete_transient( '_give_installed' );

	do_action( 'give_after_install', $give_options );

}

add_action( 'admin_init', 'give_after_install' );


/**
 * Install user roles on sub-sites of a network
 *
 * Roles do not get created when Give is network activation so we need to create them during admin_init
 *
 * @since 1.0
 * @return void
 */
function give_install_roles_on_network() {

	global $wp_roles;

	if ( ! is_object( $wp_roles ) ) {
		return;
	}

	if ( ! in_array( 'give_manager', $wp_roles->roles ) ) {

		// Create Give shop roles
		$roles = new Give_Roles;
		$roles->add_roles();
		$roles->add_caps();

	}

}

add_action( 'admin_init', 'give_install_roles_on_network' );