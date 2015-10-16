<?php
/**
 * Email Template
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets all the email templates that have been registerd. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to Give_Email_Templates->get_templates()
 *
 * @since 1.0
 * @return array $templates All the registered email templates
 */
function give_get_email_templates() {
	$templates = new Give_Emails;

	return $templates->get_templates();
}

/**
 * Email Template Tags
 *
 * @since 1.0
 *
 * @param string $message      Message with the template tags
 * @param array  $payment_data Payment Data
 * @param int    $payment_id   Payment ID
 * @param bool   $admin_notice Whether or not this is a notification email
 *
 * @return string $message Fully formatted message
 */
function give_email_template_tags( $message, $payment_data, $payment_id, $admin_notice = false ) {
	return give_do_email_tags( $message, $payment_id );
}

/**
 * Email Preview Template Tags
 *
 * @since 1.0
 * @global       $give_options Array of all the Give Options
 *
 * @param string $message      Email message with template tags
 *
 * @return string $message Fully formatted message
 */
function give_email_preview_template_tags( $message ) {
	global $give_options;

	$price = give_currency_filter( give_format_amount( 10.50 ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$notes = __( 'These are some sample notes added to a donation.', 'give' );

	$payment_id = rand( 1, 100 );

	$user = wp_get_current_user();

	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', date( get_option( 'date_format' ), current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{product_notes}', $notes, $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );
	$message = str_replace( '{receipt_link}', sprintf( __( '%1$sView it in your browser.%2$s', 'give' ), '<a href="' . esc_url( add_query_arg( array(
			'payment_key' => $receipt_id,
			'give_action' => 'view_receipt'
		), home_url() ) ) . '">', '</a>' ), $message );

	return wpautop( apply_filters( 'give_email_preview_template_tags', $message ) );
}

/**
 * Filter for Email Template Preview Buttons
 *
 * @param array $array
 *
 * @access private
 * @global      $give_options Array of all the Give Options
 * @since  1.0
 * @return array|bool
 */
add_filter( 'give_settings_emails', 'give_email_template_preview' );

function give_email_template_preview( $array ) {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return false;
	}
	$custom_field = array(
		'name' => __( 'Preview Email', 'give' ),
		'desc' => __( 'Click the buttons to preview emails.', 'give' ),
		'id'   => 'give_email_preview_buttons',
		'type' => 'email_preview_buttons'
	);
	array_splice( $array, 5, 0, array( $custom_field ) );

	return $array; // splice in at position 3;
}

/**
 * Output Email Template Preview Buttons
 *
 * @access private
 * @global      $give_options Array of all the Give Options
 * @since  1.0
 * @return array
 */
function give_email_preview_buttons_callback() {
	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'give_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank" title="<?php _e( 'Donation Receipt Preview', 'give' ); ?> "><?php _e( 'Preview Donation Receipt', 'give' ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array(
		'give_action'  => 'send_test_email',
		'give-message' => 'sent-test-email'
	) ), 'give-test-email' ); ?>" title="<?php _e( 'This will send a demo donation receipt to the emails listed below.', 'give' ); ?>" class="button-secondary"><?php _e( 'Send Test Email', 'give' ); ?></a>
	<?php
	echo ob_get_clean();
}

/**
 * Displays the email preview
 *
 * @since 1.0
 * @return void
 */
