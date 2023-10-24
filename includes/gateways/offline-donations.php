<?php
/**
 * Offline Donations Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

/**
 * Register gateway settings.
 *
 * @param $settings
 *
 * @return array
 */
function give_offline_add_settings( $settings ) {

	// Bailout: Do not show offline gateways setting in to metabox if its disabled globally.
	if ( in_array( 'offline', (array) give_get_option( 'gateways' ) ) ) {
		return $settings;
	}

	// Vars
	$prefix = '_give_';

	$is_gateway_active = give_is_gateway_active( 'offline' );

	// this gateway isn't active
	if ( ! $is_gateway_active ) {
		// return settings and bounce
		return $settings;
	}

	// Fields
	$check_settings = [

		[
			'name'    => __( 'Offline Donations', 'give' ),
			'desc'    => __( 'Do you want to customize the donation instructions for this form?', 'give' ),
			'id'      => $prefix . 'customize_offline_donations',
			'type'    => 'radio_inline',
			'default' => 'global',
			'options' => apply_filters(
				'give_forms_content_options_select',
				[
					'global'   => __( 'Global Option', 'give' ),
					'enabled'  => __( 'Customize', 'give' ),
					'disabled' => __( 'Disable', 'give' ),
				]
			),
		],
		[
			'name'        => __( 'Billing Fields', 'give' ),
			'desc'        => __( 'This option will enable the billing details section for this form\'s offline donation payment gateway. The fieldset will appear above the offline donation instructions.', 'give' ),
			'id'          => $prefix . 'offline_donation_enable_billing_fields_single',
			'row_classes' => 'give-subfield give-hidden',
			'type'        => 'radio_inline',
			'default'     => 'disabled',
			'options'     => [
				'enabled'  => __( 'Enabled', 'give' ),
				'disabled' => __( 'Disabled', 'give' ),
			],
		],
		[
			'id'          => $prefix . 'offline_checkout_notes',
			'name'        => __( 'Donation Instructions', 'give' ),
			'desc'        => __( 'Enter the instructions you want to display to the donor during the donation process. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
			'default'     => give_get_default_offline_donation_content(),
			'type'        => 'wysiwyg',
			'row_classes' => 'give-subfield give-hidden',
			'options'     => [
				'textarea_rows' => 6,
			],
		],
		[
			'name'  => 'offline_docs',
			'type'  => 'docs_link',
			'url'   => 'http://docs.givewp.com/settings-gateway-offline-donations',
			'title' => __( 'Offline Donations', 'give' ),
		],
	];

	return array_merge( $settings, $check_settings );
}

add_filter( 'give_forms_offline_donations_metabox_fields', 'give_offline_add_settings' );


/**
 * Offline Donation Content
 *
 * Get default offline donation text
 *
 * @return string
 */
function give_get_default_offline_donation_content() {
	$default_text = '<p>' . __( 'To make an offline donation toward this cause, follow these steps:', 'give' ) . ' </p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'Write a check payable to "{sitename}"', 'give' )
	);
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'On the memo line of the check, indicate that the donation is for "{sitename}"', 'give' )
	);
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Mail your check to:', 'give' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '{offline_mailing_address}<br>';
	$default_text .= '<p>' . __( 'Your tax-deductible donation is greatly appreciated!', 'give' ) . '</p>';

	return apply_filters( 'give_default_offline_donation_content', $default_text );

}

/**
 * Offline Donation Email Content
 *
 * Gets the default offline donation email content
 *
 * @since 2.14.0 Remove unnecessary sprintf
 *
 * @return string
 */
function give_get_default_offline_donation_email_content() {
	$default_text = '<p>' . __( 'Hi {name},', 'give' ) . '</p>';
	$default_text .= '<p>' . __( 'Thank you for letting us know that you\'re mailing a check! Your generosity is greatly appreciated. Here are those steps again:', 'give' ) . '</p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= esc_html__( 'Write a check payable to "{sitename}"', 'give' );
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= esc_html__( 'On the memo line of the check, indicate that the donation is for "{form_title}"', 'give' );
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Mail your check to:', 'give' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '{offline_mailing_address}<br>';
	$default_text .= '<p>' . esc_html__( 'Once we receive the check, we will mark it as complete in our system, which will generate an email receipt for your records. Please contact us with any questions you may have!', 'give' ) . '</p>';
	$default_text .= '<p>' . esc_html__( 'Thanks in advance!', 'give' ) . '</p>';
	$default_text .= '<p>{sitename}</p>';

	return apply_filters( 'give_default_offline_donation_content', $default_text );
}

