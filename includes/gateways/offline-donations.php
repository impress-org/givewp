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
 * Add our payment instructions to the checkout
 *
 * @since  1.0
 *
 * @param  int $form_id Give form id.
 *
 * @return void
 */
function give_offline_payment_cc_form( $form_id ) {
	// Get offline payment instruction.
	$offline_instructions = give_get_offline_payment_instruction( $form_id, true );

	ob_start();

	/**
	 * Fires before the offline info fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id Give form id.
	 */
	do_action( 'give_before_offline_info_fields', $form_id );
	?>
	<fieldset class="no-fields" id="give_offline_payment_info">
		<?php echo stripslashes( $offline_instructions ); ?>
	</fieldset>
	<?php
	/**
	 * Fires after the offline info fields.
	 *
	 * @since 1.0
	 *
	 * @param int $form_id Give form id.
	 */
	do_action( 'give_after_offline_info_fields', $form_id );

	echo ob_get_clean();
}

add_action( 'give_offline_cc_form', 'give_offline_payment_cc_form' );

/**
 * Give Offline Billing Field
 *
 * @param $form_id
 */
function give_offline_billing_fields( $form_id ) {
	// Enable Default CC fields (billing info)
	$post_offline_cc_fields        = give_get_meta( $form_id, '_give_offline_donation_enable_billing_fields_single', true );
	$post_offline_customize_option = give_get_meta( $form_id, '_give_customize_offline_donations', true, 'global' );

	$global_offline_cc_fields = give_get_option( 'give_offline_donation_enable_billing_fields' );

	// Output CC Address fields if global option is on and user hasn't elected to customize this form's offline donation options
	if (
		( give_is_setting_enabled( $post_offline_customize_option, 'global' ) && give_is_setting_enabled( $global_offline_cc_fields ) )
		|| ( give_is_setting_enabled( $post_offline_customize_option, 'enabled' ) && give_is_setting_enabled( $post_offline_cc_fields ) )
	) {
		give_default_cc_address_fields( $form_id );
	}
}

add_action( 'give_before_offline_info_fields', 'give_offline_billing_fields', 10, 1 );

/**
 * Process the payment
 *
 * @since  1.0
 *
 * @param $purchase_data
 *
 * @return void
 */
function give_offline_process_payment( $purchase_data ) {

	// Setup the payment details.
	$payment_data = [
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => give_get_currency( $purchase_data['post_data']['give-form-id'], $purchase_data ),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'offline',
	];

	// record the pending payment
	$payment = give_insert_payment( $payment_data );

	if ( $payment ) {
		give_send_to_success_page();
	} else {
		// if errors are present, send the user back to the donation form so they can be corrected
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	}

}

add_action( 'give_gateway_offline', 'give_offline_process_payment' );


/**
 * Send Offline Donation Instructions
 *
 * Sends a notice to the donor with offline instructions; can be customized per form
 *
 * @param int $payment_id
 *
 * @since       1.0
 * @return void
 */
function give_offline_send_donor_instructions( $payment_id = 0 ) {

	$payment_data                      = give_get_payment_meta( $payment_id );
	$post_offline_customization_option = give_get_meta( $payment_data['form_id'], '_give_customize_offline_donations', true );

	// Customize email content depending on whether the single form has been customized
	$email_content = give_get_option( 'global_offline_donation_email' );

	if ( give_is_setting_enabled( $post_offline_customization_option, 'enabled' ) ) {
		$email_content = give_get_meta( $payment_data['form_id'], '_give_offline_donation_email', true );
	}

	$from_name = give_get_option( 'from_name', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );

	/**
	 * Filters the from name.
	 *
	 * @since 1.7
	 */
	$from_name = apply_filters( 'give_donation_from_name', $from_name, $payment_id, $payment_data );

	$from_email = give_get_option( 'from_email', get_bloginfo( 'admin_email' ) );

	/**
	 * Filters the from email.
	 *
	 * @since 1.7
	 */
	$from_email = apply_filters( 'give_donation_from_address', $from_email, $payment_id, $payment_data );

	$to_email = give_get_payment_user_email( $payment_id );

	$subject = give_get_option( 'offline_donation_subject', __( 'Offline Donation Instructions', 'give' ) );
	if ( give_is_setting_enabled( $post_offline_customization_option, 'enabled' ) ) {
		$subject = give_get_meta( $payment_data['form_id'], '_give_offline_donation_subject', true );
	}

	$subject = apply_filters( 'give_offline_donation_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = give_do_email_tags( $subject, $payment_id );

	$attachments = apply_filters( 'give_offline_donation_attachments', [], $payment_id, $payment_data );
	$message     = give_do_email_tags( $email_content, $payment_id );

	$emails = Give()->emails;

	$emails->__set( 'from_name', $from_name );
	$emails->__set( 'from_email', $from_email );
	$emails->__set( 'heading', __( 'Offline Donation Instructions', 'give' ) );

	$headers = apply_filters( 'give_receipt_headers', $emails->get_headers(), $payment_id, $payment_data );
	$emails->__set( 'headers', $headers );

	$emails->send( $to_email, $subject, $message, $attachments );

}


/**
 * Send Offline Donation Admin Notice.
 *
 * Sends a notice to site admins about the pending donation.
 *
 * @since       1.0
 *
 * @param int $payment_id
 *
 * @return void
 */
function give_offline_send_admin_notice( $payment_id = 0 ) {

	/* Send an email notification to the admin */
	$admin_email = give_get_admin_notice_emails();
	$user_info   = give_get_payment_meta_user_info( $payment_id );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $user_info['email'];
	}

	$amount = give_donation_amount( $payment_id );

	$admin_subject = apply_filters( 'give_offline_admin_donation_notification_subject', __( 'New Pending Donation', 'give' ), $payment_id );

	$admin_message  = __( 'Dear Admin,', 'give' ) . "\n\n";
	$admin_message .= sprintf( __( 'A new offline donation has been made on your website for %s.', 'give' ), $amount ) . "\n\n";
	$admin_message .= __( 'The donation is in a pending status and is awaiting payment. Donation instructions have been emailed to the donor. Once you receive payment, be sure to mark the donation as complete using the link below.', 'give' ) . "\n\n";

	$admin_message .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
	$admin_message .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n\n";

	$admin_message .= sprintf(
		'<a href="%1$s">%2$s</a>',
		admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=' . $payment_id ),
		__( 'View Donation Details &raquo;', 'give' )
	) . "\n\n";

	$admin_message = apply_filters( 'give_offline_admin_donation_notification', $admin_message, $payment_id );
	$admin_message = give_do_email_tags( $admin_message, $payment_id );

	$attachments   = apply_filters( 'give_offline_admin_donation_notification_attachments', [], $payment_id );
	$admin_headers = apply_filters( 'give_offline_admin_donation_notification_headers', [], $payment_id );

	// Send Email
	$emails = Give()->emails;
	$emails->__set( 'heading', __( 'New Offline Donation', 'give' ) );

	if ( ! empty( $admin_headers ) ) {
		$emails->__set( 'headers', $admin_headers );
	}

	$emails->send( $admin_email, $admin_subject, $admin_message, $attachments );

}


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

	$offline_instructions = give_do_email_tags( $offline_instructions, null );

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