function give_display_email_template_preview() {

	if ( empty( $_GET['give_action'] ) ) {
		return;
	}

	if ( 'preview_email' !== $_GET['give_action'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return;
	}

	Give()->emails->heading = __( 'Donation Receipt', 'give' );

	echo Give()->emails->build_email( give_email_preview_template_tags( give_get_email_body_content( 0, array() ) ) );

	exit;

}

add_action( 'template_redirect', 'give_display_email_template_preview' );

/**
 * Email Template Body
 *
 * @since 1.0
 *
 * @param int   $payment_id   Payment ID
 * @param array $payment_data Payment Data
 *
 * @return string $email_body Body of the email
 */
function give_get_email_body_content( $payment_id = 0, $payment_data = array() ) {
	global $give_options;

	$default_email_body = __( "Dear", "give" ) . " {name},\n\n";
	$default_email_body .= __( "Thank you for your donation. Your generosity is appreciated! Please click on the link below to view your receipt.", "give" ) . "\n\n" . '{receipt_link}';
	$default_email_body .= "\n\nSincerely,\n{sitename}";

	$email = isset( $give_options['donation_receipt'] ) ? stripslashes( $give_options['donation_receipt'] ) : $default_email_body;

	$email_body = wpautop( $email );

	$email_body = apply_filters( 'give_donation_receipt_' . Give()->emails->get_template(), $email_body, $payment_id, $payment_data );

	return apply_filters( 'give_donation_receipt', $email_body, $payment_id, $payment_data );
}

/**
 * Sale Notification Template Body
 *
 * @since  1.0
 *
 * @param int   $payment_id   Payment ID
 * @param array $payment_data Payment Data
 *
 * @return string $email_body Body of the email
 */
function give_get_donation_notification_body_content( $payment_id = 0, $payment_data = array() ) {
	global $give_options;

	$user_info = maybe_unserialize( $payment_data['user_info'] );
	$email     = give_get_payment_user_email( $payment_id );

	if ( isset( $user_info['id'] ) && $user_info['id'] > 0 ) {
		$user_data = get_userdata( $user_info['id'] );
		$name      = $user_data->display_name;
	} elseif ( isset( $user_info['first_name'] ) && isset( $user_info['last_name'] ) ) {
		$name = $user_info['first_name'] . ' ' . $user_info['last_name'];
	} else {
		$name = $email;
	}

	$gateway = give_get_gateway_admin_label( get_post_meta( $payment_id, '_give_payment_gateway', true ) );

	$default_email_body = __( 'Hello', 'give' ) . "\n\n" . __( 'A donation has been made', 'give' ) . ".\n\n";
	$default_email_body .= sprintf( __( '%s sold:', 'give' ), give_get_forms_label_plural() ) . "\n\n";

	$default_email_body .= __( 'Donor: ', 'give' ) . " " . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Amount: ', 'give' ) . " " . html_entity_decode( give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= __( 'Payment Method: ', 'give' ) . " " . $gateway . "\n\n";

	$default_email_body .= __( 'Thank you', 'give' );

	$email = isset( $give_options['donation_notification'] ) ? stripslashes( $give_options['donation_notification'] ) : $default_email_body;

	$email_body = give_do_email_tags( $email, $payment_id );

	return apply_filters( 'give_donation_notification', wpautop( $email_body ), $payment_id, $payment_data );
}

/**
 * Render Receipt in the Browser
 *
 * A link is added to the Donation Receipt to view the email in the browser and
 * this function renders the Donation Receipt in the browser. It overrides the
 * Purchase Receipt template and provides its only styling.
 *
 * @since  1.0
 */
function give_render_receipt_in_browser() {
	if ( ! isset( $_GET['payment_key'] ) ) {
		wp_die( __( 'Missing donation key.', 'give' ), __( 'Error', 'give' ) );
	}

	$key = urlencode( $_GET['payment_key'] );

	ob_start();
	//Disallows caching of the page
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
	header("Expires: Sat, 23 Oct 1977 05:00:00 PST"); // Date in the past
	?>
	<!DOCTYPE html>
	<html lang="en">
		<head>
			<title><?php _e( 'Donation Receipt', 'give' ); ?></title>
			<meta charset="utf-8" />

			<!-- Further disallowing of caching of this page -->
			<meta charset="utf-8" />
			<meta http-equiv="cache-control" content="max-age=0" />
			<meta http-equiv="cache-control" content="no-cache" />
			<meta http-equiv="expires" content="0" />
			<meta http-equiv="expires" content="Tue, 23 Oct 1977 05:00:00 PST" />
			<meta http-equiv="pragma" content="no-cache" />

			<?php wp_head(); ?>
		</head>
		<body class="<?php echo apply_filters( 'give_receipt_page_body_class', 'give_receipt_page' ); ?>">

			<div id="give_receipt_wrapper">
				<?php do_action( 'give_render_receipt_in_browser_before' ); ?>
				<?php echo do_shortcode( '[give_receipt payment_key=' . $key . ']' ); ?>
				<?php do_action( 'give_render_receipt_in_browser_after' ); ?>
			</div>

			<?php wp_footer(); ?>
		</body>
	</html>
	<?php
	echo ob_get_clean();
	die();
}

add_action( 'give_view_receipt', 'give_render_receipt_in_browser' );
