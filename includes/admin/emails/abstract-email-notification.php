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
		 * @var     bool $has_recipient_field Flag to check if email notification has recipient setting field.
		 * @access  protected
		 * @since   1.8
		 */
		protected $has_recipient_field = false;

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

			// Setup setting fields.
			add_filter( 'give_get_settings_emails', array( $this, 'add_setting_fields' ), 10, 2 );
		}

		/**
		 * Returns all email tags.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.8
		 */
		public function get_email_tags() {}

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

			$setting_fields = array(
				array(
					'id'   => 'give_title_email_settings_2',
					'type' => 'title',
				),
				array(
					'name' => esc_html__( 'Preview Email', 'give' ),
					'desc' => esc_html__( 'Click the buttons to preview emails.', 'give' ),
					'id'   => 'give_email_preview_buttons',
					'type' => 'email_preview_buttons',
				),
				array(
					'id'      => 'admin_notice_emails',
					'name'    => esc_html__( 'Donation Notification Emails', 'give' ),
					'desc'    => __( 'Enter the email address(es) that should receive a notification anytime a donation is made, please only enter <span class="give-underline">one email address per line</span> and <strong>not separated by commas</strong>.', 'give' ),
					'type'    => 'textarea',
					'default' => get_bloginfo( 'admin_email' ),
				),
				array(
					'id'      => 'donation_subject',
					'name'    => esc_html__( 'Donation Email Subject', 'give' ),
					'desc'    => esc_html__( 'Enter the subject line for the donation receipt email.', 'give' ),
					'default' => esc_attr__( 'Donation Receipt', 'give' ),
					'type'    => 'text',
				),
				array(
					'id'      => 'donation_receipt',
					'name'    => esc_html__( 'Donation Receipt', 'give' ),
					'desc'    => sprintf(
					/* translators: %s: emails tags list */
						esc_html__( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted. Available template tags: %s', 'give' ),
						'<br/>' . give_get_emails_tags_list()
					),
					'type'    => 'wysiwyg',
					'default' => give_get_default_donation_receipt_email(),
				),
				array(
					'id'   => 'give_title_email_settings_2',
					'type' => 'sectionend',
				),
			);

			return $setting_fields;
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
	}

endif; // End class_exists check