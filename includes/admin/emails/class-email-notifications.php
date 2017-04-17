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
 * @since       1.9
 */

/**
 * Class Give_Email_Notifications
 */
class Give_Email_Notifications {
	/**
	 * Instance.
	 *
	 * @since  1.9
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Array of email notifications.
	 *
	 * @since  1.9
	 * @access private
	 * @var array
	 */
	private $emails = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  1.9
	 * @access private
	 * Give_Payumoney_API constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.9
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
	 * @since 1.9
	 */
	public function init() {
		// Load ajax handler.
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/ajax-handler.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-setting-field.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/filters.php';

		// Load email notifications.
		$this->add_emails_notifications();

		add_filter( 'give_metabox_form_data_settings', array( $this, 'add_metabox_setting_fields' ), 10, 2 );
		add_action( 'init', array( $this, 'preview_email' ) );
		add_action( 'init', array( $this, 'send_preview_email' ) );

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			// Add section.
			add_filter( 'give_get_sections_emails', array( $email, 'add_section' ) );

			if ( ! $email->is_email_preview_has_header() ) {
				continue;
			}

			add_action( "give_{$email->get_id()}_email_preview", array( $this, 'email_preview_header' ) );
			add_filter( "give_{$email->get_id()}_email_preview_data", array( $this, 'email_preview_data' ) );
			add_filter( "give_{$email->get_id()}_email_preview_message", array( $this, 'email_preview_message', ), 1, 2 );
		}
	}


	/**
	 * Add setting to metabox.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param array $settings
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public function add_metabox_setting_fields( $settings, $post_id ) {
		$emails = $this->get_email_notifications();

		// Bailout.
		if ( empty( $emails ) ) {
			return $settings;
		}

		// Email notification setting.
		$settings['email_notification_options'] = array(
			'id'         => "email_notification_options",
			'title'      => __( 'Email Notification', 'give' ),

			/**
			 * Filter the email notification settings.
			 *
			 * @since 1.9
			 */
			'sub-fields' => apply_filters( 'give_email_notification_options_metabox_fields', array(), $post_id ),
		);

		return $settings;
	}

	/**
	 * Add email notifications
	 *
	 * @since  1.9
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
		 * @since 1.9
		 */
		$this->emails = apply_filters( 'give_email_notifications', $this->emails, $this );

		// Bailout.
		if( empty( $this->emails ) ) {
			return;
		}
		
		// Initiate email notifications.
		foreach ( $this->emails as $email ) {
			$email->init();
		}
	}


	/**
	 * Get list of email notifications.
	 *
	 * @since  1.9
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
		 * @since 1.9
		 */
		return apply_filters( 'give_email_notification_setting_columns', array(
			'status'     => '',
			'name'       => __( 'Email', 'give' ),
			'email_type' => __( 'Content Type', 'give' ),
			'recipient'  => __( 'Recipient(s)', 'give' ),
			'setting'    => __( 'Edit Email', 'give' ),
		) );
	}


	/**
	 * Get name column.
	 *
	 * @since  1.9
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function get_name_column( Give_Email_Notification $email ) {
		$edit_url = esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->get_id() ) );
		?>
		<td class="give-email-notification-settings-table-name">
			<a class="row-title" href="<?php echo $edit_url; ?>"><?php echo $email->get_label(); ?></a>
			<?php if ( $desc = $email->get_description() ) : ?>
				<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php echo esc_attr( $desc ); ?>"></span>
			<?php endif; ?>
			<?php $this->print_row_actions( $email ); ?>
		</td>
		<?php
	}


	/**
	 * Print row actions.
	 *
	 * @since  1.9
	 * @access private
	 *
	 * @param Give_Email_Notification $email
	 */
	private function print_row_actions( $email ) {
		$edit_url    = esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->get_id() ) );
		$row_actions = apply_filters(
			'give_email_notification_row_actions',
			array( 'edit' => "<a href=\"{$edit_url}\">" . __( 'Edit', 'give' ) . "</a>" ),
			$email
		);
		?>
		<?php if ( ! empty( $row_actions ) ) : $index = 0; ?>
			<div class="row-actions">
				<?php foreach ( $row_actions as $action => $link ) : ?>
					<?php $sep = 0 < $index ? '&nbsp;|&nbsp;' : ''; ?>
					<span class="<?php echo $action; ?>">
						<?php echo $sep . $link; ?>
					</span>
					<?php $index ++; endforeach; ?>
			</div>
			<?php
		endif;
	}

	/**
	 * Get recipient column.
	 *
	 * @since  1.9
	 * @access public
	 *
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
	 * @since  1.9
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function get_status_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-status">
			<?php
			$notification_status       = $email->get_notification_status();
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
	 * @since  1.9
	 * @access public
	 *
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
	 * @since  1.9
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function get_setting_column( Give_Email_Notification $email ) {
		?>
		<td class="give-email-notification-settings-table-actions">
			<a class="button button-small" data-tooltip="<?php echo __( 'Edit', 'give' ); ?> <?php echo $email->get_label(); ?>" href="<?php echo esc_url( admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails&section=' . $email->get_id() ) ); ?>"><span class="dashicons dashicons-admin-generic"></span></a>
		</td>
		<?php
	}

	/**
	 * Render column.
	 *
	 * @since  1.9
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
	 * @since  1.9
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
	 * Check if admin preview email or not
	 *
	 * @since  1.9
	 * @access public
	 * @return bool   $is_preview
	 */
	public function is_send_preview_email() {
		$is_preview = false;

		if (
			current_user_can( 'manage_give_settings' )
			&& ! empty( $_GET['give_action'] )
			&& 'send_preview_email' === $_GET['give_action']
		) {
			$is_preview = true;
		}

		return $is_preview;
	}

	/**
	 * Displays the email preview
	 *
	 * @since  1.9
	 * @access public
	 * @return bool|null
	 */
	public function preview_email() {
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

			// Call setup email data to apply filter and other thing to email.
			$email->setup_email_data();

			// Decode message.
			$email_message = $email->preview_email_template_tags( $email->get_email_message() );

			if ( $email_message = Give()->emails->build_email( $email_message ) ) {

				/**
				 * Filter the email preview data
				 *
				 * @since 1.9
				 *
				 * @param array
				 */
				$email_preview_data = apply_filters( "give_{$email_type}_email_preview_data", array() );

				/**
				 * Fire the give_{$email_type}_email_preview action
				 *
				 * @since 1.9
				 */
				do_action( "give_{$email_type}_email_preview", $email );

				/**
				 * Filter the email message
				 *
				 * @since 1.9
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
	 * @since   1.9
	 * @access  public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function email_preview_header( $email ) {
		/**
		 * Filter the all email preview headers.
		 *
		 * @since 1.9
		 *
		 * @param Give_Email_Notification $email
		 */
		$email_preview_header = apply_filters( 'give_email_preview_header', give_get_preview_email_header(), $email );

		/**
		 * Filter the specific email preview header.
		 *
		 * @since 1.9
		 *
		 * @param Give_Email_Notification $email
		 */
		$email_preview_header = apply_filters( "give_email_preview_{$email->get_id()}_header", $email_preview_header, $email );

		echo $email_preview_header;
	}

	/**
	 * Add email preview data
	 *
	 * @since   1.9
	 * @access  public
	 *
	 * @param array $email_preview_data
	 *
	 * @return array
	 */
	public function email_preview_data( $email_preview_data ) {
		$email_preview_data['payment_id'] = absint( give_check_variable( give_clean( $_GET ), 'isset', 0, 'preview_id' ) );
		$email_preview_data['user_id']   = absint( give_check_variable( give_clean( $_GET ), 'isset', 0, 'user_id' ) );

		return $email_preview_data;
	}

	/**
	 * Replace email template tags.
	 *
	 * @since   1.9
	 * @access  public
	 *
	 * @param string $email_message
	 * @param array  $email_preview_data
	 *
	 * @return string
	 */
	public function email_preview_message( $email_message, $email_preview_data ) {
		if(
			! empty( $email_preview_data['payment_id'] )
			|| ! empty( $email_preview_data['user_id'] )
		) {
			$email_message = give_do_email_tags( $email_message, $email_preview_data );
		}

		return $email_message;
	}

	/**
	 * Displays the email preview
	 *
	 * @since  1.9
	 * @access public
	 * @return bool|null
	 */
	public function send_preview_email() {
		// Bailout.
		if ( ! $this->is_send_preview_email() ) {
			return false;
		}

		// Security check.
		give_validate_nonce( $_GET['_wpnonce'], 'give-send-preview-email' );


		// Get email type.
		$email_type = give_check_variable( give_clean( $_GET ), 'isset', '', 'email_type' );

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type === $email->get_id() && $email->is_email_preview() ) {
				$email->send_preview_email();
				break;
			}
		}
	}
}


/**
 * Initialize functionality.
 */
Give_Email_Notifications::get_instance()->init();
