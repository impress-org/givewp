<?php
/**
 * Upgrade Functions
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install: /includes/install.php @ Appox Line 156
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Perform automatic database upgrades when necessary.
 *
 * @since 1.6
 * @return void
 */
function give_do_automatic_upgrades() {
	$did_upgrade  = false;
	$give_version = preg_replace( '/[^0-9.].*/', '', get_option( 'give_version' ) );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it.
		$give_version = '1.0';
	}

	switch ( true ) {

		case version_compare( $give_version, '1.6', '<' ) :
			give_v16_upgrades();
			$did_upgrade = true;

		case version_compare( $give_version, '1.7', '<' ) :
			give_v17_upgrades();
			$did_upgrade = true;
	}

	if ( $did_upgrade ) {
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	}
}

add_action( 'admin_init', 'give_do_automatic_upgrades' );

/**
 * Display Upgrade Notices
 *
 * @since 1.0
 * @return void
 */
function give_show_upgrade_notices() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'give-upgrades' ) {
		return;
	} // Don't show notices on the upgrades page

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	/*
	 *  NOTICE:
	 *
	 *  When adding new upgrade notices, please be sure to put the action into the upgrades array during install:
	 *  /includes/install.php @ Appox Line 156
	 *
	 */

	//v1.3.2 Upgrades
	if ( version_compare( $give_version, '1.3.2', '<' ) || ! give_has_upgrade_completed( 'upgrade_give_payment_customer_id' ) ) {
		printf(
		/* translators: %s: upgrade URL */
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donor database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_give_payment_customer_id' ) )
		);
	}

	//v1.3.4 Upgrades //ensure the user has gone through 1.3.4
	if ( version_compare( $give_version, '1.3.4', '<' ) || ( ! give_has_upgrade_completed( 'upgrade_give_offline_status' ) && give_has_upgrade_completed( 'upgrade_give_payment_customer_id' ) ) ) {
		printf(
		/* translators: %s: upgrade URL */
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donations database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_give_offline_status' ) )
		);
	}


	// End 'Stepped' upgrade process notices


}

add_action( 'admin_notices', 'give_show_upgrade_notices' );

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 1.0
 * @return void
 */
function give_trigger_upgrades() {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it
		$give_version = '1.0';
		add_option( 'give_version', $give_version );
	}

	update_option( 'give_version', GIVE_VERSION );

	if ( DOING_AJAX ) {
		die( 'complete' );
	} // Let AJAX know that the upgrade is complete
}

add_action( 'wp_ajax_give_trigger_upgrades', 'give_trigger_upgrades' );

/**
 * Check if the upgrade routine has been run for a specific action
 *
 * @since  1.0
 *
 * @param  string $upgrade_action The upgrade action to check completion for
 *
 * @return bool                   If the action has been added to the completed actions array
 */
function give_has_upgrade_completed( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades = give_get_completed_upgrades();

	return in_array( $upgrade_action, $completed_upgrades );

}

/**
 * Adds an upgrade action to the completed upgrades array
 *
 * @since  1.0
 *
 * @param  string $upgrade_action The action to add to the completed upgrades array
 *
 * @return bool                   If the function was successfully added
 */
function give_set_upgrade_complete( $upgrade_action = '' ) {

	if ( empty( $upgrade_action ) ) {
		return false;
	}

	$completed_upgrades   = give_get_completed_upgrades();
	$completed_upgrades[] = $upgrade_action;

	// Remove any blanks, and only show uniques
	$completed_upgrades = array_unique( array_values( $completed_upgrades ) );

	return update_option( 'give_completed_upgrades', $completed_upgrades );
}

/**
 * Get's the array of completed upgrade actions
 *
 * @since  1.0
 * @return array The array of completed upgrades
 */
function give_get_completed_upgrades() {

	$completed_upgrades = get_option( 'give_completed_upgrades' );

	if ( false === $completed_upgrades ) {
		$completed_upgrades = array();
	}

	return $completed_upgrades;

}

/**
 * Upgrades the
 *
 * Standardizes the discrepancies between two metakeys `_give_payment_customer_id` and `_give_payment_donor_id`
 *
 * @since      1.3.2
 *
 */
