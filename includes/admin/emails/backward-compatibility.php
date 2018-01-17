<?php

/**
 * Offline donation instruction setting backward compatibility.
 *
 * @since 2.0
 *
 * @param string                  $notification_status
 * @param Give_Email_Notification $email
 * @param int                     $form_id
 *
 * @return string
 */
function _give_bc_offline_donation_instruction_notification_status( $notification_status, $email, $form_id ) {
	// Bailout.
	if ( ! $form_id ) {
		return $notification_status;
	}

	if ( ! get_post_meta( $form_id, '_give_offline-donation-instruction_notification', true ) ) {
		$old_value           = get_post_meta( $form_id, '_give_customize_offline_donations', true );
		$notification_status = give_is_setting_enabled( $old_value, array( 'enabled', 'global' ) )
			? $old_value
			: 'global';
	}


	return $notification_status;
}

add_filter( 'give__give_offline-donation-instruction_get_notification_status', '_give_bc_offline_donation_instruction_notification_status', 10, 3 );


/**
 * Offline donation instruction setting backward compatibility.
 *
 * @since 2.0
 *
 * @param mixed                   $option_value
 * @param string                  $option_name
 * @param Give_Email_Notification $email
 * @param int                     $form_id
 *
 * @return mixed
 */
function _give_bc_offline_donation_instruction_email_setting_values( $option_value, $option_name, $email, $form_id ) {
	// Bailout.
	if ( empty( $form_id ) || 'offline-donation-instruction' !== $email->config['id'] ) {
		return $option_value;
	}

	switch ( $option_name ) {
		case '_give_offline-donation-instruction_email_message':
			if ( ! get_post_meta( $form_id, $option_name, true ) && give_is_setting_enabled( $email->get_notification_status( $form_id ) ) ) {
				$option_value = get_post_meta( $form_id, '_give_offline_donation_email', true );
			}
			break;

		case '_give_offline-donation-instruction_email_subject':
			if ( ! get_post_meta( $form_id, $option_name, true ) && give_is_setting_enabled( $email->get_notification_status( $form_id ) ) ) {
				$option_value = get_post_meta( $form_id, '_give_offline_donation_subject', true );
			}
			break;
	}

	return $option_value;
}

add_filter( 'give_email_setting_value', '_give_bc_offline_donation_instruction_email_setting_values', 10, 4 );


/**
 * Offline donation instruction setting for form metabox setting
 *
 * @since 2.0
 *
 * @param $field_value
 * @param $field
 * @param $form_id
 *
 * @return string
 */
function _give_bc_offline_instruction_status_setting_value( $field_value, $field, $form_id ) {
	if ( ! get_post_meta( $form_id, $field['id'], true ) ) {
		$old_value   = get_post_meta( $form_id, '_give_customize_offline_donations', true );
		$field_value = give_is_setting_enabled( $old_value, array( 'enabled', 'global' ) )
			? $old_value
			: 'global';
	}

	return $field_value;
}

add_filter( '_give_offline-donation-instruction_notification_field_value', '_give_bc_offline_instruction_status_setting_value', 10, 3 );


/**
 * Offline donation instruction setting for form metabox setting
 *
 * @since 2.0
 *
 * @param $field_value
 * @param $field
 * @param $form_id
 *
 * @return string
 */
function _offline_donation_instruction_email_subject_setting_value( $field_value, $field, $form_id ) {
	if ( ! get_post_meta( $form_id, $field['id'], true ) ) {
		$field_value = get_post_meta( $form_id, '_give_offline_donation_subject', true );
	}

	return $field_value;
}

add_filter( '_give_offline-donation-instruction_email_subject_field_value', '_offline_donation_instruction_email_subject_setting_value', 10, 3 );


/**
 * Offline donation instruction setting for form metabox setting
 *
 * @since 2.0
 *
 * @param $field_value
 * @param $field
 * @param $form_id
 *
 * @return string
 */
function _give_bc_offline_donation_instruction_email_message_setting_value( $field_value, $field, $form_id ) {
	if ( ! get_post_meta( $form_id, $field['id'], true ) ) {
		$field_value = get_post_meta( $form_id, '_give_offline_donation_email', true );
	}

	return $field_value;
}

add_filter( '_give_offline-donation-instruction_email_message_field_value', '_give_bc_offline_donation_instruction_email_message_setting_value', 10, 3 );