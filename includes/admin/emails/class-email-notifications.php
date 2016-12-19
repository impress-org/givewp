<?php
/**
 * Email Notification
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8
 */

/**
 * Class Give_Email_Notifications
 */
class Give_Email_Notifications {
	/**
	 * Instance.
	 *
	 * @since  1.8
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Array of email notifications.
	 *
	 * @since  1.8
	 * @access private
	 * @var array
	 */
	private $emails = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8
	 * @access private
	 * Give_Payumoney_API constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.8
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup dependencies
	 *
	 * @since 1.8
	 */
	public function init() {
		// Load email notifications.
		$this->add_emails_notifications();
	}

	/**
	 * Add email notifications
	 *
	 * @since  1.8
	 * @access private
	 */
	private function add_emails_notifications() {
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/abstract-email-notification.php';

		$this->emails = array(
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-donation-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-donation-receipt-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-offline-donation.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-offline-donation-instruction-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-donor-register-email.php',
		);

		/**
		 * Filter the email notifications.
		 *
		 * @since 1.8
		 */
		$this->emails = apply_filters( 'give_email_notifications', $this->emails );
	}


	/**
	 * Get list of email notifications.
	 *
	 * @since  1.8
	 * @access public
	 * @return array
	 */
	public function get_email_notifications() {
		return $this->emails;
	}


	public function get_columns(){
		/**
		 * Filter the table columns
		 *
		 * @since 1.8
		 */
		return apply_filters( 'give_email_notification_setting_columns', array(
			'status'     => '',
			'name'       => __( 'Email', 'give' ),
			'email_type' => __( 'Content Type', 'give' ),
			'recipient'  => __( 'Recipient(s)', 'give' ),
			'setting'    => ''
		) );
	}


	/**
	 * Get name column.
	 *
	 * @since 1.8
	 * @access public
	 * @param Give_Email_Notification $email
	 */
	public function get_name_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-settings-table-name">
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->get_id() ) ); ?>"><?php echo $email->get_label(); ?></a>
			<?php if ( $desc = $email->get_description() ) : ?>
				<br>
				<span class="give-field-description">
					<?php echo $desc; ?>
				</span>
			<?php endif; ?>
		</td>
		<?php
	}

	/**
	 * Get recipient column.
	 *
	 * @since 1.8
	 * @access public
	 * @param Give_Email_Notification $email
	 */
	public function get_recipient_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-settings-table-recipient">
			<?php echo $email->get_recipient(); ?>
		</td>
		<?php
	}

	/**
	 * Get status column.
	 *
	 * @since 1.8
	 * @access public
	 * @param Give_Email_Notification $email
	 */
	public function get_status_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-status">
			<?php
			$notification_status = $email->get_notification_status();
			$notification_status_class = $email->is_email_notification_active()
				? 'dashicons-yes'
				: 'dashicons-no-alt';
			echo "<span class=\"give-email-notification-{$notification_status} dashicons {$notification_status_class}\"></span>";
			?>
		</td>
		<?php
	}

	/**
	 * Get email_type column.
	 *
	 * @since 1.8
	 * @access public
	 * @param Give_Email_Notification $email
	 */
	public function get_email_type_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-settings-table-email_type">
			<?php echo $email->get_email_type(); ?>
		</td>
		<?php
	}

	/**
	 * Get setting column.
	 *
	 * @since 1.8
	 * @access public
	 * @param Give_Email_Notification $email
	 */
	public function get_setting_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-settings-table-actions">
			<a class="dashicons dashicons-admin-generic alignright" href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->get_id() ) ); ?>"></a>
		</td>
		<?php
	}

	/**
	 * Render column.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 * @param string                  $column_name
	 */
	public function render_column( Give_Email_Notification $email, $column_name ) {
		if( method_exists( $this, "get_{$column_name}_column" ) ) {
			$this->{"get_{$column_name}_column"}( $email );
		} else {
			do_action( "give_email_notification_setting_column_$column_name", $email );
		}
	}
}


/**
 * Initialize functionality.
 */
Give_Email_Notifications::get_instance()->init();