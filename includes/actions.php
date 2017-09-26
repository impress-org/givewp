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

	// Add backward compatibility to give-action param ( $_GET )
	if(  empty( $_get_action ) ) {
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

/**
 * Hooks Give actions, when present in the $_POST super global. Every give_action
 * present in $_POST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since  1.0
 *
 * @return void
 */
function give_post_actions() {

	$_post_action = ! empty( $_POST['give_action'] ) ? $_POST['give_action'] : null;


	// Add backward compatibility to give-action param ( $_POST ).
	if(  empty( $_post_action ) ) {
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

/**
 * Connect WordPress user with Donor.
 *
 * @since  1.7
 * @param  int   $user_id   User ID
 * @param  array $user_data User Data
 * @return void
 */
function give_connect_donor_to_wpuser( $user_id, $user_data ){
	/* @var Give_Donor $donor */
	$donor = new Give_Donor( $user_data['user_email'] );

	// Validate donor id and check if do nor is already connect to wp user or not.
	if( $donor->id && ! $donor->user_id ) {

		// Update donor user_id.
		if( $donor->update( array( 'user_id' => $user_id ) ) ) {
			$donor_note = sprintf( esc_html__( 'WordPress user #%d is connected to #%d', 'give' ), $user_id, $donor->id );
			$donor->add_note( $donor_note );

			// Update user_id meta in payments.
			if( ! empty( $donor->payment_ids ) && ( $donations = explode( ',', $donor->payment_ids ) ) ) {
				foreach ( $donations as $donation  ) {
					give_update_meta( $donation, '_give_payment_user_id', $user_id );
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
		&& 'Give_Batch_Donors_Export' === $data['class']
		&& ! empty( $data['forms'] )
		&& isset( $data['give_export_option']['query_id'] )
	) {
		Give_Cache::delete( Give_Cache::get_key( $data['give_export_option']['query_id'] ) );
	}
}
add_action('give_file_export_complete', 'give_donor_batch_export_complete' );

/**
 * Print css for wordpress setting pages.
 *
 * @since 1.8.7
 */
function give_admin_quick_css() {
	/* @var WP_Screen $screen */
	$screen = get_current_screen();

	if( ! ( $screen instanceof WP_Screen ) ) {
		return false;
	}

	switch ( true ) {
		case ( 'plugins' === $screen->base || 'plugins-network' === $screen->base ):
			?>
			<style>
				tr.active.update + tr.give-addon-notice-tr td{
                    box-shadow:none;
                    -webkit-box-shadow:none;
				}
				tr.active + tr.give-addon-notice-tr td{
                    position: relative;
				    top:-1px;
                }
				tr.active + tr.give-addon-notice-tr .notice{
                    margin: 5px 20px 15px 40px;
                }

				tr.give-addon-notice-tr .dashicons {
                    color: #f56e28;
                }
				tr.give-addon-notice-tr td{
					border-left: 4px solid #00a0d2;
				}

				tr.give-addon-notice-tr td{
					padding: 0!important;
				}

                tr.active.update + tr.give-addon-notice-tr .notice{
					margin: 5px 20px 5px 40px;
				}
			</style>
			<?php
	}
}
add_action( 'admin_head', 'give_admin_quick_css' );


/**
 * Set Donation Amount for Multi Level Donation Forms
 *
 * @param int    $form_id
 * @param object $form
 *
 * @since 1.8.9
 *
 * @return void
 */
function give_set_donation_levels_max_min_amount( $form_id ) {
	if (
		( 'set' === $_POST['_give_price_option'] ) ||
		( in_array( '_give_donation_levels', $_POST ) && count( $_POST['_give_donation_levels'] ) <= 0 ) ||
		! ( $donation_levels_amounts = wp_list_pluck( $_POST['_give_donation_levels'], '_give_amount' ) )
	) {
		// Delete old meta.
		give_delete_meta( $form_id, '_give_levels_minimum_amount' );
		give_delete_meta( $form_id, '_give_levels_maximum_amount' );

		return;
	}

	// Sanitize donation level amounts.
	$donation_levels_amounts = array_map( 'give_maybe_sanitize_amount', $donation_levels_amounts );

	$min_amount = min( $donation_levels_amounts );
	$max_amount = max( $donation_levels_amounts );

	// Set Minimum and Maximum amount for Multi Level Donation Forms
	give_update_meta( $form_id, '_give_levels_minimum_amount', $min_amount ? give_sanitize_amount_for_db( $min_amount ) : 0 );
	give_update_meta( $form_id, '_give_levels_maximum_amount', $max_amount? give_sanitize_amount_for_db( $max_amount ) : 0 );
}

add_action( 'give_pre_process_give_forms_meta', 'give_set_donation_levels_max_min_amount', 30 );