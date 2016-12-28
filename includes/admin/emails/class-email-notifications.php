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

		// Load ajax handler.
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/ajax-handler.php';

		add_action( 'init', array( $this, 'preview_email' ) );

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( ! $email->is_email_preview_has_header() ) {
				return false;
			}

			add_action( "give_{$email->get_id()}_email_preview", array( $this, 'email_preview_header' ) );
			add_filter( "give_{$email->get_id()}_email_preview_data", array( $this, 'email_preview_data' ) );
			add_filter( "give_{$email->get_id()}_email_preview_message", array( $this, 'email_preview_message' ), 1, 2 );
		}
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
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-offline-donation-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-offline-donation-instruction-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-donor-register-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-donor-register-email.php',
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


	public function get_columns() {
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
			'setting'    => '',
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
			<?php
			if ( $email->get_recipient_group_name() ) {
				echo $email->get_recipient_group_name();
			} else {
				$recipients = $email->get_recipient();
				if ( is_array( $recipients ) ) {
					$recipients = implode( '<br>', $recipients );
				}
				
				echo $recipients;
			}
			?>
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
			echo "<span class=\"give-email-notification-{$notification_status} dashicons {$notification_status_class}\" data-status=\"{$notification_status}\" data-id=\"{$email->get_id()}\"></span><span class=\"spinner\"></span>";
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
	 * @since  1.8
	 * @access public
	 *
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
		if ( method_exists( $this, "get_{$column_name}_column" ) ) {
			$this->{"get_{$column_name}_column"}( $email );
		} else {
			do_action( "give_email_notification_setting_column_$column_name", $email );
		}
	}

	/**
	 * Check if admin preview email or not
	 *
	 * @since  1.8
	 * @access public
	 * @return bool   $is_preview
	 */
	public function is_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_give_settings' )
			&& ! empty( $_GET['give_action'] )
			&& 'preview_email' === $_GET['give_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}

	/**
	 * Displays the email preview
	 *
	 * @since  1.8
	 * @access public
	 * @return bool
	 */
	function preview_email() {
		// Bailout.
		if ( ! $this->is_preview_email() ) {
			return false;
		}

		// Security check.
		give_validate_nonce( $_GET['_wpnonce'], 'give-preview-email' );


		// Get email type.
		$email_type = isset( $_GET['email_type'] ) ? esc_attr( $_GET['email_type'] ) : '';

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type !== $email->get_id() ) {
				continue;
			}

			if ( $email_message = Give()->emails->build_email( $email->get_email_message() ) ) {

				/**
				 * Filter the email preview data
				 *
				 * @since 1.8
				 *
				 * @param array
				 */
				$email_preview_data = apply_filters( "give_{$email_type}_email_preview_data", array() );

				/**
				 * Fire the give_{$email_type}_email_preview action
				 *
				 * @since 1.8
				 */
				do_action( "give_{$email_type}_email_preview", $email );

				/**
				 * Filter the email message
				 *
				 * @since 1.8
				 *
				 * @param string                  $email_message
				 * @param array                   $email_preview_data
				 * @param Give_Email_Notification $email
				 */
				echo apply_filters( "give_{$email_type}_email_preview_message", $email_message, $email_preview_data, $email );

				exit();
			}
		}// End foreach().
	}


	/**
	 * Add header to donation receipt email preview
	 *
	 * @since   1.8
	 * @access  public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function email_preview_header( $email ) {
		/**
		 * Filter the all email preview headers.
		 *
		 * @since 1.8
		 *
		 * @param Give_Email_Notification $email
		 */
		$email_preview_header = apply_filters( 'give_email_preview_header', give_get_preview_email_header(), $email );

		/**
		 * Filter the specific email preview header.
		 *
		 * @since 1.8
		 *
		 * @param Give_Email_Notification $email
		 */
		$email_preview_header = apply_filters( "give_email_preview_{$email->get_id()}_header", $email_preview_header, $email );

		echo $email_preview_header;
	}

	/**
	 * Add email preview data
	 *
	 * @since   1.8
	 * @access  public
	 *
	 * @param array $email_preview_data
	 *
	 * @return array
	 */
	public function email_preview_data( $email_preview_data ) {
		$email_preview_data['payment_id'] = absint( give_check_variable( give_clean( $_GET ), 'isset', 0, 'preview_id' ) );

		return $email_preview_data;
	}

	/**
	 * Replace email template tags.
	 *
	 * @since   1.8
	 * @access  public
	 *
	 * @param string $email_message
	 * @param array  $email_preview_data
	 *
	 * @return string
	 */
	public function email_preview_message( $email_message, $email_preview_data ) {
		if ( $email_preview_data['payment_id'] ) {
			$email_message = give_do_email_tags( $email_message, $email_preview_data['payment_id'] );
		}

		return $email_message;
	}
}


/**
 * Initialize functionality.
 */
Give_Email_Notifications::get_instance()->init();
