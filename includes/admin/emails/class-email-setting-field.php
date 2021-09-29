<?php

/**
 * Email Notification Setting Fields
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */
class Give_Email_Setting_Field {
	/**
	 * Get setting field.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_setting_fields( Give_Email_Notification $email, $form_id = null ) {
		$setting_fields = self::get_default_setting_fields( $email, $form_id );

		// Recipient field.
		$setting_fields[] = self::get_recipient_setting_field( $email, $form_id, Give_Email_Notification_Util::has_recipient_field( $email ) );

		// Add extra setting field.
		if ( $extra_setting_field = $email->get_extra_setting_fields( $form_id ) ) {
			$setting_fields = array_merge( $setting_fields, $extra_setting_field );
		}

		// Preview field.
		if ( Give_Email_Notification_Util::has_preview( $email ) ) {
			$setting_fields[] = self::get_preview_setting_field( $email, $form_id );
		}

		$setting_fields = self::add_section_end( $email, $setting_fields );

		/**
		 * Filter the email notification settings.
		 *
		 * @since 2.0
		 */
		return apply_filters( 'give_email_notification_setting_fields', $setting_fields, $email, $form_id );
	}


	/**
	 * Check if email notification setting has section end or not.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param $setting
	 *
	 * @return bool
	 */
	public static function has_section_end( $setting ) {
		$last_field      = end( $setting );
		$has_section_end = false;

		if ( 'sectionend' === $last_field['type'] ) {
			$has_section_end = true;
		}

		return $has_section_end;
	}

	/**
	 * Check if email notification setting has section end or not.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_section_start( Give_Email_Notification $email, $form_id = null ) {
		// Add section end field.
		$setting = array(
			'id'    => "give_title_email_settings_{$email->config['id']}",
			'type'  => 'title',
			'title' => $email->config['label'],
		);

		return $setting;
	}

	/**
	 * Check if email notification setting has section end or not.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param array                   $setting
	 * @param Give_Email_Notification $email
	 *
	 * @return array
	 */
	public static function add_section_end( Give_Email_Notification $email, $setting ) {
		if ( ! self::has_section_end( $setting ) ) {
			// Add section end field.
			$setting[] = array(
				'id'   => "give_title_email_settings_{$email->config['id']}",
				'type' => 'sectionend',
			);
		}

		return $setting;
	}

	/**
	 * Get default setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_default_setting_fields( Give_Email_Notification $email, $form_id = null ) {
		$settings[] = self::get_section_start( $email, $form_id );
		$settings[] = self::get_notification_status_field( $email, $form_id );

		if ( ! Give_Email_Notification_Util::is_notification_status_editable( $email ) ) {
			if ( $form_id || give_is_add_new_form_page() ) {
				// Do not allow admin to disable notification on perform basis.
				unset( $settings[1]['options']['disabled'] );
			} else {
				// Do not allow admin to edit notification status globally.
				unset( $settings[1] );
			}
		}

		$settings[] = self::get_email_subject_field( $email, $form_id );
		$settings[] = self::get_email_header_field( $email, $form_id );
		$settings[] = self::get_email_message_field( $email, $form_id );

		if ( Give_Email_Notification_Util::is_content_type_editable( $email ) ) {
			$settings[] = self::get_email_content_type_field( $email, $form_id );
		}

		return $settings;
	}

	/**
	 * Get notification status setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_notification_status_field( Give_Email_Notification $email, $form_id = null ) {
		$option = array(
			'enabled'  => __( 'Enabled', 'give' ),
			'disabled' => __( 'Disabled', 'give' ),
		);

		$default_value = $email->get_notification_status();

		// Add global options.
		if ( $form_id || give_is_add_new_form_page() ) {
			$option = array(
				'global'   => __( 'Global Options' ),
				'enabled'  => __( 'Customize', 'give' ),
				'disabled' => __( 'Disabled', 'give' ),
			);

			$default_value = 'global';
		}

		$description = isset( $_GET['page'] ) && 'give-settings' === $_GET['page'] ? __( 'Choose whether you want this email enabled or not.', 'give' ) : sprintf( __( 'Global Options are set <a href="%s">in GiveWP settings</a>. You may override them for this form here.', 'give' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=emails' ) );

		return array(
			'name'          => esc_html__( 'Notification', 'give' ),
			'desc'          => $description,
			'id'            => self::get_prefix( $email, $form_id ) . 'notification',
			'type'          => 'radio_inline',
			'default'       => $default_value,
			'options'       => $option,
			'wrapper_class' => 'give_email_api_notification_status_setting',
		);
	}

	/**
	 * Get email subject setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_email_subject_field( Give_Email_Notification $email, $form_id = null ) {
		return array(
			'id'      => self::get_prefix( $email, $form_id ) . 'email_subject',
			'name'    => esc_html__( 'Email Subject', 'give' ),
			'desc'    => esc_html__( 'Enter the email subject line.', 'give' ),
			'default' => $email->config['default_email_subject'],
			'type'    => 'text',
		);
	}

	/**
	 * Get email header setting field.
	 *
	 * @since  2.1.3
	 *
	 * @param Give_Email_Notification $email   The email object.
	 * @param int                     $form_id The Form ID.
	 *
	 * @return array
	 */
	public static function get_email_header_field( Give_Email_Notification $email, $form_id = null ) {
		return array(
			'id'      => self::get_prefix( $email, $form_id ) . 'email_header',
			'name'    => esc_html__( 'Email Header', 'give' ),
			'desc'    => esc_html__( 'Enter the email header that appears at the top of the email.', 'give' ),
			'default' => $email->config['default_email_header'],
			'type'    => 'text',
		);
	}

	/**
	 * Get email message setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_email_message_field( Give_Email_Notification $email, $form_id = null ) {
		$desc = esc_html__( 'Enter the email message.', 'give' );

		if ( $email_tag_list = $email->get_allowed_email_tags( true ) ) {
			$desc = sprintf(
				'%1$s <br> %2$s %3$s',
				__( 'Available template tags for this email. HTML is accepted.', 'give' ),
				$email_tag_list,
				sprintf(
					'<br><a href="%1$s" target="_blank">%2$s</a> %3$s',
					esc_url( 'http://docs.givewp.com/meta-email-tags' ),
					__( 'See our documentation', 'give' ),
					__( 'for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails.', 'give' )
				)
			);

		}

		return array(
			'id'      => self::get_prefix( $email, $form_id ) . 'email_message',
			'name'    => esc_html__( 'Email message', 'give' ),
			'desc'    => $desc,
			'type'    => 'wysiwyg',
			'default' => $email->config['default_email_message'],
		);
	}

	/**
	 * Get email message setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_email_content_type_field( Give_Email_Notification $email, $form_id = null ) {
		return array(
			'id'      => self::get_prefix( $email, $form_id ) . 'email_content_type',
			'name'    => esc_html__( 'Email Content Type', 'give' ),
			'desc'    => __( 'Choose email type.', 'give' ),
			'type'    => 'select',
			'options' => array(
				'text/html'  => Give_Email_Notification_Util::get_formatted_email_type( 'text/html' ),
				'text/plain' => Give_Email_Notification_Util::get_formatted_email_type( 'text/plain' ),
			),
			'default' => $email->config['content_type'],
		);
	}


	/**
	 * Get recipient setting field.
	 *
	 * @since  2.0
	 * @access public
	 * @todo   check this field in form metabox setting after form api merge.
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 * @param bool                    $edit_recipient
	 *
	 * @return array
	 */
	public static function get_recipient_setting_field( Give_Email_Notification $email, $form_id = null, $edit_recipient = true ) {
		$recipient = array(
			'id'               => self::get_prefix( $email, $form_id ) . 'recipient',
			'name'             => esc_html__( 'Email Recipients', 'give' ),
			'desc'             => __( 'Enter the email address(es) that should receive a notification.', 'give' ),
			'type'             => 'email',
			'default'          => get_bloginfo( 'admin_email' ),
			'repeat'           => true,
			'repeat_btn_title' => esc_html__( 'Add Recipient', 'give' ),
		);

		if ( $form_id || give_is_add_new_form_page() ) {
			$recipient['name']    = __( 'Email', 'give' );
			$recipient['default'] = '';
			$recipient['id']      = 'email';
			$recipient['desc']    = __( 'Enter the email address that should receive a notification.', 'give' );

			$recipient = array(
				'id'      => self::get_prefix( $email, $form_id ) . 'recipient',
				'type'    => 'group',
				'options' => array(
					'add_button'    => __( 'Add Email', 'give' ),
					'header_title'  => __( 'Email Recipient', 'give' ),
					'remove_button' => '<span class="dashicons dashicons-no"></span>',
				),
				'fields'  => array(
					$recipient,
				),
			);
		}

		// Disable field if email donor has recipient field.
		// @see https://github.com/impress-org/give/issues/2657
		if ( ! $edit_recipient ) {
			if ( 'group' == $recipient['type'] ) {
				$recipient         = current( $recipient['fields'] );
				$recipient['type'] = 'text';
			}

			$recipient['attributes']['disabled'] = 'disabled';
			$recipient['value']                  = $recipient['default'] = '{donor_email}';
			$recipient['repeat']                 = false;
			$recipient['desc']                   = __( 'This email is automatically sent to the donor and the recipient cannot be customized.', 'give' );
		}

		return $recipient;
	}

	/**
	 * Get preview setting field.
	 *
	 * @param Give_Email_Notification $email   Email Type.
	 * @param int                     $form_id Form ID.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @return array
	 */
	public static function get_preview_setting_field( Give_Email_Notification $email, $form_id = null ) {
		return array(
			'name' => __( 'Preview Email', 'give' ),
			'desc' => sprintf(
				'%1$s<br />%2$s <code>%3$s</code>.',
				esc_html__( 'Click the "Preview Email" button to preview the email in your browser.', 'give' ),
				esc_html__( 'Click the "Send Test Email" button to send a test email to yourself at', 'give' ),
				wp_get_current_user()->user_email
			),
			'id'   => self::get_prefix( $email, $form_id ) . 'preview_buttons',
			'type' => 'email_preview_buttons',
		);
	}


	/**
	 * Get form metabox setting field prefix.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return string
	 */
	public static function get_prefix( Give_Email_Notification $email, $form_id = null ) {
		$meta_key = "{$email->config['id']}_";

		if ( $form_id || give_is_add_new_form_page() ) {
			$meta_key = "_give_{$email->config['id']}_";
		}

		return $meta_key;
	}
}

// @todo: add per email sender options
