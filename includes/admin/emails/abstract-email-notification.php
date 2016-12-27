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

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Email_Notification' ) ) :

	/**
	 * Give_Email_Notification
	 *
	 * @abstract
	 * @since       1.8
	 */
	abstract class Give_Email_Notification {

		/**
		 * @var     string $id The email's unique identifier.
		 */
		protected $id = '';

		/**
		 * @var     string $id The email's action unique identifier.
		 */
		protected $action = '';

		/**
		 * @var     string $label Name of the email.
		 * @access  protected
		 * @since   1.8
		 */
		protected $label = '';

		/**
		 * @var     string $label Name of the email.
		 * @access  protected
		 * @since   1.8
		 */
		protected $description = '';

		/**
		 * @var     Give_Emails $email Mailer.
		 * @access  protected
		 * @since   1.8
		 */
		protected $email;

		/**
		 * @var     bool $has_preview Flag to check if email notification has preview setting field.
		 * @access  protected
		 * @since   1.8
		 */
		protected $has_preview = true;

		/**
		 * @var     bool $has_preview Flag to check if email notification has preview header.
		 * @access  protected
		 * @since   1.8
		 */
		protected $has_preview_header = true;

		/**
		 * @var     bool $has_recipient_field Flag to check if email notification has recipient setting field.
		 * @access  protected
		 * @since   1.8
		 */
		protected $has_recipient_field = false;

		/**
		 * @var     bool $notification_status Flag to check if email notification enabled or not.
		 * @access  protected
		 * @since   1.8
		 */
		protected $notification_status = 'disabled';

		/**
		 * @var     bool $email_type Flag to check email type.
		 * @access  protected
		 * @since   1.8
		 */
		protected $email_type = 'text/html';

		/**
		 * Create a class instance.
		 *
		 * @param   mixed[] $objects
		 *
		 * @access  public
		 * @since   1.8
		 */
		public function __construct( $objects = array() ) {
			// Setup email class.
			$this->email = new Give_Emails();

			// Get email action.
			$this->action = give_check_variable( $this->action, 'empty', str_replace( '-', '_', $this->id ) );

			// Set email preview header status.
			$this->has_preview_header = $this->has_preview && $this->has_preview_header ? true : false;

			// Setup setting fields.
			add_filter( 'give_get_settings_emails', array( $this, 'add_setting_fields' ), 10, 2 );
		}

		/**
		 * Register email settings.
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param   array $settings
		 *
		 * @return  array
		 */
		public function add_setting_fields( $settings ) {
			if ( $this->id === give_get_current_setting_section() ) {
				$settings = $this->get_setting_fields();
			}

			return $settings;
		}


		/**
		 * Get setting field.
		 *
		 * @since  1.8
		 * @access public
		 * @return array|int
		 */
		public function get_setting_fields() {
			$setting_fields = $this->get_default_setting_fields();

			// Add extra setting field.
			if ( $extra_setting_field = $this->get_extra_setting_fields() ) {
				$setting_fields = array_merge( $setting_fields, $extra_setting_field );
			}

			// Recipient field.
			if ( $this->has_recipient_field ) {
				$setting_fields[] = $this->get_recipient_setting_field();
			}

			// Preview field.
			if ( $this->has_preview ) {
				$setting_fields[] = $this->get_preview_setting_field();
			}

			// Add section end field.
			$setting_fields[] = array(
				'id'   => "give_title_email_settings_{$this->id}",
				'type' => 'sectionend',
			);

			return $setting_fields;
		}

		/**
		 * Get extra setting field.
		 *
		 * @since  1.8
		 * @access public
		 * @return array
		 */
		public function get_extra_setting_fields() {
			return array();
		}

		/**
		 * Get recipient setting field.
		 *
		 * @since  1.8
		 * @access public
		 * @return array
		 */
		function get_recipient_setting_field() {
			return array(
				'id'      => "{$this->id}_recipient",
				'name'    => esc_html__( 'Donation Notification Emails', 'give' ),
				'desc'    => __( 'Enter the email address(es) that should receive a notification anytime a donation is made, please only enter <span class="give-underline">one email address per line</span> and <strong>not separated by commas</strong>.', 'give' ),
				'type'    => 'textarea',
				'default' => $this->get_default_recipient(),
			);
		}


		/**
		 * Get preview setting field.
		 *
		 * @since  1.8
		 * @access public
		 * @return array
		 */
		public function get_preview_setting_field() {
			return array(
				'name' => esc_html__( 'Preview Email', 'give' ),
				'desc' => esc_html__( 'Click the buttons to preview emails.', 'give' ),
				'id'   => "{$this->id}_preview_buttons",
				'type' => 'email_preview_buttons',
			);
		}

		/**
		 * Get id.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get label.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_label() {
			return $this->label;
		}

		/**
		 * Get description.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_description() {
			return $this->description;
		}

		/**
		 * Get recipient(s).
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_recipient() {
			$recipient = __( 'Donor', 'give' );

			if ( $this->has_recipient_field ) {
				$recipient = give_get_option( "{$this->id}_recipient", $this->get_default_recipient() );
			}

			return $recipient;
		}

		/**
		 * Get notification status.
		 *
		 * @since  1.8
		 * @access public
		 * @return bool
		 */
		public function get_notification_status() {
			return give_get_option( "{$this->id}_notification", $this->notification_status );
		}

		/**
		 * Get notification status.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function get_email_type() {
			return $this->email_type;
		}


		/**
		 * Get email subject.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		function get_email_subject() {
			return give_get_option( "{$this->id}_email_subject", $this->get_default_email_subject() );
		}

		/**
		 * Get email message.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		function get_email_message() {
			return give_get_option( "{$this->id}_email_message", $this->get_default_email_message() );
		}


		/**
		 * Get email message field description
		 *
		 * @since 1.8
		 * @acess public
		 * @return string
		 */
		function get_email_message_field_description() {
			$desc = esc_html__( 'Enter the subject line for email.', 'give' );

			if ( ! empty( give_get_email_tags() ) ) {
				$desc = sprintf(
					esc_html__( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted. Available template tags: %s', 'give' ),
					give_get_emails_tags_list()
				);

			}

			return $desc;
		}


		/**
		 * Get default recipient.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		function get_default_recipient() {
			return get_bloginfo( 'admin_email' );
		}

		/**
		 * Get default email subject.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		function get_default_email_subject() {
			return '';
		}

		/**
		 * Get default email message.
		 *
		 * @since  1.8
		 * @access public
		 *
		 * @param array $args Email arguments.
		 *
		 * @return string
		 */
		function get_default_email_message( $args = array() ) {
			return '';
		}

		/**
		 * Get default setting field.
		 *
		 * @since  1.8
		 * @access public
		 * @return array
		 */
		function get_default_setting_fields() {
			return array(
				array(
					'id'    => "give_title_email_settings_{$this->id}",
					'type'  => 'title',
					'title' => $this->label,
				),
				array(
					'name'    => esc_html__( 'Notification', 'give' ),
					'desc'    => esc_html__( 'Choose option if you want to send email notification or not.', 'give' ),
					'id'      => "{$this->id}_notification",
					'type'    => 'radio_inline',
					'default' => $this->notification_status,
					'options' => array(
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					),
				),
				array(
					'id'      => "{$this->id}_email_subject",
					'name'    => esc_html__( 'Email Subject', 'give' ),
					'desc'    => esc_html__( 'Enter the subject line for email.', 'give' ),
					'default' => $this->get_default_email_subject(),
					'type'    => 'text',
				),
				array(
					'id'      => "{$this->id}_email_message",
					'name'    => esc_html__( 'Email message', 'give' ),
					'desc'    => $this->get_email_message_field_description(),
					'type'    => 'wysiwyg',
					'default' => $this->get_default_email_message(),
				),
			);
		}

		/**
		 * Check email active or not.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function is_email_notification_active() {
			return give_is_setting_enabled( $this->get_notification_status() );
		}

		/**
		 * Check email preview header active or not.
		 *
		 * @since  1.8
		 * @access public
		 * @return string
		 */
		public function is_email_preview_has_header() {
			return $this->has_preview_header;
		}
	}

endif; // End class_exists check
