<?php
/**
 * Offline Donations Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

/**
 * Register the payment gateway
 *
 * @since  1.0
 *
 * @param array $gateways
 *
 * @return array
 */
function give_offline_register_gateway( $gateways ) {
	// Format: ID => Name
	$gateways['offline'] = array(
		'admin_label'    => esc_attr__( 'Offline Donation', 'give' ),
		'checkout_label' => esc_attr__( 'Offline Donation', 'give' )
	);

	return $gateways;
}

add_filter( 'give_payment_gateways', 'give_offline_register_gateway', 1 );


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
	<fieldset id="give_offline_payment_info">
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
	//Enable Default CC fields (billing info)
	$post_offline_cc_fields        = get_post_meta( $form_id, '_give_offline_donation_enable_billing_fields_single', true );
	$post_offline_customize_option = get_post_meta( $form_id, '_give_customize_offline_donations', true );

	$global_offline_cc_fields      = give_get_option( 'give_offline_donation_enable_billing_fields' );

	//Output CC Address fields if global option is on and user hasn't elected to customize this form's offline donation options
	if ( $global_offline_cc_fields == 'on' && $post_offline_customize_option !== 'yes' ) {
		give_default_cc_address_fields( $form_id );
	} elseif($post_offline_customize_option == 'yes' && $post_offline_cc_fields == 'on') {
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

	$purchase_summary = give_get_purchase_summary( $purchase_data );

	// setup the payment details
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
		'give_price_id'   => isset( $purchase_data['post_data']['give-price-id'] ) ? $purchase_data['post_data']['give-price-id'] : '',
		'date'            => $purchase_data['date'],
		'user_email'      => $purchase_data['user_email'],
		'purchase_key'    => $purchase_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $purchase_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'offline'
	);


	// record the pending payment
	$payment = give_insert_payment( $payment_data );

	if ( $payment ) {
		give_offline_send_admin_notice( $payment );
		give_offline_send_donor_instructions( $payment );
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
	$post_offline_customization_option = get_post_meta( $payment_data['form_id'], '_give_customize_offline_donations', true );

	//Customize email content depending on whether the single form has been customized
	$email_content = give_get_option( 'global_offline_donation_email' );

	if ( $post_offline_customization_option === 'yes' ) {
		$email_content = get_post_meta( $payment_data['form_id'], '_give_offline_donation_email', true );
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
	if ( $post_offline_customization_option === 'yes' ) {
		$subject = get_post_meta( $payment_data['form_id'], '_give_offline_donation_subject', true );
	}

	$subject = apply_filters( 'give_offline_donation_subject', wp_strip_all_tags( $subject ), $payment_id );
	$subject = give_do_email_tags( $subject, $payment_id );

	$attachments = apply_filters( 'give_offline_donation_attachments', array(), $payment_id, $payment_data );
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
 *
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

	$amount = give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) );

	$admin_subject = apply_filters( 'give_offline_admin_donation_notification_subject', __( 'New Pending Donation', 'give' ), $payment_id );

	$admin_message = __( 'Dear Admin,', 'give' ) . "\n\n";
	$admin_message .= __( 'An offline donation has been made on your website:', 'give' ) . ' ' . get_bloginfo( 'name' ) . ' ';
	$admin_message .= __( 'Hooray! The donation is in a pending status and is awaiting payment. Donation instructions have been emailed to the donor. Once you receive payment, be sure to mark the donation as complete using the link below.', 'give' ) . "\n\n";


	$admin_message .= '<strong>' . __( 'Donor:', 'give' ) . '</strong> {fullname}' . "\n";
	$admin_message .= '<strong>' . __( 'Amount:', 'give' ) . '</strong> {amount}' . "\n\n";

	$admin_message .= sprintf(
		'<a href="%1$s">%2$s</a>',
		admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment_id ),
		__( 'Click Here to View and/or Update Donation Details', 'give' )
	) . "\n\n";

	$admin_message = apply_filters( 'give_offline_admin_donation_notification', $admin_message, $payment_id );
	$admin_message = give_do_email_tags( $admin_message, $payment_id );

	$attachments   = apply_filters( 'give_offline_admin_donation_notification_attachments', array(), $payment_id );
	$admin_headers = apply_filters( 'give_offline_admin_donation_notification_headers', array(), $payment_id );

	//Send Email
	$emails = Give()->emails;
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

	//Vars
	$prefix = '_give_';

	$is_gateway_active = give_is_gateway_active( 'offline' );

	//this gateway isn't active
	if ( ! $is_gateway_active ) {
		//return settings and bounce
		return $settings;
	}

	//Fields
	$check_settings = array(

		array(
			'name'    => __( 'Customize Offline Donations', 'give' ),
			'desc'    => __( 'If you would like to customize the donation instructions for this specific forms check this option.', 'give' ),
			'id'      => $prefix . 'customize_offline_donations',
			'type'    => 'radio_inline',
			'default' => 'no',
			'options' => array(
				'yes' => __( 'Yes', 'give' ),
				'no'  => __( 'No', 'give' ),
			),
		),
		array(
			'name'        => __( 'Request Billing Information', 'give' ),
			'desc'        => __( 'This option will enable the billing details section for this form\'s offline donation payment gateway. The fieldset will appear above the offline donation instructions.', 'give' ),
			'id'          => $prefix . 'offline_donation_enable_billing_fields_single',
			'row_classes' => 'give-subfield',
			'type'        => 'checkbox'
		),
		array(
			'id'          => $prefix . 'offline_checkout_notes',
			'name'        => __( 'Offline Donation Instructions', 'give' ),
			'desc'        => __( 'Enter the instructions you want to display to the donor during the donation process. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
			'default'     => give_get_default_offline_donation_content(),
			'type'        => 'wysiwyg',
			'row_classes' => 'give-subfield',
			'options'     => array(
				'textarea_rows' => 6,
			)
		),
		array(
			'id'          => $prefix . 'offline_donation_subject',
			'name'        => __( 'Offline Donation Email Instructions Subject', 'give' ),
			'desc'        => __( 'Enter the subject line for the donation receipt email.', 'give' ),
			'default'     => __( '{form_title} - Offline Donation Instructions', 'give' ),
			'row_classes' => 'give-subfield',
			'type'        => 'text'
		),
		array(
			'id'          => $prefix . 'offline_donation_email',
			'name'        => __( 'Offline Donation Email Instructions', 'give' ),
			'desc'        => __( 'Enter the instructions you want emailed to the donor after they have submitted the donation form. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
			'default'     => give_get_default_offline_donation_email_content(),
			'type'        => 'wysiwyg',
			'row_classes' => 'give-subfield',
			'options'     => array(
				'textarea_rows' => 6,
			)
		)
	);

	return array_merge( $settings, $check_settings );
}

add_filter( 'give_forms_display_options_metabox_fields', 'give_offline_add_settings' );


/**
 * Offline Donation Content
 *
 * Get default offline donation text
 *
 * @return string
 */
function give_get_default_offline_donation_content() {

	$sitename = get_bloginfo( 'sitename' );

	$default_text = '<p>' . __( 'In order to make an offline donation we ask that you please follow these instructions', 'give' ) . ': </p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'Make a check payable to "%s"', 'give' ),
		$sitename
	);
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'On the memo line of the check, please indicate that the donation is for "%s"', 'give' ),
		$sitename
	);
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Please mail your check to:', 'give' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>' . $sitename . '</em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>123 G Street </em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>San Diego, CA 92101 </em><br>';
	$default_text .= '<p>' . __( 'All contributions will be gratefully acknowledged and are tax deductible.', 'give' ) . '</p>';

	return apply_filters( 'give_default_offline_donation_content', $default_text );

}

