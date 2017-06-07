<?php
/**
 * Admin Actions
 *
 * @package     Give
 * @subpackage  Admin/Actions
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Hide subscription notice if admin click on "Click here if already renewed" in subscription notice.
 *
 * @since 1.7
 * @return void
 */
function give_hide_subscription_notices() {

	// Hide subscription notices permanently.
	if ( ! empty( $_GET['_give_hide_license_notices_permanently'] ) ) {
		$current_user = wp_get_current_user();

		// check previously disabled notice ids.
		$already_dismiss_notices = ( $already_dismiss_notices = get_user_meta( $current_user->ID, '_give_hide_license_notices_permanently', true ) )
			? $already_dismiss_notices
			: array();

		// Get notice id.
		$notice_id = sanitize_text_field( $_GET['_give_hide_license_notices_permanently'] );

		if ( ! in_array( $notice_id, $already_dismiss_notices ) ) {
			$already_dismiss_notices[] = $notice_id;
		}

		// Store subscription ids.
		update_user_meta( $current_user->ID, '_give_hide_license_notices_permanently', $already_dismiss_notices );

		// Redirect user.
		wp_safe_redirect( remove_query_arg( '_give_hide_license_notices_permanently', $_SERVER['REQUEST_URI'] ) );
		exit();
	}

	// Hide subscription notices shortly.
	if ( ! empty( $_GET['_give_hide_license_notices_shortly'] ) ) {
		$current_user = wp_get_current_user();

		// Get notice id.
		$notice_id = sanitize_text_field( $_GET['_give_hide_license_notices_shortly'] );

		// Transient key name.
		$transient_key = "_give_hide_license_notices_shortly_{$current_user->ID}_{$notice_id}";

		if ( Give_Cache::get( $transient_key, true ) ) {
			return;
		}

		// Hide notice for 24 hours.
		Give_Cache::set( $transient_key, true, DAY_IN_SECONDS, true );

		// Redirect user.
		wp_safe_redirect( remove_query_arg( '_give_hide_license_notices_shortly', $_SERVER['REQUEST_URI'] ) );
		exit();
	}
}

add_action( 'admin_init', 'give_hide_subscription_notices' );

/**
 * Load wp editor by ajax.
 *
 * @since 1.8
 */
function give_load_wp_editor() {
	if ( ! isset( $_POST['wp_editor'] ) ) {
		die();
	}

	$wp_editor                     = json_decode( base64_decode( $_POST['wp_editor'] ), true );
	$wp_editor[2]['textarea_name'] = $_POST['textarea_name'];

	wp_editor( $wp_editor[0], $_POST['wp_editor_id'], $wp_editor[2] );

	die();
}

add_action( 'wp_ajax_give_load_wp_editor', 'give_load_wp_editor' );


/**
 * Redirect admin to clean url give admin pages.
 *
 * @since 1.8
 *
 * @return bool
 */
function give_redirect_to_clean_url_admin_pages() {
	// Give admin pages.
	$give_pages = array(
		'give-payment-history',
		'give-donors',
		'give-reports'
	);

	// Get current page.
	$current_page = isset( $_GET['page'] ) ? esc_attr( $_GET['page'] ) : '';

	// Bailout.
	if (
		empty( $current_page )
		|| empty( $_GET['_wp_http_referer'] )
		|| ! in_array( $current_page, $give_pages )
	) {
		return false;
	}

	/**
	 * Verify current page request.
	 *
	 * @since 1.8
	 */
	$redirect = apply_filters( "give_validate_{$current_page}", true );

	if ( $redirect ) {
		// Redirect.
		wp_redirect(
			remove_query_arg(
				array( '_wp_http_referer', '_wpnonce' ),
				wp_unslash( $_SERVER['REQUEST_URI'] )
			)
		);
		exit;
	}
}

add_action( 'admin_init', 'give_redirect_to_clean_url_admin_pages' );

/**
 * Hide License Notice Shortly.
 *
 * This code is used with AJAX call to hide license notice for a short period of time
 *
 * @since 1.8.8
 *
 * @return void
 */
function give_hide_license_notice() {

	if ( ! isset( $_POST['_give_hide_license_notices_shortly'] ) ) {
		die();
	}

	$current_user = wp_get_current_user();

    // Get notice id.
    $notice_id = sanitize_text_field( $_POST['_give_hide_license_notices_shortly'] );

    // Transient key name.
    $transient_key = "_give_hide_license_notices_shortly_{$current_user->ID}_{$notice_id}";

    if ( Give_Cache::get( $transient_key, true ) ) {
        return;
    }

    // Hide notice for 24 hours.
    Give_Cache::set( $transient_key, true, DAY_IN_SECONDS, true );

    die();

}

add_action( 'wp_ajax_give_hide_license_notice', 'give_hide_license_notice' );


/**
 * Hide Outdated PHP Notice Shortly.
 *
 * This code is used with AJAX call to hide outdated PHP notice for a short period of time
 *
 * @since 1.8.9
 *
 * @return void
 */
function give_hide_outdated_php_notice() {

	if ( ! isset( $_POST['_give_hide_outdated_php_notices_shortly'] ) ) {
		give_die();
	}

	// Transient key name.
	$transient_key = "_give_hide_outdated_php_notices_shortly";

	if ( Give_Cache::get( $transient_key, true ) ) {
		return;
	}

	// Hide notice for 24 hours.
	Give_Cache::set( $transient_key, true, DAY_IN_SECONDS, true );

	give_die();

}

add_action( 'wp_ajax_give_hide_outdated_php_notice', 'give_hide_outdated_php_notice' );

/**
 * Register admin notices.
 *
 * @since 1.8.9
 */
