<?php
/**
 * Email Notification
 *
 * This class handles all email notification settings.
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

/**
 * Class Give_Email_Notifications
 */
class Give_Email_Notifications {
	/**
	 * Instance.
	 *
	 * @since  2.0
	 * @access static
	 * @var
	 */
	private static $instance;

	/**
	 * Array of email notifications.
	 *
	 * @since  2.0
	 * @access private
	 * @var array
	 */
	private $emails = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  2.0
	 * @access private
	 * Give_Email_Notifications constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.0
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
	 * @since 2.0
	 */
	public function init() {
		// Load files.
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/ajax-handler.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-setting-field.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/filters.php';

		// Load email notifications.
		$this->add_emails_notifications();

		add_filter( 'give_metabox_form_data_settings', array( $this, 'add_metabox_setting_fields' ), 10, 2 );
		add_action( 'init', array( $this, 'preview_email' ) );
		add_action( 'init', array( $this, 'send_preview_email' ) );
		add_action( 'admin_init', array( $this, 'validate_settings' ) );

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			// Setup email section.
			if ( Give_Email_Notification_Util::is_show_on_emails_setting_page( $email ) ) {
				add_filter( 'give_get_sections_emails', array( $email, 'add_section' ) );
				add_filter( "give_hide_section_{$email->config['id']}_on_emails_page", array( $email, 'hide_section' ) );
			}

			// Setup email preview.
			if ( Give_Email_Notification_Util::is_email_preview_has_header( $email ) ) {
				add_action( "give_{$email->config['id']}_email_preview", array( $this, 'email_preview_header' ) );
				add_filter( "give_{$email->config['id']}_email_preview_data", array( $this, 'email_preview_data' ) );
				add_filter( "give_{$email->config['id']}_email_preview_message", array( $this, 'email_preview_message' ), 1, 2 );
			}
		}
	}


	/**
	 * Add setting to metabox.
	 *
	 * @since  2.0
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
			'id'         => 'email_notification_options',
			'title'      => __( 'Email Notifications', 'give' ),
			'icon-html'  => '<i class="fas fa-envelope"></i>',
			'fields'     => array(
				array(
					'name'    => __( 'Email Options', 'give' ),
					'id'      => '_give_email_options',
					'type'    => 'radio_inline',
					'default' => 'global',
					'options' => array(
						'global'  => __( 'Global Options', 'give' ),
						'enabled' => __( 'Customize', 'give' ),
					),
				),
				array(
					'id'      => '_give_email_template',
					'name'    => esc_html__( 'Email Template', 'give' ),
					'desc'    => esc_html__( 'Choose your template from the available registered template types.', 'give' ),
					'type'    => 'select',
					'default' => 'default',
					'options' => give_get_email_templates(),
				),
				array(
					'id'   => '_give_email_logo',
					'name' => esc_html__( 'Logo', 'give' ),
					'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'give' ),
					'type' => 'file',
				),
				array(
					'id'      => '_give_from_name',
					'name'    => esc_html__( 'From Name', 'give' ),
					'desc'    => esc_html__( 'The name which appears in the "From" field in all GiveWP donation emails.', 'give' ),
					'default' => get_bloginfo( 'name' ),
					'type'    => 'text',
				),
				array(
					'id'      => '_give_from_email',
					'name'    => esc_html__( 'From Email', 'give' ),
					'desc'    => esc_html__( 'Email address from which all GiveWP emails are sent from. This will act as the "from" and "reply-to" email address.', 'give' ),
					'default' => get_bloginfo( 'admin_email' ),
					'type'    => 'text',
				),
				array(
					'name'  => 'email_notification_docs',
					'type'  => 'docs_link',
					'url'   => 'http://docs.givewp.com/email-notification',
					'title' => __( 'Email Notification', 'give' ),
				),
			),

			/**
			 * Filter the email notification settings.
			 *
			 * @since 2.0
			 */
			'sub-fields' => apply_filters( 'give_email_notification_options_metabox_fields', array(), $post_id ),
		);

		return $settings;
	}

	/**
	 * Add email notifications
	 *
	 * @since  2.0
	 * @access private
	 */
	private function add_emails_notifications() {
		$this->emails = array(
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-donation-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-donation-receipt-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-offline-donation-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-offline-donation-instruction-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-new-donor-register-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-donor-register-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-donor-note-email.php',
			include GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-access-email.php',
		);

		/**
		 * Filter the email notifications.
		 *
		 * @since 2.0
		 */
		$this->emails = apply_filters( 'give_email_notifications', $this->emails, $this );

		// Bailout.
		if ( empty( $this->emails ) ) {
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
	 * @since  2.0
	 * @access public
	 * @return array
	 */
	public function get_email_notifications() {
		return $this->emails;
	}


	/**
	 * Displays the email preview
	 *
	 * @since  2.0
	 * @access public
	 * @return bool|null
	 */
	public function preview_email() {
		// Bailout.
		if ( ! Give_Email_Notification_Util::can_preview_email() ) {
			return false;
		}

		// Security check.
		give_validate_nonce( $_GET['_wpnonce'], 'give-preview-email' );

		// Get email type.
		$email_type = isset( $_GET['email_type'] ) ? esc_attr( $_GET['email_type'] ) : '';

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type !== $email->config['id'] ) {
				continue;
			}

			// Set form id.
			$form_id = empty( $_GET['form_id'] ) ? null : absint( $_GET['form_id'] );

			// Call setup email data to apply filter and other thing to email.
			$email->send_preview_email( false );

			// Decode message.
			$email_message = $email->preview_email_template_tags( $email->get_email_message( $form_id ) );

			// Show formatted text in browser even text/plain content type set for an email.
			Give()->emails->html = true;

			Give()->emails->form_id = $form_id;

			if ( 'text/plain' === $email->config['content_type'] ) {
				// Give()->emails->__set( 'html', false );
				Give()->emails->__set( 'template', 'none' );
			}

			if ( $email_message = Give()->emails->build_email( $email_message ) ) {

				/**
				 * Filter the email preview data
				 *
				 * @since 2.0
				 *
				 * @param array
				 */
				$email_preview_data = apply_filters( "give_{$email_type}_email_preview_data", array() );

				/**
				 * Fire the give_{$email_type}_email_preview action
				 *
				 * @since 2.0
				 */
				do_action( "give_{$email_type}_email_preview", $email );

				/**
				 * Filter the email message
				 *
				 * @since 2.0
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
	 * @since   2.0
	 * @access  public
	 *
	 * @param Give_Email_Notification $email
	 */
	public function email_preview_header( $email ) {
		/**
		 * Filter the all email preview headers.
		 *
		 * @since 2.0
		 *
		 * @param Give_Email_Notification $email
		 */
		$email_preview_header = apply_filters( 'give_email_preview_header', give_get_preview_email_header(), $email );

		echo $email_preview_header;
	}

	/**
	 * Add email preview data
	 *
	 * @since   2.0
	 * @access  public
	 *
	 * @param array $email_preview_data
	 *
	 * @return array
	 */
	public function email_preview_data( $email_preview_data ) {
		$email_preview_data['payment_id'] = absint( give_check_variable( give_clean( $_GET ), 'isset', 0, 'preview_id' ) );
		$email_preview_data['user_id']    = absint( give_check_variable( give_clean( $_GET ), 'isset', 0, 'user_id' ) );

		return $email_preview_data;
	}

	/**
	 * Replace email template tags.
	 *
	 * @since   2.0
	 * @access  public
	 *
	 * @param string $email_message
	 * @param array  $email_preview_data
	 *
	 * @return string
	 */
	public function email_preview_message( $email_message, $email_preview_data ) {
		if (
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
	 * @since  2.0
	 * @access public
	 * @return bool|null
	 */
	public function send_preview_email() {
		// Bailout.
		if ( ! Give_Email_Notification_Util::can_send_preview_email() ) {
			return false;
		}

		// Security check.
		give_validate_nonce( $_GET['_wpnonce'], 'give-send-preview-email' );

		// Get email type.
		$email_type = give_check_variable( give_clean( $_GET ), 'isset', '', 'email_type' );

		/* @var Give_Email_Notification $email */
		foreach ( $this->get_email_notifications() as $email ) {
			if ( $email_type === $email->config['id'] && Give_Email_Notification_Util::is_email_preview( $email ) ) {
				$email->send_preview_email();
				break;
			}
		}

		// Remove the test email query arg.
		wp_redirect( remove_query_arg( 'give_action' ) );
		exit;
	}


	/**
	 * Load Give_Email_Notifications
	 *
	 * @since  2.0
	 * @access public
	 */
	public function load() {
		add_action( 'init', array( $this, 'init' ), -1 );
	}


	/**
	 * Verify email setting before saving
	 *
	 * @since  2.0
	 * @access public
	 */
	public function validate_settings() {
		// Bailout.
		if (
			! Give_Admin_Settings::is_saving_settings() ||
			'emails' !== give_get_current_setting_tab() ||
			! isset( $_GET['section'] )
		) {
			return;
		}

		// Get email type.
		$email_type = give_get_current_setting_section();

		if ( ! empty( $_POST[ "{$email_type}_recipient" ] ) ) {
			$_POST[ "{$email_type}_recipient" ] = array_unique( array_filter( $_POST[ "{$email_type}_recipient" ] ) );
		}
	}
}

// Helper class.
require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/abstract-email-notification.php';
require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/class-email-notification-util.php';

// Add backward compatibility.
require_once GIVE_PLUGIN_DIR . 'includes/admin/emails/backward-compatibility.php';

/**
 * Initialize functionality.
 */
Give_Email_Notifications::get_instance()->load();