/**
 * Offline Donation Email Content
 *
 * Gets the default offline donation email content
 *
 * @return string
 */
function give_get_default_offline_donation_email_content() {

	$sitename      = get_bloginfo( 'sitename' );
	$default_text  = '<p>' . __( 'Dear {name},', 'give' ) . '</p>';
	$default_text .= '<p>' . __( 'Thank you for your offline donation request! Your generosity is greatly appreciated. In order to make an offline donation we ask that you please follow these instructions:', 'give' ) . '</p>';
	$default_text .= '<ol>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'Make a check payable to "%s"', 'give' ),
		$sitename
	);
	$default_text .= '</li>';
	$default_text .= '<li>';
	$default_text .= sprintf(
		/* translators: %s: site name */
		__( 'On the memo line of the check, please indicate that the donation is for "%s"', 'give' ),
		$sitename
	);
	$default_text .= '</li>';
	$default_text .= '<li>' . __( 'Please mail your check to:', 'give' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>' . $sitename . '</em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>123 G Street </em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>San Diego, CA 92101 </em><br>';
	$default_text .= '<p>' . __( 'Once your donation has been received we will mark it as complete and you will receive an email receipt for your records. Please contact us with any questions you may have!', 'give' ) . '</p>';
	$default_text .= '<p>' . __( 'Sincerely,', 'give' ) . '</p>';
	$default_text .= '<p>' . $sitename . '</p>';

	return apply_filters( 'give_default_offline_donation_content', $default_text );

}

