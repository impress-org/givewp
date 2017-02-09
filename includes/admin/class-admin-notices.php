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
	 * List of notices
	 * @var array
	 * @since 1.8
	 */
	private $notices;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'give_dismiss_notices', array( $this, 'dismiss_notices' ) );
		add_action( 'admin_bar_menu', array( $this, 'give_admin_bar_menu' ), 1000, 1 );

		$this->notices = array(
			'updated' => array(),
			'error'   => array(),
		);
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

		if ( ! give_test_ajax_works() && ! get_user_meta( get_current_user_id(), '_give_admin_ajax_inaccessible_dismissed', true ) && current_user_can( 'manage_give_settings' ) ) {
			echo '<div class="error">';
			echo '<p>' . esc_html__( 'Your site appears to be blocking the WordPress ajax interface. This may cause issues with Give.', 'give' ) . '</p>';
			/* translators: %s: http://docs.givewp.com/ajax-blocked */
			echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'give' ), esc_url( 'http://docs.givewp.com/ajax-blocked' ) ) . '</p>';
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
						$this->notices['updated']['give-donation-deleted'] = esc_attr__( 'The donation has been deleted.', 'give' );
						break;
					case 'email_sent' :
						$this->notices['updated']['give-payment-sent'] = esc_attr__( 'The donation receipt has been resent.', 'give' );
						break;
					case 'refreshed-reports' :
						$this->notices['updated']['give-refreshed-reports'] = esc_attr__( 'The reports cache has been cleared.', 'give' );
						break;
					case 'donation-note-deleted' :
						$this->notices['updated']['give-donation-note-deleted'] = esc_attr__( 'The donation note has been deleted.', 'give' );
						break;
				}
			}

			// Give settings notices and errors.
			if ( current_user_can( 'manage_give_settings' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'settings-imported' :
						$this->notices['updated']['give-settings-imported'] = esc_attr__( 'The settings have been imported.', 'give' );
						break;
					case 'api-key-generated' :
						$this->notices['updated']['give-api-key-generated'] = esc_attr__( 'API keys have been generated.', 'give' );
						break;
					case 'api-key-exists' :
						$this->notices['error']['give-api-key-exists'] = esc_attr__( 'The specified user already has API keys.', 'give' );
						break;
					case 'api-key-regenerated' :
						$this->notices['updated']['give-api-key-regenerated'] = esc_attr__( 'API keys have been regenerated.', 'give' );
						break;
					case 'api-key-revoked' :
						$this->notices['updated']['give-api-key-revoked'] = esc_attr__( 'API keys have been revoked.', 'give' );
						break;
					case 'sent-test-email' :
						$this->notices['updated']['give-sent-test-email'] = esc_attr__( 'The test email has been sent.', 'give' );
						break;
					case 'matched-success-failure-page':
						$this->notices['updated']['give-matched-success-failure-page'] = esc_html__( 'You cannot set the success and failed pages to the same page', 'give' );
				}
			}
			// Payments errors.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'note-added' :
						$this->notices['updated']['give-note-added'] = esc_attr__( 'The donation note has been added.', 'give' );
						break;
					case 'payment-updated' :
						$this->notices['updated']['give-payment-updated'] = esc_attr__( 'The donation has been updated.', 'give' );
						break;
				}
			}

			// Customer Notices.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'customer-deleted' :
						$this->notices['updated']['give-customer-deleted'] = esc_attr__( 'The donor has been deleted.', 'give' );
						break;

					case 'email-added' :
						$this->notices['updated']['give-customer-email-added'] = esc_attr__( 'Donor email added', 'give' );
						break;

					case 'email-removed' :
						$this->notices['updated']['give-customer-email-removed'] = esc_attr__( 'Donor email removed', 'give' );
						break;

					case 'email-remove-failed' :
						$this->notices['error']['give-customer-email-remove-failed'] = esc_attr__( 'Failed to remove donor email', 'give' );
						break;

					case 'primary-email-updated' :
						$this->notices['updated']['give-customer-primary-email-updated'] = esc_attr__( 'Primary email updated for donor', 'give' );
						break;

					case 'primary-email-failed' :
						$this->notices['error']['give-customer-primary-email-failed'] = esc_attr__( 'Failed to set primary email', 'give' );

				}
			}
		}

		$this->add_payment_bulk_action_notice();

		if ( count( $this->notices['updated'] ) > 0 ) {
			foreach ( $this->notices['updated'] as $notice => $message ) {
				add_settings_error( 'give-notices', $notice, $message, 'updated' );
			}
		}

		if ( count( $this->notices['error'] ) > 0 ) {
			foreach ( $this->notices['error'] as $notice => $message ) {
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


	/**
	 * Add payment bulk notice.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function add_payment_bulk_action_notice() {
		if (
			current_user_can( 'edit_give_payments' )
			&& isset( $_GET['action'] )
			&& ! empty( $_GET['action'] )
			&& isset( $_GET['payment'] )
			&& ! empty( $_GET['payment'] )
		) {
			$payment_count = isset( $_GET['payment'] ) ? count( $_GET['payment'] ) : 0;

			switch ( $_GET['action'] ) {
				case 'delete':
					if ( $payment_count ) {
						$this->notices['updated']['bulk_action_delete'] = sprintf( _n( 'Successfully deleted only one transaction.', 'Successfully deleted %d number of transactions.', $payment_count, 'give' ), $payment_count );
					}
					break;

				case 'resend-receipt':
					if ( $payment_count ) {
						$this->notices['updated']['bulk_action_resend_receipt'] = sprintf( _n( 'Successfully send email receipt to only one recipient.', 'Successfully send email receipts to %d recipients.', $payment_count, 'give' ), $payment_count );
					}
					break;
			}
		}

		return $this->notices;
	}


	/**
	 * Get give style admin notice.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return string
	 */
	public static function notice_html( $message, $type = 'updated' ) {
		ob_start();
		?>
		<div class="<?php echo $type; ?> notice">
			<p><?php echo $message; ?></p>
		</div>
		<?php

		return ob_get_clean();
	}
}

new Give_Notices();
