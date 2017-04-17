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

			add_action( 'give_admin_field_email_notification', array( $this, 'email_notification_setting' ) );
		}

		/**
		 * Get settings array.
		 *
		 * @since  1.8
		 * @return array
		 */
		public function get_settings() {
			$settings        = array();
			$current_section = give_get_current_setting_section();

			switch ( $current_section ) {
				case 'email-settings' :
					$settings = array(

						// Section 1: Email Notification Listing.
						array(
							'title'      => __( 'Email Notifications', 'give' ),
							'desc'       => __( 'Email notifications sent from Give are listed below. Click on an email to configure it.', 'give' ),
							'type'       => 'title',
							'id'         => 'give_email_notification_settings',
							'table_html' => false,
						),
						array(
							'type' => 'email_notification',
						),
						array(
							'type' => 'sectionend',
							'id'   => 'give_email_notification_settings',
						),

						// Section 2: Email Sender Setting
						array(
							'title' => __( 'Email Sender Options', 'give' ),
							'id'    => 'give_title_email_settings_1',
							'type'  => 'title',
						),
						array(
							'id'      => 'email_template',
							'name'    => esc_html__( 'Email Template', 'give' ),
							'desc'    => esc_html__( 'Choose your template from the available registered template types.', 'give' ),
							'type'    => 'select',
							'options' => give_get_email_templates(),
						),
						array(
							'id'   => 'email_logo',
							'name' => esc_html__( 'Logo', 'give' ),
							'desc' => esc_html__( 'Upload or choose a logo to be displayed at the top of the donation receipt emails. Displayed on HTML emails only.', 'give' ),
							'type' => 'file',
						),
						array(
							'id'      => 'from_name',
							'name'    => esc_html__( 'From Name', 'give' ),
							'desc'    => esc_html__( 'The name which appears in the "From" field in all Give donation emails.', 'give' ),
							'default' => get_bloginfo( 'name' ),
							'type'    => 'text',
						),
						array(
							'id'      => 'from_email',
							'name'    => esc_html__( 'From Email', 'give' ),
							'desc'    => esc_html__( 'Email address from which all Give emails are sent from. This will act as the "from" and "reply-to" email address.', 'give' ),
							'default' => get_bloginfo( 'admin_email' ),
							'type'    => 'text',
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
							'type' => 'sectionend',
						),
					);
					break;
			}// End switch().

			/**
			 * Filter the emails settings.
			 * Backward compatibility: Please do not use this filter. This filter is deprecated in 1.8
			 */
			$settings = apply_filters( 'give_settings_emails', $settings );

			/**
			 * Filter the settings.
			 *
			 * @since  1.8
			 *
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
				'email-settings' => esc_html__( 'Email Settings', 'give' ),
			);

			return apply_filters( 'give_get_sections_' . $this->id, $sections );
		}


		public function email_notification_setting() {
			/* @var Give_Email_Notifications $email_notifications */
			$email_notifications = Give_Email_Notifications::get_instance();
			$emails              = $email_notifications->get_email_notifications();

			// @todo: fix responsiveness of email notification table.
			?>
			<table class="give_emails_notification wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<?php
						$columns = Give_Email_Notifications::get_instance()->get_columns();
						foreach ( $columns as $key => $column ) {
							echo '<th class="give-email-notification-settings-column-' . esc_attr( $key ) . '">' . esc_html( $column ) . '</th>';
						}
						?>
				</thead>
				<tbody>
					<?php
					/* @var Give_Email_Notification $email */
					foreach ( $emails as $email ) :
						echo '<tr>';

						foreach ( $columns as $column_name => $column ) {
							$email_notifications->render_column( $email, $column_name );
						}

						echo '</tr>';
					endforeach;;
					?>
				</tbody>
			</table>
			<?php
		}
	}

endif;

return new Give_Settings_Email();