function give_v132_upgrade_give_payment_customer_id() {
	global $wpdb;
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	//UPDATE DB METAKEYS
	$sql   = "UPDATE $wpdb->postmeta SET meta_key = '_give_payment_customer_id' WHERE meta_key = '_give_payment_donor_id'";
	$query = $wpdb->query( $sql );

	update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	give_set_upgrade_complete( 'upgrade_give_payment_customer_id' );
	delete_option( 'give_doing_upgrade' );
	wp_redirect( admin_url() );
	exit;


}

add_action( 'give_upgrade_give_payment_customer_id', 'give_v132_upgrade_give_payment_customer_id' );

/**
 * Upgrades the Offline Status
 *
 * Reverses the issue where offline donations in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 *
 */
function give_v134_upgrade_give_offline_status() {

	global $wpdb;

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( esc_html__( 'You do not have permission to do Give upgrades.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	ignore_user_abort( true );

	if ( ! give_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	// Get abandoned offline payments
	$select = "SELECT ID FROM $wpdb->posts p ";
	$join   = "LEFT JOIN $wpdb->postmeta m ON p.ID = m.post_id ";
	$where  = "WHERE p.post_type = 'give_payment' ";
	$where .= "AND ( p.post_status = 'abandoned' )";
	$where .= "AND ( m.meta_key = '_give_payment_gateway' AND m.meta_value = 'offline' )";

	$sql            = $select . $join . $where;
	$found_payments = $wpdb->get_col( $sql );


	foreach ( $found_payments as $payment ) {

		//Only change ones marked abandoned since our release last week
		//because the admin may have marked some abandoned themselves
		$modified_time = get_post_modified_time( 'U', false, $payment );

		//1450124863 =  12/10/2015 20:42:25
		if ( $modified_time >= 1450124863 ) {

			give_update_payment_status( $payment, 'pending' );

		}

	}

	update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
	give_set_upgrade_complete( 'upgrade_give_offline_status' );
	delete_option( 'give_doing_upgrade' );
	wp_redirect( admin_url() );
	exit;


}

add_action( 'give_upgrade_give_offline_status', 'give_v134_upgrade_give_offline_status' );

/**
 * Cleanup User Roles
 *
 * This upgrade routine removes unused roles and roles with typos
 *
 * @since      1.5.2
 */
function give_v152_cleanup_users() {

	$give_version = get_option( 'give_version' );

	if ( ! $give_version ) {
		// 1.0 is the first version to use this option so we must add it
		$give_version = '1.0';
	}

	$give_version = preg_replace( '/[^0-9.].*/', '', $give_version );

	//v1.5.2 Upgrades
	if ( version_compare( $give_version, '1.5.2', '<' ) || ! give_has_upgrade_completed( 'upgrade_give_user_caps_cleanup' ) ) {

		//Delete all caps with "ss"
		//Also delete all unused "campaign" roles
		$delete_caps = array(
			'delete_give_formss',
			'delete_others_give_formss',
			'delete_private_give_formss',
			'delete_published_give_formss',
			'read_private_forms',
			'edit_give_formss',
			'edit_others_give_formss',
			'edit_private_give_formss',
			'edit_published_give_formss',
			'publish_give_formss',
			'read_private_give_formss',
			'assign_give_campaigns_terms',
			'delete_give_campaigns',
			'delete_give_campaigns_terms',
			'delete_give_campaignss',
			'delete_others_give_campaignss',
			'delete_private_give_campaignss',
			'delete_published_give_campaignss',
			'edit_give_campaigns',
			'edit_give_campaigns_terms',
			'edit_give_campaignss',
			'edit_others_give_campaignss',
			'edit_private_give_campaignss',
			'edit_published_give_campaignss',
			'manage_give_campaigns_terms',
			'publish_give_campaignss',
			'read_give_campaigns',
			'read_private_give_campaignss',
			'view_give_campaigns_stats',
			'delete_give_paymentss',
			'delete_others_give_paymentss',
			'delete_private_give_paymentss',
			'delete_published_give_paymentss',
			'edit_give_paymentss',
			'edit_others_give_paymentss',
			'edit_private_give_paymentss',
			'edit_published_give_paymentss',
			'publish_give_paymentss',
			'read_private_give_paymentss',
		);

		global $wp_roles;
		foreach ( $delete_caps as $cap ) {
			foreach ( array_keys( $wp_roles->roles ) as $role ) {
				$wp_roles->remove_cap( $role, $cap );
			}
		}

		// Create Give plugin roles
		$roles = new Give_Roles();
		$roles->add_roles();
		$roles->add_caps();

		//The Update Ran
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );
		give_set_upgrade_complete( 'upgrade_give_user_caps_cleanup' );
		delete_option( 'give_doing_upgrade' );

	}

}

add_action( 'admin_init', 'give_v152_cleanup_users' );

/**
 * 1.6 Upgrade routine to create the customer meta table.
 *
 * @since  1.6
 * @return void
 */
function give_v16_upgrades() {
	@Give()->customers->create_table();
	@Give()->customer_meta->create_table();
}

/**
 * 1.7 Upgrade.
 *   a. Update license api data for plugin addons.
 *
 * @since  1.7
 * @return void
 */
function give_v17_upgrades() {
	// Upgrade license data.
	give_v17_upgrade_addon_license_data();
	give_v17_cleanup_roles();
}

/**
 * Upgrade license data
 *
 * @since 1.7
 */
function give_v17_upgrade_addon_license_data() {
	$give_options = give_get_settings();

	$api_url = 'https://givewp.com/give-sl-api/';

	// Get addons license key.
	$addons = array();
	foreach ( $give_options as $key => $value ) {
		if ( false !== strpos( $key, '_license_key' ) ) {
			$addons[ $key ] = $value;
		}
	}

	// Bailout: We do not have any addon license data to upgrade.
	if ( empty( $addons ) ) {
		return false;
	}

	foreach ( $addons as $key => $addon_license ) {

		// Get addon shortname.
		$shortname = str_replace( '_license_key', '', $key );

		// Addon license option name.
		$addon_license_option_name = $shortname . '_license_active';

		// bailout if license is empty.
		if ( empty( $addon_license ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Get addon name.
		$addon_name       = array();
		$addon_name_parts = explode( '_', str_replace( 'give_', '', $shortname ) );
		foreach ( $addon_name_parts as $name_part ) {

			// Fix addon name
			switch ( $name_part ) {
				case 'authorizenet' :
					$name_part = 'authorize.net';
					break;
			}

			$addon_name[] = ucfirst( $name_part );
		}

		$addon_name = implode( ' ', $addon_name );

		// Data to send to the API
		$api_params = array(
			'edd_action' => 'activate_license', //never change from "edd_" to "give_"!
			'license'    => $addon_license,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url()
		);

		// Call the API
		$response = wp_remote_post(
			$api_url,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params
			)
		);

		// Make sure there are no errors
		if ( is_wp_error( $response ) ) {
			delete_option( $addon_license_option_name );
			continue;
		}

		// Tell WordPress to look for updates
		set_site_transient( 'update_plugins', null );

		// Decode license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		update_option( $addon_license_option_name, $license_data );
	}
}


/**
 * Cleanup User Roles.
 *
 * This upgrade routine removes unused roles and roles with typos.
 *
 * @since      1.7
 */
function give_v17_cleanup_roles() {

	//Delete all caps with "_give_forms_" and "_give_payments_"
	//These roles have no usage; the proper is singular.
	$delete_caps = array(
		'view_give_forms_stats',
		'delete_give_forms_terms',
		'assign_give_forms_terms',
		'edit_give_forms_terms',
		'manage_give_forms_terms',
		'view_give_payments_stats',
		'manage_give_payments_terms',
		'edit_give_payments_terms',
		'assign_give_payments_terms',
		'delete_give_payments_terms',
	);

	global $wp_roles;
	foreach ( $delete_caps as $cap ) {
		foreach ( array_keys( $wp_roles->roles ) as $role ) {
			$wp_roles->remove_cap( $role, $cap );
		}
	}

	//Set roles again.
	$roles = new Give_Roles();
	$roles->add_roles();
	$roles->add_caps();

}