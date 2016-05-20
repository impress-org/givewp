<?php
/**
 * Upgrade Functions
 *
 * @package     Give
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 *
 * NOTICE: When adding new upgrade notices, please be sure to put the action into the upgrades array during install: /includes/install.php @ Appox Line 156
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			'<div class="updated"><p>' . __( 'Give needs to upgrade the donor database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
			esc_url( admin_url( 'index.php?page=give-upgrades&give-upgrade=upgrade_give_payment_customer_id' ) )
		);
	}

	//v1.3.4 Upgrades //ensure the user has gone through 1.3.4
	if ( version_compare( $give_version, '1.3.4', '<' ) || ( ! give_has_upgrade_completed( 'upgrade_give_offline_status' ) && give_has_upgrade_completed( 'upgrade_give_payment_customer_id' ) ) ) {
		printf(
			'<div class="updated"><p>' . __( 'Give needs to upgrade the transaction database, click <a href="%s">here</a> to start the upgrade.', 'give' ) . '</p></div>',
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
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
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
 * @param  string $upgrade_action The action to add to the copmleted upgrades array
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
 * @description: Standardizes the discrepancies between two metakeys `_give_payment_customer_id` and `_give_payment_donor_id`
 *
 * @since      1.3.2
 *
 */
function give_v132_upgrade_give_payment_customer_id() {
	global $wpdb;
	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
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
 * @description: Reverses the issue where offline donation transactions in "pending" status where inappropriately marked as abandoned
 *
 * @since      1.3.4
 *
 */
function give_v134_upgrade_give_offline_status() {

	global $wpdb;

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		wp_die( __( 'You do not have permission to do Give upgrades', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
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
