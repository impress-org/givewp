<?php
/**
 * Front-end Actions
 *
 * @package     Give
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks Give actions, when present in the $_GET superglobal. Every give_action
 * present in $_GET is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function give_get_actions() {

	$_get_action = ! empty( $_GET['give_action'] ) ? $_GET['give_action'] : null;

	// Add backward compatibility to give-action param ( $_GET or $_POST )
	if(  doing_action( 'admin_init' ) && empty( $_get_action ) ) {
		$_get_action = ! empty( $_GET['give-action'] ) ? $_GET['give-action'] : null;
	}

	if ( isset( $_get_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when give_action is present in $_GET.
		 *
		 * @since 1.0
		 *
		 * @param array $_GET Array of HTTP GET variables.
		 */
		do_action( "give_{$_get_action}", $_GET );
	}

}

add_action( 'init', 'give_get_actions' );
add_action( 'admin_init', 'give_get_actions' );

/**
 * Hooks Give actions, when present in the $_POST superglobal. Every give_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function give_post_actions() {

	$_post_action = ! empty( $_POST['give_action'] ) ? $_POST['give_action'] : null;


	// Add backward compatibility to give-action param ( $_GET or $_POST )
	if(  doing_action( 'admin_init' ) && empty( $_post_action ) ) {
		$_post_action = ! empty( $_POST['give-action'] ) ? $_POST['give-action'] : null;
	}

	if ( isset( $_post_action ) ) {
		/**
		 * Fires in WordPress init or admin init, when give_action is present in $_POST.
		 *
		 * @since 1.0
		 *
		 * @param array $_POST Array of HTTP POST variables.
		 */
		do_action( "give_{$_post_action}", $_POST );
	}

}

add_action( 'init', 'give_post_actions' );
add_action( 'admin_init', 'give_post_actions' );

/**
 * Connect WordPress user with Donor.
 *
 * @since  1.7
 * @param  int   $user_id   User ID
 * @param  array $user_data User Data
 * @return void
 */
function give_connect_donor_to_wpuser( $user_id, $user_data ){
	$donor = new Give_Customer( $user_data['user_email'] );

	// Validate donor id and check if do nor is already connect to wp user or not.
	if( $donor->id && ! $donor->user_id ) {

		// Update donor user_id.
		if( $donor->update( array( 'user_id' => $user_id ) ) ) {
			$donor_note = sprintf( esc_html__( 'WordPress user #%d is connected to #%d', 'give' ), $user_id, $donor->id );
			$donor->add_note( $donor_note );

			// Update user_id meta in payments.
			if( ! empty( $donor->payment_ids ) && ( $donations = explode( ',', $donor->payment_ids ) ) ) {
				foreach ( $donations as $donation  ) {
					update_post_meta( $donation, '_give_payment_user_id', $user_id );
				}
			}
		}
	}
}
add_action( 'give_insert_user', 'give_connect_donor_to_wpuser', 10, 2 );


/**
 * Setup site home url check
 *
 * Note: if location of site changes then run cron to validate licenses
 *
 * @since  1.7
 * @return void
 */
function give_validate_license_when_site_migrated() {
	// Store current site address if not already stored.
	$homeurl = home_url();
	if( ! get_option( 'give_site_address_before_migrate' ) ) {
		// Update site address.
		update_option( 'give_site_address_before_migrate', $homeurl );

		return;
	}

	if( $homeurl !== get_option( 'give_site_address_before_migrate' ) ) {
		// Immediately run cron.
		wp_schedule_single_event( time() , 'give_validate_license_when_site_migrated' );

		// Update site address.
		update_option( 'give_site_address_before_migrate', home_url() );
	}

}
add_action( 'init', 'give_validate_license_when_site_migrated' );
add_action( 'admin_init', 'give_validate_license_when_site_migrated' );


/**
 * Processing after donor batch export complete
 *
 * @since 1.8
 * @param $data
 */
function give_donor_batch_export_complete( $data ) {
	// Remove donor ids cache.
	if(
		isset( $data['class'] )
		&& 'Give_Batch_Customers_Export' === $data['class']
		&& ! empty( $data['forms'] )
		&& isset( $data['give_export_option']['query_id'] )
	) {
		delete_transient( $data['give_export_option']['query_id'] );
	}
}
add_action('give_file_export_complete', 'give_donor_batch_export_complete' );
