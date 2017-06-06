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
			echo '<p>' . __( 'Your site appears to be blocking the WordPress ajax interface. This may cause issues with Give.', 'give' ) . '</p>';
			/* translators: %s: http://docs.givewp.com/ajax-blocked */
			echo '<p>' . sprintf( __( 'Please see <a href="%s" target="_blank">this reference</a> for possible solutions.', 'give' ), esc_url( 'http://docs.givewp.com/ajax-blocked' ) ) . '</p>';
			echo '<p><a href="' . add_query_arg( array(
					'give_action' => 'dismiss_notices',
					'give_notice' => 'admin_ajax_inaccessible',
				) ) . '">' . __( 'Dismiss Notice', 'give' ) . '</a></p>';
			echo '</div>';
		}

		if ( isset( $_GET['give-message'] ) ) {

			// Donation reports errors.
			if ( current_user_can( 'view_give_reports' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'donation_deleted' :
						$this->notices['updated']['give-donation-deleted'] = __( 'The donation has been deleted.', 'give' );
						break;
					case 'email_sent' :
						$this->notices['updated']['give-payment-sent'] = __( 'The donation receipt has been resent.', 'give' );
						break;
					case 'refreshed-reports' :
						$this->notices['updated']['give-refreshed-reports'] = __( 'The reports cache has been cleared.', 'give' );
						break;
					case 'donation-note-deleted' :
						$this->notices['updated']['give-donation-note-deleted'] = __( 'The donation note has been deleted.', 'give' );
						break;
				}
			}

			// Give settings notices and errors.
			if ( current_user_can( 'manage_give_settings' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'settings-imported' :
						$this->notices['updated']['give-settings-imported'] = __( 'The settings have been imported.', 'give' );
						break;
					case 'api-key-generated' :
						$this->notices['updated']['give-api-key-generated'] = __( 'API keys have been generated.', 'give' );
						break;
					case 'api-key-exists' :
						$this->notices['error']['give-api-key-exists'] = __( 'The specified user already has API keys.', 'give' );
						break;
					case 'api-key-regenerated' :
						$this->notices['updated']['give-api-key-regenerated'] = __( 'API keys have been regenerated.', 'give' );
						break;
					case 'api-key-revoked' :
						$this->notices['updated']['give-api-key-revoked'] = __( 'API keys have been revoked.', 'give' );
						break;
					case 'sent-test-email' :
						$this->notices['updated']['give-sent-test-email'] = __( 'The test email has been sent.', 'give' );
						break;
					case 'matched-success-failure-page':
						$this->notices['updated']['give-matched-success-failure-page'] = __( 'You cannot set the success and failed pages to the same page', 'give' );
				}
			}
			// Payments errors.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'note-added' :
						$this->notices['updated']['give-note-added'] = __( 'The donation note has been added.', 'give' );
						break;
					case 'payment-updated' :
						$this->notices['updated']['give-payment-updated'] = __( 'The donation has been updated.', 'give' );
						break;
				}
			}

			// Donor Notices.
			if ( current_user_can( 'edit_give_payments' ) ) {
				switch ( $_GET['give-message'] ) {
					case 'donor-deleted' :
						$this->notices['updated']['give-donor-deleted'] = __( 'The donor has been deleted.', 'give' );
						break;

					case 'email-added' :
						$this->notices['updated']['give-donor-email-added'] = __( 'Donor email added.', 'give' );
						break;

					case 'email-removed' :
						$this->notices['updated']['give-donor-email-removed'] = __( 'Donor email removed.', 'give' );
						break;

					case 'email-remove-failed' :
						$this->notices['error']['give-donor-email-remove-failed'] = __( 'Failed to remove donor email.', 'give' );
						break;

					case 'primary-email-updated' :
						$this->notices['updated']['give-donor-primary-email-updated'] = __( 'Primary email updated for donor.', 'give' );
						break;

					case 'primary-email-failed' :
						$this->notices['error']['give-donor-primary-email-failed'] = __( 'Failed to set primary email.', 'give' );

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

		/**
		 * Error Notice: PHP Versions Update nag
         *
         * @since 1.8.9
		 */
		if ( ! Give_Cache::get( '_give_hide_outdated_php_notices_shortly', true ) || version_compare( GIVE_REQUIRED_PHP_VERSION, array( $this, 'give_get_current_php_version' ), '>') ) {

			// Check for outdated PHP Versions, if outdated show dismissable notice.
			$html = '';
			$html .= '<div class="notice notice-error is-dismissible give-outdated-php-notice">';
			$html .= '<p><strong>' . __( 'Your site could be faster and more secure with a newer PHP version.', 'give' ) . '</strong></p>';
			$html .= '<p>' . __( 'Hey, we\'ve noticed that you\'re running an outdated version of PHP. PHP is the programming language that WordPress and Give are built on. The version that is currently used for your site is no longer supported. Newer versions of PHP are both faster and more secure. In fact, your version of PHP no longer receives security updates, which is why we\'re sending you this notice.', 'give' ) . '</p>';
			$html .= '<p>' . __( 'Hosts have the ability to update your PHP version, but sometimes they don\'t dare to do that because they\'re afraid they\'ll break your site.', 'give' ) . '</p>';
			$html .= '<p><strong>' . __( 'To which version should I update?', 'give' ) . '</strong></p>';
			$html .= '<p>' . sprintf( __( 'You should update your PHP version to either 5.6 or to 7.0 or 7.1. On a normal WordPress site, switching to PHP 5.6 should never cause issues. We would however actually recommend you switch to PHP7. There are some plugins that are not ready for PHP7 though, so do some testing first. We have an article on how to test whether that\'s an option for you %1$shere%2$s. PHP7 is much faster than PHP 5.6. It\'s also the only PHP version still in active development and therefore the better option for your site in the long run.', 'give' ), '<a href="https://yoa.st/wg" target="_blank">', '</a>' ) . '</p>';
			$html .= '<p><strong>' . __( 'Can\'t update? Ask your host!', 'give' ) . '</strong></p>';
			$html .= '<p>' . sprintf( __( 'If you cannot upgrade your PHP version yourself, you can send an email to your host. If they don\'t want to upgrade your PHP version, we would suggest you switch hosts. Have a look at one of the recommended %1$sWordPress hosting partners%2$s.', 'give' ), sprintf( '<a href="%1$s" target="_blank">', esc_url( 'https://wordpress.org/hosting/' ) ), '</a>' ) . '</p>';
			$html .= '</div>';

			echo apply_filters( 'give_outdated_php_version_notice_message', $html);

		}

	}


	/**
	 * Admin Add-ons Notices.
	 *
	 * @since 1.0
	 * @return void
	 */
	function give_admin_addons_notices() {
		add_settings_error( 'give-notices', 'give-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'give' ), 'error' );
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

	/**
	 * Retrieve Current PHP Version
	 *
	 * @since 1.8.9
	 *
	 * @return float
	 */
	function give_get_current_php_version() {
		return phpversion();
	}
}

new Give_Notices();
