<?php

/**
 * Email Notification Setting Fields
 *
 * @package     Give
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2016, WordImpress
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
	public static function get_setting_fields( Give_Email_Notification $email, $form_id = 0 ) {
		$setting_fields = self::get_default_setting_fields( $email, $form_id );

		// Recipient field.
		if ( Give_Email_Notification_Util::has_recipient_field( $email ) ) {
			$setting_fields[] = self::get_recipient_setting_field( $email, $form_id );
		}

		// Preview field.
		if ( Give_Email_Notification_Util::has_preview( $email ) ) {
			$setting_fields[] = self::get_preview_setting_field( $email, $form_id );
		}

		// Add extra setting field.
		if ( $extra_setting_field = $email->get_extra_setting_fields( $form_id ) ) {
			$setting_fields = array_merge( $setting_fields, $extra_setting_field );
		}

		$setting_fields = self::add_section_end( $email, $setting_fields );

		return $setting_fields;
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
	public static function get_section_start( Give_Email_Notification $email, $form_id = 0 ) {
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
	public static function get_default_setting_fields( Give_Email_Notification $email, $form_id = 0 ) {
		$settings[] = self::get_section_start( $email, $form_id );

		if ( Give_Email_Notification_Util::is_notification_status_editable( $email ) ) {
			$settings[] = self::get_notification_status_field( $email, $form_id );
		}

		$settings[] = self::get_email_subject_field( $email, $form_id );
		$settings[] = self::get_email_message_field( $email, $form_id );
		$settings[] = self::get_email_content_type_field( $email, $form_id );

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
	public static function get_notification_status_field( Give_Email_Notification $email, $form_id = 0 ) {
		$option = array(
			'enabled'  => __( 'Enabled', 'give' ),
			'disabled' => __( 'Disabled', 'give' ),
		);

		$default_value = $email->get_notification_status();

		// Remove global options.
		if ( $form_id ) {
			$option = array(
				'global'   => __( 'Global Options' ),
				'enabled'  => __( 'Customize', 'give' ),
				'disabled' => __( 'Disabled', 'give' ),
			);

			$default_value = 'global';
		}

		return array(
			'name'          => esc_html__( 'Notification', 'give' ),
			'desc'          => esc_html__( 'Choose option if you want to send email notification or not.', 'give' ),
			'id'            => "{$email->config['id']}_notification",
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
	public static function get_email_subject_field( Give_Email_Notification $email, $form_id = 0 ) {
		return array(
			'id'      => "{$email->config['id']}_email_subject",
			'name'    => esc_html__( 'Email Subject', 'give' ),
			'desc'    => esc_html__( 'Enter the subject line for email.', 'give' ),
			'default' => $email->config['default_email_subject'],
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
	public static function get_email_message_field( Give_Email_Notification $email, $form_id = 0 ) {
		$desc = esc_html__( 'Enter the email message.', 'give' );

		if ( $email_tag_list = $email->get_allowed_email_tags( true ) ) {
			$desc = sprintf(
				esc_html__( 'Enter the email that is sent to users after completing a successful donation. HTML is accepted. Available template tags: %s', 'give' ),
				$email_tag_list
			);

		}

		return array(
			'id'      => "{$email->config['id']}_email_message",
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
	public static function get_email_content_type_field( Give_Email_Notification $email, $form_id = 0 ) {
		return array(
			'id'      => "{$email->config['id']}_email_content_type",
			'name'    => esc_html__( 'Email Content Type', 'give' ),
			'desc'    => __( 'Choose email content type.', 'give' ),
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
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_recipient_setting_field( Give_Email_Notification $email, $form_id = 0 ) {
		return array(
			'id'               => "{$email->config['id']}_recipient",
			'name'             => esc_html__( 'Email Recipients', 'give' ),
			'desc'             => __( 'Enter the email address(es) that should receive a notification anytime a donation is made.', 'give' ),
			'type'             => 'email',
			'default'          => get_bloginfo( 'admin_email' ),
			'repeat'           => true,
			'repeat_btn_title' => esc_html__( 'Add Recipient', 'give' ),
		);
	}

	/**
	 * Get preview setting field.
	 *
	 * @since  2.0
	 * @access static
	 *
	 * @param Give_Email_Notification $email
	 * @param int                     $form_id
	 *
	 * @return array
	 */
	public static function get_preview_setting_field( Give_Email_Notification $email, $form_id = 0 ) {
		return array(
			'name' => esc_html__( 'Preview Email', 'give' ),
			'desc' => esc_html__( 'Click the buttons to preview emails.', 'give' ),
			'id'   => "{$email->config['id']}_preview_buttons",
			'type' => 'email_preview_buttons',
		);
	}
}

// @todo: add per email sender options
