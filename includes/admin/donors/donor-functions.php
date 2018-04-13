<?php
/**
 * Donors
 *
 * @package    Give
 * @subpackage Admin/Donors
 * @copyright  Copyright (c) 2016, WordImpress
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a view for the single donor view.
 *
 * @param array $views An array of existing views.
 *
 * @since 1.0
 *
 * @return array        The altered list of views.
 */
function give_register_default_donor_views( $views ) {

	$default_views = array(
		'overview' => 'give_donor_view',
		'delete'   => 'give_donor_delete_view',
		'notes'    => 'give_donor_notes_view',
	);

	return array_merge( $views, $default_views );

}

add_filter( 'give_donor_views', 'give_register_default_donor_views', 1, 1 );

/**
 * Register a tab for the single donor view.
 *
 * @param array $tabs An array of existing tabs.
 *
 * @since 1.0
 *
 * @return array       The altered list of tabs
 */
function give_register_default_donor_tabs( $tabs ) {

	$default_tabs = array(
		'overview' => array(
			'dashicon' => 'dashicons-admin-users',
			'title'    => __( 'Donor Profile', 'give' ),
		),
		'notes'    => array(
			'dashicon' => 'dashicons-admin-comments',
			'title'    => __( 'Donor Notes', 'give' ),
		),
	);

	return array_merge( $tabs, $default_tabs );
}

add_filter( 'give_donor_tabs', 'give_register_default_donor_tabs', 1, 1 );

/**
 * Register the Delete icon as late as possible so it's at the bottom.
 *
 * @param array $tabs An array of existing tabs.
 *
 * @since 1.0
 *
 * @return array       The altered list of tabs, with 'delete' at the bottom.
 */
function give_register_delete_donor_tab( $tabs ) {

	$tabs['delete'] = array(
		'dashicon' => 'dashicons-trash',
		'title'    => __( 'Delete Donor', 'give' ),
	);

	return $tabs;
}

add_filter( 'give_donor_tabs', 'give_register_delete_donor_tab', PHP_INT_MAX, 1 );

/**
 * Connect and Reconnect Donor with User profile.
 *
 * @todo  $address is unnecessary param because we are store address to user.
 *
 * @param Give_Donor $donor      Donor Object.
 * @param array      $donor_data Donor Post Variables.
 * @param array      $address    Address Information.
 *
 * @since 1.8.14
 *
 * @return array
 */
function give_connect_user_donor_profile( $donor, $donor_data, $address ) {

	$donor_id         = $donor->id;
	$previous_user_id = $donor->user_id;

	/**
	 * Fires before editing a donor.
	 *
	 * @param int   $donor_id   The ID of the donor.
	 * @param array $donor_data The donor data.
	 * @param array $address    The donor's address.
	 *
	 * @since 1.0
	 */
	do_action( 'give_pre_edit_donor', $donor_id, $donor_data, $address );

	$output = array();

	if ( $donor->update( $donor_data ) ) {

		// Create and Update Donor First Name and Last Name in Meta Fields.
		$donor->update_meta( '_give_donor_first_name', $donor_data['first_name'] );
		$donor->update_meta( '_give_donor_last_name', $donor_data['last_name'] );

		// Fetch disconnected user id, if exists.
		$disconnected_user_id = $donor->get_meta( '_give_disconnected_user_id', true );

		// Flag User and Donor Disconnection.
		delete_user_meta( $disconnected_user_id, '_give_is_donor_disconnected' );

		// Check whether the disconnected user id and the reconnected user id are same or not.
		// If both are same then delete user id store in donor meta.
		if ( $donor_data['user_id'] === $disconnected_user_id ) {
			delete_user_meta( $disconnected_user_id, '_give_disconnected_donor_id' );
			$donor->delete_meta( '_give_disconnected_user_id' );
		}

		$output['success']       = true;
		$donor_data              = array_merge( $donor_data, $address );
		$output['customer_info'] = $donor_data;

	} else {

		$output['success'] = false;

	}

	/**
	 * Fires after editing a donor.
	 *
	 * @param int   $donor_id   The ID of the donor.
	 * @param array $donor_data The donor data.
	 *
	 * @since 1.0
	 */
	do_action( 'give_post_edit_donor', $donor_id, $donor_data );

	return $output;
}
