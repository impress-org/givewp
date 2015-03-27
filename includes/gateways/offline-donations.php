<?php
/**
 * Offline Donations Gateway
 *
 * @package     Give
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
		'admin_label'    => 'Offline Donation',
		'checkout_label' => __( 'Offline Donation', 'give' )
	);

	return $gateways;
}

add_filter( 'give_payment_gateways', 'give_offline_register_gateway', 1 );


/**
 * Disables the automatic marking of abandoned orders
 * Marking pending payments as abandoned could break manual check payments
 *
 * @since  1.0
 * @return void
 */
function give_offline_disable_abandoned_orders() {
	remove_action( 'give_weekly_scheduled_events', 'give_mark_abandoned_orders' );
}

add_action( 'plugins_loaded', 'give_offline_disable_abandoned_orders' );


/**
 * Add our payment instructions to the checkout
 *
 * @since  1.0
 *
 * @param int $form_id
 *
 * @return void
 */
function give_offline_payment_cc_form( $form_id ) {

	$post_offline_customization_option = get_post_meta( $form_id, '_give_customize_offline_donations', true );
	$post_offline_instructions         = get_post_meta( $form_id, '_give_offline_checkout_notes', true );
	$global_offline_instruction        = give_get_option( 'global_offline_donation_content' );
	$offline_instructions              = $global_offline_instruction;

	if ( $post_offline_customization_option == 'yes' ) {
		$offline_instructions = $post_offline_instructions;
	}

	ob_start(); ?>
	<?php do_action( 'give_before_offline_info_fields' ); ?>
	<fieldset id="give_offline_payment_info">
		<?php
		$settings_url         = admin_url( 'post.php?post=' . $form_id . '&action=edit&message=1' );
		$offline_instructions = ! empty( $offline_instructions ) ? $offline_instructions : sprintf( __( 'Please enter offline donation instructions in the %s.', 'give' ), '<a href="' . $settings_url . '">' . __( 'this form\'s settings', 'give' ) . '</a>' );
		echo wpautop( stripslashes( $offline_instructions ) );
		?>
	</fieldset>
	<?php do_action( 'give_after_offline_info_fields' ); ?>
	<?php
	echo ob_get_clean();
}

add_action( 'give_offline_cc_form', 'give_offline_payment_cc_form' );


/**
 * Process the payment
 *
 * @since  1.0
 * @return void
 */
function give_offline_process_payment( $purchase_data ) {

	$purchase_summary = give_get_purchase_summary( $purchase_data );

	// setup the payment details
	$payment_data = array(
		'price'           => $purchase_data['price'],
		'give_form_title' => $purchase_data['post_data']['give-form-title'],
		'give_form_id'    => intval( $purchase_data['post_data']['give-form-id'] ),
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
		give_cg_send_admin_notice( $payment );
		give_send_to_success_page();
	} else {
		// if errors are present, send the user back to the donation form so they can be corrected
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	}

}

add_action( 'give_gateway_offline', 'give_offline_process_payment' );


/**
 * Sends a notice to site admins about the pending sale
 *
 * @since  1.0
 * @return void
 */
function give_cg_send_admin_notice( $payment_id = 0 ) {

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

	$admin_message = __( 'Hey there,', 'give' ) . "\n\n" . __( 'An offline donation has been made', 'give' ) . ".\n\n";

	$order_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $payment_id );
	$admin_message .= __( 'Donor: ', 'give' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$admin_message .= __( 'Amount: ', 'give' ) . " " . html_entity_decode( $amount, ENT_COMPAT, 'UTF-8' ) . "\n\n";
	$admin_message .= __( 'This is a pending donation awaiting payment.', 'give' ) . "\n\n";
	$admin_message .= sprintf( __( 'View Donation Details: %s.', 'give' ), $order_url ) . "\n\n";
	$admin_message = apply_filters( 'give_offline_admin_donation_notification', $admin_message, $payment_id );
	$admin_headers = apply_filters( 'give_offline_admin_donation_notification_headers', array(), $payment_id );
	$attachments   = apply_filters( 'give_offline_admin_donation_notification_attachments', array(), $payment_id );

	wp_mail( $admin_email, $admin_subject, $admin_message, $admin_headers, $attachments );

}


/**
 * Register gateway settings
 *
 * @since  1.0
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
			'name'    => __( 'Customize Offline Donation Instructions', 'give' ),
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
			'id'      => $prefix . 'offline_checkout_notes',
			'name'    => __( 'Offline Donation Instructions', 'give' ),
			'desc'    => __( 'Enter the instructions you want to display to the donor during the payment process. Most likely this would include important information like mailing address and who to make the check out to.', 'give' ),
			'default' => give_get_default_offline_donation_content(),
			'type'    => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 6,
			)
		)
	);

	return array_merge( $settings, $check_settings );
}

add_filter( 'give_forms_display_options_metabox_fields', 'give_offline_add_settings' );


/**
 * Get Default Offline Donation Text
 *
 * @return mixed|void
 */
function give_get_default_offline_donation_content() {

	$sitename = get_bloginfo( 'sitename' );

	$default_text = '<p>' . __( 'In order to make an offline donation we ask that you please follow these instructions', 'give' ) . ': </p>';
	$default_text .= '<ol>';
	$default_text .= '<li>' . _x( 'Make a check payable to ', 'Step 1 for donating offline by check', 'give' ) . '"' . $sitename . '"' . '</li>';
	$default_text .= '<li>' . _x( 'On the memo line of the check, please indicate that the donation is for ', 'Step 2 for donating by check; this explains who the check should be written to', 'give' ) . '"' . $sitename . '"' . '</li>';
	$default_text .= '<li>' . _x( 'Please mail your check to:', 'Step 3; where to mail the check', 'give' ) . '</li>';
	$default_text .= '</ol>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>' . $sitename . '</em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>123 G Street </em><br>';
	$default_text .= '&nbsp;&nbsp;&nbsp;&nbsp;<em>San Diego, CA 92101 </em><br>';
	$default_text .= '<p>' . __( 'All contributions will be gratefully acknowledged and are tax deductible.', 'give' ) . '</p>';

	return apply_filters( 'give_default_offline_donation_content', $default_text );

}