/**
 * Set notice for offline donation.
 *
 * @since 1.7
 *
 * @param string $notice
 * @param int    $id
 *
 * @return string
 */
function give_offline_donation_receipt_status_notice( $notice, $id ) {
	$payment = new Give_Payment( $id );

	if ( 'offline' !== $payment->gateway ) {
		return $notice;
	}

	return give_output_error( 'Payment Pending: Please follow the instructions below to complete your donation.', false, 'warning' );
}

add_filter( 'give_receipt_status_notice', 'give_offline_donation_receipt_status_notice', 10, 2 );

/**
 * Add offline payment instruction on payment receipt.
 *
 * @since 1.7
 *
 * @param WP_Post $payment
 *
 * @return mixed
 */
function give_offline_payment_receipt_after( $payment ) {
	// Get payment object.
	$payment = new Give_Payment( $payment->ID );

	// Bailout.
	if ( 'offline' !== $payment->gateway ) {
		return false;
	}

	?>
	<tr>
		<td scope="row"><strong><?php esc_html_e( 'Offline Payment Instruction:', 'give' ); ?></strong></td>
		<td>
			<?php echo give_get_offline_payment_instruction( $payment->form_id, true ); ?>
		</td>
	</tr>
	<?php
}

add_filter( 'give_payment_receipt_after', 'give_offline_payment_receipt_after' );

/**
 * Get offline payment instructions.
 *
 * @since 1.7
 *
 * @param int  $form_id
 * @param bool $wpautop
 *
 * @return string
 */
function give_get_offline_payment_instruction( $form_id, $wpautop = false ) {
	// Bailout.
	if ( ! $form_id ) {
		return '';
	}

	$post_offline_customization_option = get_post_meta( $form_id, '_give_customize_offline_donations', true );
	$post_offline_instructions         = get_post_meta( $form_id, '_give_offline_checkout_notes', true );
	$global_offline_instruction        = give_get_option( 'global_offline_donation_content' );
	$offline_instructions              = $global_offline_instruction;

	if ( $post_offline_customization_option == 'yes' ) {
		$offline_instructions = $post_offline_instructions;
	}

	$settings_url = admin_url( 'post.php?post=' . $form_id . '&action=edit&message=1' );

	/* translators: %s: form settings url */
	$offline_instructions = ! empty( $offline_instructions ) ? $offline_instructions : sprintf( __( 'Please enter offline donation instructions in <a href="%s">this form\'s settings</a>.', 'give' ), $settings_url );

	return ( $wpautop ? wpautop( $offline_instructions ) : $offline_instructions );
}
