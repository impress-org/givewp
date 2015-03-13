<?php
/**
 * Checks Gateway
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
 * @return array
 */
function give_checks_register_gateway( $gateways ) {
	// Format: ID => Name
	$gateways['checks'] = array( 'admin_label' => 'Checks', 'checkout_label' => __( 'Check', 'give' ) );

	return $gateways;
}

add_filter( 'give_payment_gateways', 'give_checks_register_gateway' );


/**
 * Disables the automatic marking of abandoned orders
 * Marking pending payments as abandoned could break manual check payments
 *
 * @since  1.0
 * @return void
 */
function give_checks_disable_abandoned_orders() {
	remove_action( 'give_weekly_scheduled_events', 'give_mark_abandoned_orders' );
}

add_action( 'plugins_loaded', 'give_checks_disable_abandoned_orders' );


/**
 * Add our payment instructions to the checkout
 *
 * @since  1.0
 * @return void
 */
function give_checks_payment_cc_form() {
	global $give_options;
	ob_start(); ?>
	<?php do_action( 'give_before_check_info_fields' ); ?>
	<fieldset id="give_check_payment_info">
		<?php
		$settings_url = admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways' );
		$notes        = ! empty( $give_options['give_checks_checkout_notes'] ) ? $give_options['give_checks_checkout_notes'] : sprintf( __( 'Please enter checkout instructions in the %s settings for paying by check.', 'give' ), '<a href="' . $settings_url . '">' . __( 'Payment Gateway', 'give' ) . '</a>' );
		echo wpautop( stripslashes( $notes ) );
		?>
	</fieldset>
	<?php do_action( 'give_after_check_info_fields' ); ?>
	<?php
	echo ob_get_clean();
}

add_action( 'give_checks_cc_form', 'give_checks_payment_cc_form' );


/**
 * Process the payment
 *
 * @since  1.0
 * @return void
 */
function give_checks_process_payment( $purchase_data ) {

	global $give_options;

	$purchase_summary = give_get_purchase_summary( $purchase_data );

	// setup the payment details
	$payment = array(
		'price'        => $purchase_data['price'],
		'date'         => $purchase_data['date'],
		'user_email'   => $purchase_data['user_email'],
		'purchase_key' => $purchase_data['purchase_key'],
		'currency'     => $give_options['currency'],
		'downloads'    => $purchase_data['downloads'],
		'cart_details' => $purchase_data['cart_details'],
		'user_info'    => $purchase_data['user_info'],
		'status'       => 'pending'
	);

	// record the pending payment
	$payment = give_insert_payment( $payment );

	if ( $payment ) {
		give_cg_send_admin_notice( $payment );
		give_empty_cart();
		give_send_to_success_page();
	} else {
		// if errors are present, send the user back to the purchase page so they can be corrected
		give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
	}

}

add_action( 'give_gateway_checks', 'give_checks_process_payment' );


/**
 * Sends a notice to site admins about the pending sale
 *
 * @since  1.1
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

	$admin_subject = apply_filters( 'give_checks_admin_purchase_notification_subject', __( 'New pending purchase', 'give' ), $payment_id );

	$admin_message = __( 'Hello', 'give' ) . "\n\n" . sprintf( __( 'A %s purchase has been made', 'give' ), give_get_label_plural() ) . ".\n\n";
	$admin_message .= sprintf( __( '%s sold:', 'give' ), give_get_label_plural() ) . "\n\n";

	$download_list = '';
	$downloads     = give_get_payment_meta_downloads( $payment_id );

	if ( is_array( $downloads ) ) {
		foreach ( $downloads as $download ) {
			$title = get_the_title( $download['id'] );
			if ( isset( $download['options'] ) ) {
				if ( isset( $download['options']['price_id'] ) ) {
					$title .= ' - ' . give_get_price_option_name( $download['id'], $download['options']['price_id'], $payment_id );
				}
			}
			$download_list .= html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) . "\n";
		}
	}

	$order_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&give-action=view-order-details&id=' . $payment_id );
	$admin_message .= $download_list . "\n";
	$admin_message .= __( 'Donated by: ', 'give' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$admin_message .= __( 'Amount: ', 'give' ) . " " . html_entity_decode( $amount, ENT_COMPAT, 'UTF-8' ) . "\n\n";
	$admin_message .= __( 'This is a pending donation awaiting payment.', 'give' ) . "\n\n";
	$admin_message .= sprintf( __( 'View Order Details: %s.', 'give' ), $order_url ) . "\n\n";
	$admin_message = apply_filters( 'give_checks_admin_purchase_notification', $admin_message, $payment_id );
	$admin_headers = apply_filters( 'give_checks_admin_purchase_notification_headers', array(), $payment_id );
	$attachments   = apply_filters( 'give_checks_admin_purchase_notification_attachments', array(), $payment_id );

	wp_mail( $admin_email, $admin_subject, $admin_message, $admin_headers, $attachments );
}


/**
 * Register gateway settings
 *
 * @since  1.0
 * @return array
 */
function give_checks_add_settings( $settings ) {

	$check_settings = array(
		array(
			'id'   => 'check_payment_settings',
			'name' => '<strong>' . __( 'Check Payment Settings', 'give' ) . '</strong>',
			'desc' => __( 'Configure the Check Payment settings', 'give' ),
			'type' => 'header'
		),
		array(
			'id'   => 'give_checks_checkout_notes',
			'name' => __( 'Check Payment Instructions', 'give' ),
			'desc' => __( 'Enter the instructions you want to show to the buyer during the checkout process here. This should probably include your mailing address and who to make the check out to.', 'give' ),
			'type' => 'rich_editor'
		)
	);

	return array_merge( $settings, $check_settings );
}

add_filter( 'give_settings_gateways', 'give_checks_add_settings' );