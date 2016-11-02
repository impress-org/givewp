<?php
/**
 * Admin Notices Class.
 *
 * @package     Give
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Notices Class
 *
 * @since 1.0
 */
class Give_Notices {

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'give_dismiss_notices', array( $this, 'dismiss_notices' ) );
		add_action( 'admin_bar_menu', array( $this, 'give_admin_bar_menu' ), 1000, 1 );
	}


	/**
	 * Display admin bar when active.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 *
	 * @return bool
	 */
	public function give_admin_bar_menu( $wp_admin_bar ) {

		if ( ! give_is_test_mode() || ! current_user_can( 'view_give_reports' ) ) {
			return false;
		}

		// Add the main siteadmin menu item.
		$wp_admin_bar->add_menu( array(
			'id'     => 'give-test-notice',
			'href'   => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' ),
			'parent' => 'top-secondary',
			'title'  => esc_html__( 'Give Test Mode Active', 'give' ),
			'meta'   => array( 'class' => 'give-test-mode-active' ),
		) );

	}

	/**
	 * Show relevant notices.
	 *
	 * @since 1.0
	 */
	public function show_notices() {
		$notices = array(
			'updated' => array(),
			'error'   => array(),
		);

		if ( ! give_test_ajax_works() && ! get_user_meta( get_current_user_id(), '_give_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_give_settings' ) ) {
			echo '<div class="error">';
			echo '<p>' . esc_html__( 'Your site appears to be blocking the WordPress ajax interface. This may cause issues with Give.', 'give' ) . '</p>';
			/* translators: %s: https://givewp.com/documentation/core/troubleshooting/admin-ajax-blocked/ */
			echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'give' ), esc_url( 'https://givewp.com/documentation/core/troubleshooting/admin-ajax-blocked/' ) ) . '</p>';
			echo '<p><a href="' . add_query_arg( array(
					'give_action' => 'dismiss_notices',
					'give_notice' => 'admin_ajax_inaccessible',
			) ) . '">' . esc_html__( 'Dismiss Notice', 'give' ) . '</a></p>';
			echo '</div>';
		}

		if ( isset( $_GET['give-message'] ) ) {

			// Donation reports errors.
			if ( current_user_can( 'view_give_reports' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'donation_deleted' :
						$notices['updated']['give-donation-deleted'] = esc_attr__( 'The donation has been deleted.', 'give' );
						break;
					case 'email_sent' :
						$notices['updated']['give-payment-sent'] = esc_attr__( 'The donation receipt has been resent.', 'give' );
						break;
					case 'refreshed-reports' :
						$notices['updated']['give-refreshed-reports'] = esc_attr__( 'The reports cache has been cleared.', 'give' );
						break;
					case 'donation-note-deleted' :
						$notices['updated']['give-donation-note-deleted'] = esc_attr__( 'The donation note has been deleted.', 'give' );
						break;
				}
			}

			// Give settings notices and errors.
			if ( current_user_can( 'manage_give_settings' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'settings-imported' :
						$notices['updated']['give-settings-imported'] = esc_attr__( 'The settings have been imported.', 'give' );
						break;
					case 'api-key-generated' :
						$notices['updated']['give-api-key-generated'] = esc_attr__( 'API keys have been generated.', 'give' );
						break;
					case 'api-key-exists' :
						$notices['error']['give-api-key-exists'] = esc_attr__( 'The specified user already has API keys.', 'give' );
						break;
					case 'api-key-regenerated' :
						$notices['updated']['give-api-key-regenerated'] = esc_attr__( 'API keys have been regenerated.', 'give' );
						break;
					case 'api-key-revoked' :
						$notices['updated']['give-api-key-revoked'] = esc_attr__( 'API keys have been revoked.', 'give' );
						break;
					case 'sent-test-email' :
						$notices['updated']['give-sent-test-email'] = esc_attr__( 'The test email has been sent.', 'give' );
						break;
				}
			}
			// Payments errors.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'note-added' :
						$notices['updated']['give-note-added'] = esc_attr__( 'The donation note has been added.', 'give' );
						break;
					case 'payment-updated' :
						$notices['updated']['give-payment-updated'] = esc_attr__( 'The donation has been updated.', 'give' );
						break;
				}
			}

			// Customer Notices.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'customer-deleted' :
						$notices['updated']['give-customer-deleted'] = esc_attr__( 'The donor has been deleted.', 'give' );
						break;

					case 'email-added' :
						$notices['updated']['give-customer-email-added'] = esc_attr__( 'Donor email added', 'give' );
						break;

					case 'email-removed' :
						$notices['updated']['give-customer-email-removed'] = esc_attr__( 'Donor email removed', 'give' );
						break;

					case 'email-remove-failed' :
						$notices['error']['give-customer-email-remove-failed'] = esc_attr__( 'Failed to remove donor email', 'give' );
						break;

					case 'primary-email-updated' :
						$notices['updated']['give-customer-primary-email-updated'] = esc_attr__( 'Primary email updated for donors', 'give' );
						break;

					case 'primary-email-failed' :
						$notices['error']['give-customer-primary-email-failed'] = esc_attr__( 'Failed to set primary email', 'give' );

				}
			}
		}

		if ( count( $notices['updated'] ) > 0 ) {
			foreach ( $notices['updated'] as $notice => $message ) {
				add_settings_error( 'give-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $notices['error'] ) > 0 ) {
			foreach ( $notices['error'] as $notice => $message ) {
				add_settings_error( 'give-notices', $notice, $message, 'error' );
			}
		}

		settings_errors( 'give-notices' );

	}


	/**
	 * Admin Add-ons Notices.
	 *
	 * @since 1.0
	 * @return void
	 */
	function give_admin_addons_notices() {
		add_settings_error( 'give-notices', 'give-addons-feed-error', esc_attr__( 'There seems to be an issue with the server. Please try again in a few minutes.', 'give' ), 'error' );
		settings_errors( 'give-notices' );
	}


	/**
	 * Dismiss admin notices when Dismiss links are clicked.
	 *
	 * @since 1.0
	 * @return void
	 */
	function dismiss_notices() {
		if ( isset( $_GET['give_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_give_' . $_GET['give_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'give_action', 'give_notice' ) ) );
			exit;
		}
	}
}

new Give_Notices();
