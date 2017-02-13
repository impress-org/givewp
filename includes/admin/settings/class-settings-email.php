<?php
/**
 * Give Settings Page/Tab
 *
 * @package     Give
 * @subpackage  Classes/Give_Settings_Email
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Give_Settings_Email' ) ) :

	/**
	 * Give_Settings_Email.
	 *
	 * @sine 1.8
	 */
	class Give_Settings_Email extends Give_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id    = 'emails';
			$this->label = esc_html__( 'Emails', 'give' );

			$this->default_tab = 'email-settings';

			parent::__construct();
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings = array();
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'email-settings' :
					$settings = array(
						// Section 1: Email
						array(
							'id'   => 'give_title_email_settings_1',
							'type' => 'title'
						),
						array(
							'id'      => 'email_template',
							'name'    => esc_html__( 'Email Template', 'give' ),
							'desc'    => esc_html__( 'Choose your template from the available registered template types.', 'give' ),
							'type'    => 'select',
							'options' => give_get_email_templates()
						),
						array(
							'id'   => 'email_logo',
							'name' => esc_html__( 'Logo', 'give' ),
							'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'give' ),
							'type' => 'file'
						),
						array(
							'id'      => 'from_name',
							'name'    => esc_html__( 'From Name', 'give' ),
							'desc'    => esc_html__( 'The name which appears in the "From" field in all Give donation emails.', 'give' ),
							'default' => get_bloginfo( 'name' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'from_email',
							'name'    => esc_html__( 'From Email', 'give' ),
							'desc'    => esc_html__( 'Email address from which all Give emails are sent from. This will act as the "from" and "reply-to" email address.', 'give' ),
							'default' => get_bloginfo( 'admin_email' ),
							'type'    => 'text'
						),
                        array(
                            'name'  => esc_html__( 'Email Settings Docs Link', 'give' ),
                            'id'    => 'email_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-emails' ),
                            'title' => __( 'Email Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'id'   => 'give_title_email_settings_1',
							'type' => 'sectionend'
						)
					);
					break;

				case 'donation-receipt':
					$settings = array(
						// Section 2: donation.
						array(
							'id'   => 'give_title_email_settings_2',
							'type' => 'title'
						),
						array(
							'id'      => 'donation_subject',
							'name'    => esc_html__( 'Donation Email Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the donation receipt email.', 'give' ),
							'default' => esc_attr__( 'Donation Receipt', 'give' ),
							'type'    => 'text'
						),
						array(
							'id'      => 'donation_receipt',
							'name'    => esc_html__( 'Donation Receipt', 'give' ),
							'desc'    => sprintf(
							/* translators: %s: emails tags list */
								__( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted.<br /><strong>Available template tags:</strong> %s', 'give' ),
								'<br/>'.give_get_emails_tags_list()
							),
							'type'    => 'wysiwyg',
							'default' => give_get_default_donation_receipt_email()
						),
                        array(
                            'name'  => esc_html__( 'Donation Receipt Settings Docs Link', 'give' ),
                            'id'    => 'donation_receipt_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-donation-receipt' ),
                            'title' => __( 'Donation Receipt Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'id'   => 'give_title_email_settings_2',
							'type' => 'sectionend'
						)
					);
					break;

				case 'new-donation-notification':
					$settings = array(
						// Section 3: New Donation.
						array(
							'id'   => 'give_title_email_settings_3',
							'type' => 'title'
						),
						array(
							'id'      => 'admin_notices',
							'name'    => esc_html__( 'Admin Notifications', 'give' ),
							'desc'    => esc_html__( 'Enable/Disable all admin notifications from Give completely.', 'give' ),
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => __( 'Enabled', 'give' ),
								'disabled' => __( 'Disabled', 'give' ),
							)
						),
						array(
							'id'      => 'donation_notification_subject',
							'name'    => esc_html__( 'Donation Notification Subject', 'give' ),
							'desc'    => esc_html__( 'Enter the subject line for the admin donation notification email.', 'give' ),
							'type'    => 'text',
							'default' => esc_attr__( 'New Donation - #{payment_id}', 'give' )
						),
						array(
							'id'      => 'donation_notification',
							'name'    => esc_html__( 'Donation Notification', 'give' ),
							'desc'    => sprintf(
							/* translators: %s: emails tags list */
								__( 'Enter the content of the email that is sent to notify an admin of a new donation. HTML is accepted. <br /><strong>Available template tags:</strong> %s', 'give' ),
								'<br/>'.give_get_emails_tags_list()
							),
							'type'    => 'wysiwyg',
							'default' => give_get_default_donation_notification_email()
						),
						array(
							'id'      => 'admin_notice_emails',
							'name'    => esc_html__( 'Donation Notification Emails', 'give' ),
							'desc'    => __( 'Enter the email address(es) that should receive a notification anytime a donation is made, please only enter <span class="give-underline">one email address per line</span> and <strong>not separated by commas</strong>.', 'give' ),
							'type'    => 'textarea',
							'default' => get_bloginfo( 'admin_email' )
						),
                        array(
                            'name'  => esc_html__( 'Donation Notification Settings Docs Link', 'give' ),
                            'id'    => 'donation_notification_settings_docs_link',
                            'url'   => esc_url( 'http://docs.givewp.com/settings-donation-notification' ),
                            'title' => __( 'Donation Notification Settings', 'give' ),
                            'type'  => 'give_docs_link',
                        ),
						array(
							'id'   => 'give_title_email_settings_3',
							'type' => 'sectionend'
						)
					);
					break;
			}

			/**
			 * Filter the emails settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_emails', $settings );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 * @param  array $settings
			 */
			$settings = apply_filters( 'give_get_settings_' . $this->id, $settings );

			// Output.
			return $settings;
		}

		/**
		 * Get sections.
		 *
		 * @since 1.8
		 * @return array
		 */
		public function get_sections() {
			$sections = array(
				'email-settings'            => esc_html__( 'Email Settings', 'give' ),
				'donation-receipt'          => esc_html__( 'Donation Receipt', 'give' ),
				'new-donation-notification' => esc_html__( 'New Donation Notification', 'give' )
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}
	}

endif;

return new Give_Settings_Email();
