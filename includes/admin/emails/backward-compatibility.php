<?php
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
function _give_offline_donation_instruction_setting_value( $option_value, $option_name, $email, $form_id ) {
	// Bailout.
	if ( empty( $form_id ) || 'offline-donation-instruction' !== $email->config['id'] ) {
		return $option_name;
	}

	switch ( $option_name ) {
		case 'offline-donation-instruction_notification':
			if ( false === get_post_meta( $form_id, $option_name, true ) ) {
				$old_value    = get_post_meta( $form_id, '_give_customize_offline_donations', true );
				$option_value = give_is_setting_enabled( $old_value, array( 'enabled', 'global' ) )
					? $old_value
					: 'global';
			}

			break;

		case 'offline-donation-instruction_email_message':
			if ( false === get_post_meta( $form_id, $option_name, true ) && give_is_setting_enabled( $email->get_notification_status( $form_id ) ) ) {
				$option_value = get_post_meta( $form_id, '_give_offline_donation_email', true );
			}
			break;

		case 'offline-donation-instruction_email_subject':
			if ( false === get_post_meta( $form_id, $option_name, true ) && give_is_setting_enabled( $email->get_notification_status( $form_id ) ) ) {
				$option_value = get_post_meta( $form_id, '_give_offline_donation_subject', true );
			}
			break;
	}

	return $option_value;
}

add_filter( 'give_email_setting_value', '_give_offline_donation_instruction_setting_value', 10, 4 );