function _give_register_admin_notices() {
	// Bailout.
	if( ! is_admin() ) {
		return;
	}

	if (
		! give_test_ajax_works() &&
		! get_user_meta( get_current_user_id(), '_give_admin_ajax_inaccessible_dismissed', true ) &&
		current_user_can( 'manage_give_settings' )
	) {
		$notice_desc = '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may cause issues with Give.', 'give' ) . '</p>';
		$notice_desc .= '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'give' ), esc_url( 'http://docs.givewp.com/ajax-blocked' ) ) . '</p>';
		$notice_desc .= sprintf(
			'<p><a href=""></a></p>',
			add_query_arg( array( 'give_action' => 'dismiss_notices', 'give_notice' => 'admin_ajax_inaccessible' ) ),
			__( 'Dismiss Notice', 'give' )
		);

		Give()->notices->register_notice( array(
			'id'          => 'give-donation-deleted',
			'type'        => 'updated',
			'description' => $notice_desc,
			'show'        => true,
		) );
	}


	// Bailout.
	if ( empty( $_GET['give-message'] ) ) {
		return;
	}

	// Donation reports errors.
	if ( current_user_can( 'view_give_reports' ) ) {
		switch ( $_GET['give-message'] ) {
			case 'donation_deleted' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donation-deleted',
					'type'        => 'updated',
					'description' => __( 'The donation has been deleted.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'email_sent' :
				Give()->notices->register_notice( array(
					'id'          => 'give-payment-sent',
					'type'        => 'updated',
					'description' => __( 'The donation receipt has been resent.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'refreshed-reports' :
				Give()->notices->register_notice( array(
					'id'          => 'give-refreshed-reports',
					'type'        => 'updated',
					'description' => __( 'The reports cache has been cleared.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'donation-note-deleted' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donation-note-deleted',
					'type'        => 'updated',
					'description' => __( 'The donation note has been deleted.', 'give' ),
					'show'        => true,
				) );
				break;
		}
	}

	// Give settings notices and errors.
	if ( current_user_can( 'manage_give_settings' ) ) {
		switch ( $_GET['give-message'] ) {
			case 'settings-imported' :
				Give()->notices->register_notice( array(
					'id'          => 'give-settings-imported',
					'type'        => 'updated',
					'description' => __( 'The settings have been imported.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'api-key-generated' :
				Give()->notices->register_notice( array(
					'id'          => 'give-api-key-generated',
					'type'        => 'updated',
					'description' => __( 'API keys have been generated.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'api-key-exists' :
				Give()->notices->register_notice( array(
					'id'          => 'give-api-key-exists',
					'type'        => 'updated',
					'description' => __( 'The specified user already has API keys.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'api-key-regenerated' :
				Give()->notices->register_notice( array(
					'id'          => 'give-api-key-regenerated',
					'type'        => 'updated',
					'description' => __( 'API keys have been regenerated.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'api-key-revoked' :
				Give()->notices->register_notice( array(
					'id'          => 'give-api-key-revoked',
					'type'        => 'updated',
					'description' => __( 'API keys have been revoked.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'sent-test-email' :
				Give()->notices->register_notice( array(
					'id'          => 'give-sent-test-email',
					'type'        => 'updated',
					'description' => __( 'The test email has been sent.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'matched-success-failure-page':
				Give()->notices->register_notice( array(
					'id'          => 'give-matched-success-failure-page',
					'type'        => 'updated',
					'description' => __( 'You cannot set the success and failed pages to the same page', 'give' ),
					'show'        => true,
				) );
				break;
		}
	}
	// Payments errors.
	if ( current_user_can( 'edit_give_payments' ) ) {
		switch ( $_GET['give-message'] ) {
			case 'note-added' :
				Give()->notices->register_notice( array(
					'id'          => 'give-note-added',
					'type'        => 'updated',
					'description' => __( 'The donation note has been added.', 'give' ),
					'show'        => true,
				) );
				break;
			case 'payment-updated' :
				Give()->notices->register_notice( array(
					'id'          => 'give-payment-updated',
					'type'        => 'updated',
					'description' => __( 'The donation has been updated.', 'give' ),
					'show'        => true,
				) );
				break;
		}
	}

	// Donor Notices.
	if ( current_user_can( 'edit_give_payments' ) ) {
		switch ( $_GET['give-message'] ) {
			case 'donor-deleted' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-deleted',
					'type'        => 'updated',
					'description' => __( 'The donor has been deleted.', 'give' ),
					'show'        => true,
				) );
				break;

			case 'email-added' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-email-added',
					'type'        => 'updated',
					'description' => __( 'Donor email added.', 'give' ),
					'show'        => true,
				) );
				break;

			case 'email-removed' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-email-removed',
					'type'        => 'updated',
					'description' => __( 'Donor email removed.', 'give' ),
					'show'        => true,
				) );
				break;

			case 'email-remove-failed' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-email-remove-failed',
					'type'        => 'updated',
					'description' => __( 'Failed to remove donor email.', 'give' ),
					'show'        => true,
				) );
				break;

			case 'primary-email-updated' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-primary-email-updated',
					'type'        => 'updated',
					'description' => __( 'Primary email updated for donor.', 'give' ),
					'show'        => true,
				) );
				break;

			case 'primary-email-failed' :
				Give()->notices->register_notice( array(
					'id'          => 'give-donor-primary-email-failed',
					'type'        => 'updated',
					'description' => __( 'Failed to set primary email.', 'give' ),
					'show'        => true,
				) );
				break;
		}
	}
}

add_action( 'admin_notices', '_give_register_admin_notices', - 1 );
