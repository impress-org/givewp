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
 * @since       2.0
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
	 * @since       2.0
	 */
	abstract class Give_Email_Notification {
		/**
		 * Array of instances
		 *
		 * @since  2.0
		 * @access private
		 * @var array
		 */
		private static $singleton = array();


		/**
		 * Array of notification settings.
		 *
		 * @since  2.0
		 * @access public
		 * @var array
		 */
		public $config = array(
			'id'                           => '',
			'label'                        => '',
			'description'                  => '',
			'has_recipient_field'          => false,
			'recipient_group_name'         => '',
			'notification_status'          => 'disabled',
			'notification_status_editable' => true,
			'has_preview'                  => true,
			'has_preview_header'           => true,
			'preview_email_tags_values'    => array(),
			'email_tag_context'            => 'all',
			'form_metabox_setting'         => true,
			'content_type'                 => '',
			'default_email_subject'        => '',
			'default_email_message'        => '',
		);

		/**
		 * @var     string $recipient_email Donor email.
		 * @access  protected
		 * @since   2.0
		 */
		protected $recipient_email = '';


		/**
		 * Setup email notification.
		 *
		 * @since 2.0
		 */
		public function init() {

		}


		/**
		 * Get instance.
		 *
		 * @since  2.0
		 * @access public
		 * @return Give_Email_Notification
		 */
		public static function get_instance() {
			$class = get_called_class();
			if ( ! array_key_exists( $class, self::$singleton ) || is_null( self::$singleton[ $class ] ) ) {
				self::$singleton[ $class ] = new $class();
			}

			return self::$singleton[ $class ];
		}

		/**
		 * Setup action and filters.
		 *
		 * @access  public
		 * @since   2.0
		 *
		 * @param array $config
		 */
		public function load( $config ) {
			// Set notification configuration.
			$this->config = wp_parse_args( $config, $this->config );

			// Set email preview header status.
			$this->config['has_preview_header'] = $this->config['has_preview'] && $this->config['has_preview_header'] ? true : false;

			// Set email content type
			$this->config['content_type'] = empty( $this->config['content_type'] ) || ! in_array( $this->config['content_type'], array( 'text/html', 'text/plain', ) )
				? Give()->emails->get_content_type()
				: $this->config['content_type'];
			$this->config['content_type'] = give_get_option( "{$this->config['id']}_email_content_type", $this->config['content_type'] );

			/**
			 *  Filter the notification config.
			 *
			 * @since 2.0
			 *
			 * @param                         array                   Give_Email_Notification::config
			 * @param Give_Email_Notification $this
			 */
			$this->config = apply_filters( 'give_email_api_notification_config', $this->config, $this );

			// Setup filters.
			$this->setup_filters();
		}


		/**
		 * Setup filters.
		 *
		 * @since  2.0
		 * @access public
		 */
		private function setup_filters() {
			// Apply filter only for current email notification section.
			if ( give_get_current_setting_section() === $this->config['id'] ) {
				// Initialize email context for email notification.
				$this->config['email_tag_context'] = apply_filters(
					"give_{$this->config['id']}_email_tag_context",
					$this->config['email_tag_context'],
					$this
				);
			}

			// Setup setting fields.
			add_filter( 'give_get_settings_emails', array( $this, 'add_setting_fields' ), 10, 2 );

			if ( $this->config['form_metabox_setting'] ) {
				add_filter(
					'give_email_notification_options_metabox_fields',
					array( $this, 'add_metabox_setting_field' ),
					10,
					2
				);
			}

			/**
			 * Filter the default email subject.
			 *
			 * @since 2.0
			 */
			$this->config['default_email_subject'] = apply_filters(
				"give_{$this->config['id']}_get_default_email_subject",
				$this->config['default_email_subject'],
				$this
			);

			/**
			 * Filter the default email message.
			 *
			 * @since 2.0
			 */
			$this->config['default_email_message'] = apply_filters(
				"give_{$this->config['id']}_get_default_email_message",
				$this->config['default_email_message'],
				$this
			);
		}

		/**
		 * Add sections.
		 *
		 * @since 2.0
		 *
		 * @param array $sections
		 *
		 * @return array
		 */
		public function add_section( $sections ) {
			$sections[ $this->config['id'] ] = $this->config['label'];

			return $sections;
		}

		/**
		 * Add sections.
		 *
		 * @since 2.0
		 *
		 * @param bool $hide_section
		 *
		 * @return bool
		 */
		public function hide_section( $hide_section ) {
			$hide_section = true;

			return $hide_section;
		}

		/**
		 * Register email settings.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param   array $settings
		 *
		 * @return  array
		 */
		public function add_setting_fields( $settings ) {
			if ( $this->config['id'] === give_get_current_setting_section() ) {
				$settings = $this->get_setting_fields();
			}

			return $settings;
		}


		/**
		 * Get setting fields
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return array
		 */
		public function get_setting_fields( $form_id = 0 ) {
			return Give_Email_Setting_Field::get_setting_fields( $this, $form_id );
		}


		/**
		 * Register email settings to form metabox.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param array $settings
		 * @param int   $post_id
		 *
		 * @return array
		 */
		public function add_metabox_setting_field( $settings, $post_id ) {

			$settings[] = array(
				'id'     => $this->config['id'],
				'title'  => $this->config['label'],
				'fields' => $this->get_setting_fields( $post_id ),
			);

			return $settings;
		}


		/**
		 * Get extra setting field.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return array
		 */
		public function get_extra_setting_fields( $form_id = 0 ) {
			return array();
		}


		/**
		 * Get recipient(s).
		 *
		 * Note: in case of admin notification this fx will return array of emails otherwise empty string or email of donor.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string|array
		 */
		public function get_recipient( $form_id = null ) {
			if ( empty( $this->recipient_email ) && $this->config['has_recipient_field'] ) {
				$this->recipient_email = Give_Email_Notification_Util::get_value( $this, "{$this->config['id']}_recipient", $form_id );
			}

			/**
			 * Filter the recipients
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_recipients",
				give_check_variable(
					$this->recipient_email,
					'empty',
					Give()->emails->get_from_address()
				),
				$this
			);
		}

		/**
		 * Get notification status.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return bool
		 */
		public function get_notification_status( $form_id = 0 ) {
			$notification_status = empty( $form_id )
				? give_get_option( "{$this->config['id']}_notification", $this->config['notification_status'] )
				: get_post_meta( $form_id,"{$this->config['id']}_notification", true  );

			/**
			 * Filter the notification status.
			 *
			 * @since 1.8
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_notification_status",
				$notification_status,
				$this
			);
		}

		/**
		 * Get email subject.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		function get_email_subject( $form_id = 0 ) {
			$subject = wp_strip_all_tags(
				Give_Email_Notification_Util::get_value(
					$this,
					"{$this->config['id']}_email_subject",
					$form_id,
					$this->config['default_email_subject']
				)
			);

			/**
			 * Filter the subject.
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_subject",
				$subject,
				$this
			);
		}

		/**
		 * Get email message.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 *
		 * @return string
		 */
		public function get_email_message( $form_id = 0 ) {
			$message = Give_Email_Notification_Util::get_value(
				$this,
				"{$this->config['id']}_email_message",
				$form_id,
				$this->config['default_email_message']
			);

			/**
			 * Filter the message.
			 *
			 * @since 2.0
			 */
			return apply_filters(
				"give_{$this->config['id']}_get_email_message",
				$message,
				$this
			);
		}


		/**
		 * Get allowed email tags for current email notification.
		 *
		 * @since  2.0
		 * @access private
		 *
		 * @param bool $formatted
		 *
		 * @return array
		 */
		public function get_allowed_email_tags( $formatted = false ) {
			// Get all email tags.
			$email_tags = Give()->email_tags->get_tags();

			// Skip if all email template tags context setup exit.
			if ( $this->config['email_tag_context'] && 'all' !== $this->config['email_tag_context'] ) {
				if ( is_array( $this->config['email_tag_context'] ) ) {
					foreach ( $email_tags as $index => $email_tag ) {
						if ( in_array( $email_tag['context'], $this->config['email_tag_context'] ) ) {
							continue;
						}

						unset( $email_tags[ $index ] );
					}
				} else {
					foreach ( $email_tags as $index => $email_tag ) {
						if ( $this->config['email_tag_context'] === $email_tag['context'] ) {
							continue;
						}

						unset( $email_tags[ $index ] );
					}
				}
			}

			if ( count( $email_tags ) && $formatted ) : ob_start() ?>
				<div class="give-email-tags-wrap">
					<?php foreach ( $email_tags as $email_tag ) : ?>
						<span class="give_<?php echo $email_tag['tag']; ?>_tag">
							<code>{<?php echo $email_tag['tag']; ?>}</code> - <?php echo $email_tag['description']; ?>
						</span>
					<?php endforeach; ?>
				</div>
				<?php
				$email_tags = ob_get_clean();
			endif;

			return $email_tags;
		}

		/**
		 * Get preview email recipients.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param int $form_id
		 * @return array|string
		 */
		public function get_preview_email_recipient( $form_id = 0 ) {
			$recipients = $this->get_recipient( $form_id );

			/**
			 * Filter the preview email recipients.
			 *
			 * @since 2.0
			 *
			 * @param string|array            $recipients List of recipients.
			 * @param Give_Email_Notification $this
			 */
			$recipients = apply_filters( 'give_get_preview_email_recipient', $recipients, $this );

			return $recipients;
		}

		/**
		 * Get the recipient attachments.
		 *
		 * @since  2.0
		 * @access public
		 * @return array
		 */
		public function get_email_attachments() {
			/**
			 * Filter the attachment.
			 *
			 * @since 2.0
			 */
			return apply_filters( "give_{$this->config['id']}_get_email_attachments", array(), $this );
		}


		/**
		 * Send preview email.
		 *
		 * @since  2.0
		 * @access public
		 */
		public function send_preview_email() {
			// Get form id
			$form_id = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;

			// setup email data.
			$this->setup_email_data();

			$attachments = $this->get_email_attachments();
			$message     = $this->preview_email_template_tags( $this->get_email_message( $form_id ) );
			$subject     = $this->preview_email_template_tags( $this->get_email_subject( $form_id ) );

			// Setup email content type.
			Give()->emails->__set( 'content_type', $this->config['content_type'] );

			// Format plain content type email.
			if ( 'text/plain' === $this->config['content_type'] ) {
				Give()->emails->__set( 'html', false );
				Give()->emails->__set( 'template', 'none' );
				$message = strip_tags( $message );
			}

			return Give()->emails->send( $this->get_preview_email_recipient( $form_id ), $subject, $message, $attachments );
		}


		/**
		 * Send email notification.
		 *
		 * Note: To render email tags certain parameter is necessary for other core:
		 * 	1. payment_id (required)
		 * 	2. user_id    (required)
		 * 	3. form_id    (required)
		 * 	3. for third party email tags you can pass necessary param along above parameters other value replace by empty string.
		 *
		 * @since  2.0
		 * @access public
		 *
		 * @param array $email_tag_args Arguments which helps to decode email template tags.
		 *
		 * @return bool
		 */
		public function send_email_notification( $email_tag_args = array() ) {
			/**
			 * Filter the email tag args
			 *
			 * @since 2.0
			 */
			$email_tag_args = apply_filters( "give_{$this->config['id']}_email_tag_args", $email_tag_args, $this );

			// Get form id.
			$form_id = ! empty( $email_tag_args['form_id'] ) ? absint( $email_tag_args['form_id'] ) : null;


			// Do not send email if notification is disable.
			if ( ! Give_Email_Notification_Util::is_email_notification_active( $this, $form_id ) ) {
				return false;
			}

			/**
			 * Fire action after before email send.
			 *
			 * @since 2.0
			 */
			do_action( "give_{$this->config['id']}_email_send_before", $this, $form_id );

			$attachments = $this->get_email_attachments();
			$message     = give_do_email_tags( $this->get_email_message( $form_id ), $email_tag_args );
			$subject     = give_do_email_tags( $this->get_email_subject( $form_id ), $email_tag_args );

			// Setup email content type.
			Give()->emails->__set( 'content_type', $this->config['content_type'] );

			if ( 'text/plain' === $this->config['content_type'] ) {
				Give()->emails->__set( 'html', false );
				Give()->emails->__set( 'template', 'none' );
				$message = strip_tags( $message );
			}

			// Send email.
			$email_status = Give()->emails->send( $this->get_recipient( $form_id ), $subject, $message, $attachments );

			/**
			 * Fire action after after email send.
			 *
			 * @since 2.0
			 */
			do_action( "give_{$this->config['id']}_email_send_after", $email_status, $this, $form_id );

			return $email_status;
		}


		/**
		 * Decode preview email template tags.
		 *
		 * @since 2.0
		 *
		 * @param string $message
		 *
		 * @return string
		 */
		public function preview_email_template_tags( $message ) {
			$user_id    = give_check_variable( give_clean( $_GET ), 'isset_empty', 0, 'user_id' );
			$user       = ! empty( $user_id ) ? get_user_by( 'id', $user_id ) : wp_get_current_user();
			$receipt_id = strtolower( md5( uniqid() ) );

			$receipt_link_url = esc_url( add_query_arg( array(
				'payment_key' => $receipt_id,
				'give_action' => 'view_receipt',
			), home_url() ) );

			$receipt_link = sprintf(
				'<a href="%1$s">%2$s</a>',
				$receipt_link_url,
				esc_html__( 'View the receipt in your browser &raquo;', 'give' )
			);

			// Set default values for tags.
			$this->config['preview_email_tags_values'] = wp_parse_args(
				$this->config['preview_email_tags_values'],
				array(
					'name'              => $user->display_name,
					'fullname'          => $user->display_name,
					'username'          => $user->user_login,
					'user_email'        => $user->user_email,
					'payment_total'     => give_currency_filter( give_format_amount( 10.50 ) ),
					'amount'            => give_currency_filter( give_format_amount( 10.50 ) ),
					'price'             => give_currency_filter( give_format_amount( 10.50 ) ),
					'payment_method'    => 'Paypal',
					'receipt_id'        => $receipt_id,
					'payment_id'        => give_check_variable( give_clean( $_GET ), 'isset_empty', rand( 2000, 2050 ), 'preview_id' ),
					'receipt_link_url'  => $receipt_link_url,
					'receipt_link'      => $receipt_link,
					'date'              => date( give_date_format(), current_time( 'timestamp' ) ),
					'donation'          => esc_html__( 'Sample Donation Form Title', 'give' ),
					'form_title'        => esc_html__( 'Sample Donation Form Title - Sample Donation Level', 'give' ),
					'sitename'          => get_bloginfo( 'name' ),
					'pdf_receipt'       => '<a href="#">Download Receipt</a>',
					'billing_address'   => '',
					'email_access_link' => sprintf(
						'<a href="%1$s">%2$s</a>',
						add_query_arg(
							array(
								'give_nl' => uniqid(),
							),
							get_permalink( give_get_option( 'history_page' ) )
						),
						__( 'Access Donation Details &raquo;', 'give' )
					),
				)
			);

			// Decode tags.
			foreach ( $this->config['preview_email_tags_values'] as $preview_tag => $value ) {
				if ( isset( $this->config['preview_email_tags_values'][ $preview_tag ] ) ) {
					$message = str_replace( "{{$preview_tag}}", $this->config['preview_email_tags_values'][ $preview_tag ], $message );
				}
			}

			return apply_filters( 'give_email_preview_template_tags', $message );
		}

		/**
		 * Setup email data
		 *
		 * @since 2.0
		 */
		public function setup_email_data() {
		}
	}

endif; // End class_exists check
