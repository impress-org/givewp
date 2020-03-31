<?php
/**
 * Donors
 *
 * @package    Give
 * @subpackage Admin/Donors
 * @copyright  Copyright (c) 2016, GiveWP
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
			'title' => __( 'Donor Profile', 'give' ),
		),
		'notes'    => array(
			'title' => __( 'Donor Notes', 'give' ),
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
		'title' => __( 'Delete Donor', 'give' ),
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

	$donor_id = $donor->id;

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
		if ( ! empty( $donor_data['first_name'] ) ) {
			$donor->update_meta( '_give_donor_first_name', $donor_data['first_name'] );
		}

		if ( isset( $donor_data['last_name'] ) ) {
			$donor->update_meta( '_give_donor_last_name', $donor_data['last_name'] );
		}

		if ( isset( $donor_data['title'] ) ) {
			$donor->update_meta( '_give_donor_title_prefix', $donor_data['title'] );
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

/**
 * This function is used to delete donor and related donation without redirection.
 *
 * @param int|Give_Donor $donor Donor ID or List of Donor IDs.
 * @param array          $args  List of arguments to handle donor and related donation deletion process.
 *
 * @type bool delete_donation Delete donor linked donations if set to true. Default is false.
 *
 * @since 2.2
 *
 * @return int
 */
function give_delete_donor_and_related_donation( $donor, $args = array() ) {

	// Default Arguments.
	$default_args = array(
		'delete_donation' => false,
	);

	$args = wp_parse_args( $args, $default_args );

	// If $donor not an instance of Give_Donor then create one.
	if ( ! $donor instanceof Give_Donor ) {
		$donor = new Give_Donor( $donor );
	}

	if ( $donor->id > 0 ) {

		// Delete Donor.
		$donor_deleted = Give()->donors->delete( $donor->id );

		// Fetch linked donations of a particular donor.
		$donation_ids = explode( ',', $donor->payment_ids );

		// Proceed to delete related donation, if user opted and donor is deleted successfully.
		if ( $donor_deleted && $args['delete_donation'] ) {
			foreach ( $donation_ids as $donation_id ) {
				give_delete_donation( $donation_id );
			}

			return 2; // Donor and linked Donations deleted.

		} else {
			foreach ( $donation_ids as $donation_id ) {
				give_update_payment_meta( $donation_id, '_give_payment_donor_id', 0 );
			}
		}

		return 1; // Donor deleted but not linked donations.
	}

	return 0; // Incorrect donor id or donor not exists.

}