/**
 * Get formatted offline instructions
 *
 * @since 2.15.0
 *
 * @param  string  $instructions
 * @param  int  $form_id
 * @param  bool  $wpautop
 *
 * @return string
 */
function get_formatted_offline_instructions( $instructions, $form_id, $wpautop = false ) {
	$settings_url = admin_url( 'post.php?post=' . $form_id . '&action=edit&message=1' );

	/* translators: %s: form settings url */
	$offline_instructions = ! empty( $instructions ) ? $instructions : sprintf(
		__( 'Please enter offline donation instructions in <a href="%s">this form\'s settings</a>.', 'give' ),
		$settings_url
	);

	$offline_instructions = give_do_email_tags($offline_instructions, ['form_id' => $form_id]);

	return $wpautop ? wpautop( do_shortcode( $offline_instructions ) ) : $offline_instructions;
}

/**
 * Get offline payment instructions.
 *
 * @since 2.15.0 - conditionally display instructions based on form settings
 * @since 1.7
 *
 * @param  int  $form_id
 * @param  bool  $wpautop
 *
 * @return string
 */
function give_get_offline_payment_instruction( $form_id, $wpautop = false ) {
	// Bailout.
	if ( ! $form_id ) {
		return '';
	}

	$post_offline_customization_option = give_get_meta( $form_id, '_give_customize_offline_donations', true );
	$post_offline_customization_option_enabled = give_is_setting_enabled( $post_offline_customization_option );

	if ( $post_offline_customization_option === 'disabled' ) {
		return '';
	}

	$post_offline_instructions = give_get_meta( $form_id, '_give_offline_checkout_notes', true );
	$global_offline_instructions = give_get_option( 'global_offline_donation_content' );
	$offline_instructions_content = $post_offline_customization_option_enabled ? $post_offline_instructions : $global_offline_instructions;

	$formatted_offline_instructions = get_formatted_offline_instructions(
		$offline_instructions_content,
		$form_id,
		$wpautop
	);

	/**
	 * Filter the offline instruction content
	 *
	 * @since 2.2.0
	 */
	return apply_filters(
		'give_the_offline_instructions_content',
		$formatted_offline_instructions,
		$offline_instructions_content,
		$form_id,
		$wpautop
	);
}


/**
 * Remove offline gateway from gateway list of offline disable for form.
 *
 * @since  1.8
 *
 * @param  array   $gateway_list
 * @param        $form_id
 *
 * @return array
 */
function give_filter_offline_gateway( $gateway_list, $form_id ) {
	if (
		// Show offline payment gateway if enable for new donation form.
		( false === strpos( $_SERVER['REQUEST_URI'], '/wp-admin/post-new.php?post_type=give_forms' ) )
		&& $form_id
		&& ! give_is_setting_enabled( give_get_meta( $form_id, '_give_customize_offline_donations', true, 'global' ), [ 'enabled', 'global' ] )
	) {
		unset( $gateway_list['offline'] );
	}

	// Output.
	return $gateway_list;
}

add_filter( 'give_enabled_payment_gateways', 'give_filter_offline_gateway', 10, 2 );

/**
 * Set default gateway to global default payment gateway
 * if current default gateways selected offline and offline payment gateway is disabled.
 *
 * @since 1.8
 *
 * @param  string $meta_key   Meta key.
 * @param  string $meta_value Meta value.
 * @param  int    $postid     Form ID.
 *
 * @return void
 */
function _give_customize_offline_donations_on_save_callback( $meta_key, $meta_value, $postid ) {
	if (
		! give_is_setting_enabled( $meta_value, [ 'global', 'enabled' ] )
		&& ( 'offline' === give_get_meta( $postid, '_give_default_gateway', true ) )
	) {
		give_update_meta( $postid, '_give_default_gateway', 'global' );
	}
}

add_filter( 'give_save__give_customize_offline_donations', '_give_customize_offline_donations_on_save_callback', 10, 3 );
