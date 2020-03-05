<?php
/**
 * New Donor Register Email
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_New_Donor_Register_Email' ) ) :

	/**
	 * Give_New_Donor_Register_Email
	 *
	 * @abstract
	 * @since       2.0
	 */
	class Give_New_Donor_Register_Email extends Give_Email_Notification {

		/**
		 * Create a class instance.
		 *
		 * @access  public
		 * @since   2.0
		 */
		public function init() {
			$this->load(
				array(
					'id'                    => 'new-donor-register',
					'label'                 => __( 'New User Registration', 'give' ),
					'description'           => __( 'Sent to designated recipient(s) when a new user registers on the site via a donation form.', 'give' ),
					'has_recipient_field'   => true,
					'notification_status'   => 'enabled',
					'has_preview_header'    => true,
					'email_tag_context'     => array( 'donor', 'general' ),
					'form_metabox_setting'  => false,
					'default_email_subject' => sprintf(
						/* translators: %s: site name */
						esc_attr__( '[%s] New User Registration', 'give' ),
						get_bloginfo( 'name' )
					),
					'default_email_message' => $this->get_default_email_message(),
					'default_email_header'  => __( 'New User Registration', 'give' ),
				)
			);

			// Setup action hook.
			add_action(
				"give_{$this->config['id']}_email_notification",
				array( $this, 'setup_email_notification' ),
				10,
				2
			);

			add_filter(
				'give_email_preview_header',
				array( $this, 'email_preview_header' ),
				10,
				2
			);
		}

		/**
		 * Get default email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @return string
		 */
		function get_default_email_message() {
			$message  = esc_attr__( 'New user registration on your site {sitename}:', 'give' ) . "\r\n\r\n";
			$message .= esc_attr__( 'Username: {username}', 'give' ) . "\r\n\r\n";
			$message .= esc_attr__( 'Email: {user_email}', 'give' ) . "\r\n";

			/**
			 * Filter the default email message
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$message,
				$this
			);
		}

		/**
		 * Send new donor register notifications.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int   $user_id   User ID.
		 * @param array $user_data User Information.
		 *
		 * @return string
		 */
		public function setup_email_notification( $user_id, $user_data ) {
			if ( ! is_int( $user_id ) || ! $user_id ) {
				wp_die(
					esc_html__( 'Cheatin&#8217; uh?', 'give' ),
					esc_html__( 'Error', 'give' ),
					array(
						'response' => 400,
					)
				);
			}

			$this->setup_email_data();

			$this->send_email_notification(
				array(
					'user_id' => $user_id,
				)
			);
		}


		/**
		 * email preview header.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param string                        $email_preview_header
		 * @param Give_New_Donor_Register_Email $email
		 * @return string
		 */
		public function email_preview_header( $email_preview_header, $email ) {
			// Bailout.
			if ( $this->config['id'] !== $email->config['id'] ) {
				return $email_preview_header;
			}

			// Payment receipt switcher
			$user_id = give_check_variable( give_clean( $_GET ), 'isset', 0, 'user_id' );

			// Get payments.
			$donors  = new Give_API();
			$donors  = give_check_variable( $donors->get_donors(), 'empty', array(), 'donors' );
			$options = array();

			// Default option.
			$options[0] = esc_html__( 'No donor(s) found.', 'give' );

			// Provide nice human readable options.
			if ( $donors ) {
				$options[0] = esc_html__( '- Select a donor -', 'give' );
				foreach ( $donors as $donor ) {
					// Exclude customers for which wp user not exist.
					if ( ! $donor['info']['user_id'] ) {
						continue;
					}
					$options[ $donor['info']['user_id'] ] = esc_html( '#' . $donor['info']['donor_id'] . ' - ' . $donor['info']['email'] );
				}
			}

			$request_url_data = wp_parse_url( $_SERVER['REQUEST_URI'] );
			$query            = $request_url_data['query'];

			// Remove user id query param if set from request url.
			$query = remove_query_arg( array( 'user_id' ), $query );

			$request_url = home_url( '/?' . str_replace( '', '', $query ) );
			?>
			<script type="text/javascript">
				function change_preview() {
					var transactions = document.getElementById("give_preview_email_user_id");
					var selected_trans = transactions.options[transactions.selectedIndex];
					if (selected_trans) {
						var url_string = "<?php echo $request_url; ?>&user_id=" + selected_trans.value;
						window.location = url_string;
					}
				}
			</script>

			<style type="text/css">
				.give_preview_email_user_id_main {
					margin: 0;
					padding: 10px 0;
					width: 100%;
					background-color: #FFF;
					border-bottom: 1px solid #eee;
					text-align: center;
				}

				.give_preview_email_user_id_label {
					font-size: 12px;
					color: #333;
					margin: 0 4px 0 0;
				}
			</style>

			<!-- Start constructing HTML output.-->
			<div class="give_preview_email_user_id_main">

				<label for="give_preview_email_user_id" class="give_preview_email_user_id_label">
					<?php echo esc_html__( 'Preview email with a donor:', 'give' ); ?>
				</label>

				<?php
				// The select field with 100 latest transactions
				echo Give()->html->select(
					array(
						'name'             => 'preview_email_user_id',
						'selected'         => $user_id,
						'id'               => 'give_preview_email_user_id',
						'class'            => 'give-preview-email-donor-id',
						'options'          => $options,
						'chosen'           => false,
						'select_atts'      => 'onchange="change_preview()"',
						'show_option_all'  => false,
						'show_option_none' => false,
					)
				);
				?>
				<!-- Closing tag-->
			</div>
			<?php
		}
	}

endif; // End class_exists check

return Give_New_Donor_Register_Email::get_instance();
