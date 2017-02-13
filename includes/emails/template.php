<?php
/**
 * Email Template
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets all the email templates that have been registered. The list is extendable
 * and more templates can be added.
 *
 * This is simply a wrapper to Give_Email_Templates->get_templates()
 *
 * @since 1.0
 * @return array $templates All the registered email templates.
 */
function give_get_email_templates() {
	$templates = new Give_Emails;

	return $templates->get_templates();
}

/**
 * Email Template Tags.
 *
 * @since 1.0
 *
 * @param string $message Message with the template tags.
 * @param array  $payment_data Payment Data.
 * @param int    $payment_id Payment ID.
 * @param bool   $admin_notice Whether or not this is a notification email.
 *
 * @return string $message Fully formatted message
 */
function give_email_template_tags( $message, $payment_data, $payment_id, $admin_notice = false ) {
	return give_do_email_tags( $message, $payment_id );
}

/**
 * Email Preview Template Tags.
 *
 * Provides sample content for the preview email functionality within settings > email.
 *
 * @since 1.0
 *
 * @param string $message Email message with template tags
 *
 * @return string $message Fully formatted message
 */
function give_email_preview_template_tags( $message ) {

	$price = give_currency_filter( give_format_amount( 10.50 ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$payment_id = rand( 1, 100 );

	$receipt_link_url = esc_url( add_query_arg( array( 'payment_key' => $receipt_id, 'give_action' => 'view_receipt' ), home_url() ) );
	$receipt_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		$receipt_link_url,
		esc_html__( 'View the receipt in your browser &raquo;', 'give' )
	);

	$user = wp_get_current_user();

	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{date}', date( give_date_format(), current_time( 'timestamp' ) ), $message );
	$message = str_replace( '{amount}', $price, $message );
	$message = str_replace( '{price}', $price, $message );
	$message = str_replace( '{donation}', esc_html__( 'Sample Donation Form Title', 'give' ), $message );
	$message = str_replace( '{form_title}', esc_html__( 'Sample Donation Form Title - Sample Donation Level', 'give' ), $message );
	$message = str_replace( '{receipt_id}', $receipt_id, $message );
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{payment_id}', $payment_id, $message );
	$message = str_replace( '{receipt_link}', $receipt_link, $message );
	$message = str_replace( '{receipt_link_url}', $receipt_link_url, $message );
	$message = str_replace( '{pdf_receipt}', '<a href="#">Download Receipt</a>', $message );

	return wpautop( apply_filters( 'give_email_preview_template_tags', $message ) );
}

/**
 * Filter for Email Template Preview Buttons.
 *
 * @param array $array
 *
 * @access private
 * @since  1.0
 * @return array|bool
 */
function give_email_template_preview( $array ) {

	if ( ! current_user_can( 'manage_give_settings' ) ) {
		return false;
	}
	$custom_field = array(
		'name' => esc_html__( 'Preview Email', 'give' ),
		'desc' => esc_html__( 'Click the buttons to preview or send test emails.', 'give' ),
		'id'   => 'give_email_preview_buttons',
		'type' => 'email_preview_buttons'
	);

	return give_settings_array_insert( $array, 'donation_subject', array( $custom_field ) );

}

add_filter( 'give_settings_emails', 'give_email_template_preview' );

/**
 * Output Email Template Preview Buttons.
 *
 * @access private
 * @since  1.0
 * @return array
 */
function give_email_preview_buttons_callback() {
	ob_start();
	?>
	<a href="<?php echo esc_url( add_query_arg( array( 'give_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'Preview Donation Receipt', 'give' ); ?></a>
	<a href="<?php echo wp_nonce_url( add_query_arg( array(
		'give_action'  => 'send_test_email',
		'give-message' => 'sent-test-email',
		'tag'          => 'emails'
	) ), 'give-test-email' ); ?>" aria-label="<?php esc_attr_e( 'Send demo donation receipt to the emails listed below.', 'give' ); ?>" class="button-secondary"><?php esc_html_e( 'Send Test Email', 'give' ); ?></a>
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


	Give()->emails->heading = esc_html__( 'Donation Receipt', 'give' );

	$payment_id = (int) isset( $_GET['preview_id'] ) ? $_GET['preview_id'] : '';

	echo give_get_preview_email_header();

	//Are we previewing an actual payment?
	if ( ! empty( $payment_id ) ) {

		$content = give_get_email_body_content( $payment_id );

		$preview_content = give_do_email_tags( $content, $payment_id );

	} else {

		//No payment ID, use sample preview content
		$preview_content = give_email_preview_template_tags( give_get_email_body_content( 0, array() ) );
	}


	echo Give()->emails->build_email( $preview_content );

	exit;

}

add_action( 'init', 'give_display_email_template_preview' );

/**
 * Email Template Body.
 *
 * @since 1.0
 *
 * @param int   $payment_id Payment ID
 * @param array $payment_data Payment Data
 *
 * @return string $email_body Body of the email
 */
function give_get_email_body_content( $payment_id = 0, $payment_data = array() ) {

	$default_email_body = give_get_default_donation_receipt_email();

	$email_content = give_get_option( 'donation_receipt' );
	$email_content = isset( $email_content ) ? stripslashes( $email_content ) : $default_email_body;

	$email_body = wpautop( $email_content );

	$email_body = apply_filters( 'give_donation_receipt_' . Give()->emails->get_template(), $email_body, $payment_id, $payment_data );

	return apply_filters( 'give_donation_receipt', $email_body, $payment_id, $payment_data );
}

/**
 * Donation Notification Template Body.
 *
 * @since  1.0
 *
 * @param int   $payment_id Payment ID
 * @param array $payment_data Payment Data
 *
 * @return string $email_body Body of the email
 */
function give_get_donation_notification_body_content( $payment_id = 0, $payment_data = array() ) {

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

	$default_email_body = esc_html__( 'Hello', 'give' ) . "\n\n";
	$default_email_body .= esc_html__( 'A donation has been made.', 'give' ) . "\n\n";
	$default_email_body .= esc_html__( 'Donation:', 'give' ) . "\n\n";
	$default_email_body .= esc_html__( 'Donor:', 'give' ) . ' ' . html_entity_decode( $name, ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= esc_html__( 'Amount:', 'give' ) . ' ' . html_entity_decode( give_currency_filter( give_format_amount( give_get_payment_amount( $payment_id ) ) ), ENT_COMPAT, 'UTF-8' ) . "\n";
	$default_email_body .= esc_html__( 'Payment Method:', 'give' ) . ' ' . $gateway . "\n\n";
	$default_email_body .= esc_html__( 'Thank you', 'give' );

	$email = give_get_option( 'donation_notification' );
	$email = isset( $email ) ? stripslashes( $email ) : $default_email_body;

	$email_body = give_do_email_tags( $email, $payment_id );

	return apply_filters( 'give_donation_notification', wpautop( $email_body ), $payment_id, $payment_data );
}

/**
 * Render Receipt in the Browser.
 *
 * A link is added to the Donation Receipt to view the email in the browser and
 * this function renders the Donation Receipt in the browser. It overrides the
 * Donation Receipt template and provides its only styling.
 *
 * @since  1.0
 */
function give_render_receipt_in_browser() {
	if ( ! isset( $_GET['payment_key'] ) ) {
		wp_die( esc_html__( 'Missing donation payment key.', 'give' ), esc_html__( 'Error', 'give' ), array( 'response' => 400 ) );
	}

	$key = urlencode( $_GET['payment_key'] );

	ob_start();
	//Disallows caching of the page
	header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
	header( "Cache-Control: no-store, no-cache, must-revalidate" ); // HTTP/1.1
	header( "Cache-Control: post-check=0, pre-check=0", false );
	header( "Pragma: no-cache" ); // HTTP/1.0
	header( "Expires: Sat, 23 Oct 1977 05:00:00 PST" ); // Date in the past
	?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<?php
		/**
		 * Fires in the receipt HEAD.
		 *
		 * @since 1.0
		 */
		do_action( 'give_receipt_head' );
		?>
	</head>
	<body class="<?php echo apply_filters( 'give_receipt_page_body_class', 'give_receipt_page' ); ?>">

	<div id="give_receipt_wrapper">
		<?php
		/**
		 * Fires in the receipt template before the content.
		 *
		 * @since 1.0
		 */
		do_action( 'give_render_receipt_in_browser_before' );

		echo do_shortcode( '[give_receipt payment_key=' . $key . ']' );

		/**
		 * Fires in the receipt template after the content.
		 *
		 * @since 1.0
		 */
		do_action( 'give_render_receipt_in_browser_after' );
		?>
	</div>

	<?php
	/**
	 * Fires in the receipt footer.
	 *
	 * @since 1.0
	 */
	do_action( 'give_receipt_footer' );
	?>
	</body>
	</html>
	<?php
	echo ob_get_clean();
	die();
}

add_action( 'give_view_receipt', 'give_render_receipt_in_browser' );


/**
 * Give Preview Email Header.
 *
 * Displays a header bar with the ability to change donations to preview actual data within the preview. Will not display if
 *
 * @since 1.6
 *
 */
function give_get_preview_email_header() {

	//Payment receipt switcher
	$payment_count = give_count_payments()->publish;
	$payment_id    = (int) isset( $_GET['preview_id'] ) ? $_GET['preview_id'] : '';

	if ( $payment_count <= 0 ) {
		return false;
	}

	//Get payments.
	$payments = new Give_Payments_Query( array(
		'number' => 100
	) );
	$payments = $payments->get_payments();
	$options  = array();

	//Provide nice human readable options.
	if ( $payments ) {
		$options[0] = esc_html__( '- Select a donation -', 'give' );
		foreach ( $payments as $payment ) {

			$options[ $payment->ID ] = esc_html( '#' . $payment->ID . ' - ' . $payment->email . ' - ' . $payment->form_title );

		}
	} else {
		$options[0] = esc_html__( 'No donations found.', 'give' );
	}

	//Start constructing HTML output.
	$transaction_header = '<div style="margin:0;padding:10px 0;width:100%;background-color:#FFF;border-bottom:1px solid #eee; text-align:center;">';

	//Inline JS function for switching donations.
	$transaction_header .= '<script>
				 function change_preview(){
				  var transactions = document.getElementById("give_preview_email_payment_id");
			        var selected_trans = transactions.options[transactions.selectedIndex];
				        console.log(selected_trans);
				        if (selected_trans){
				            var url_string = "' . get_bloginfo( 'url' ) . '?give_action=preview_email&preview_id=" + selected_trans.value;
				                window.location = url_string;
				        }
				    }
			    </script>';

	$transaction_header .= '<label for="give_preview_email_payment_id" style="font-size:12px;color:#333;margin:0 4px 0 0;">' . esc_html__( 'Preview email with a donation:', 'give' ) . '</label>';

	//The select field with 100 latest transactions
	$transaction_header .= Give()->html->select( array(
		'name'             => 'preview_email_payment_id',
		'selected'         => $payment_id,
		'id'               => 'give_preview_email_payment_id',
		'class'            => 'give-preview-email-payment-id',
		'options'          => $options,
		'chosen'           => false,
		'select_atts'      => 'onchange="change_preview()">',
		'show_option_all'  => false,
		'show_option_none' => false
	) );

	//Closing tag
	$transaction_header .= '</div>';

	return apply_filters( 'give_preview_email_receipt_header', $transaction_header );

}


/**
 * Give Receipt Head Content
 *
 * @since 1.6
 * @return string
 */
function give_receipt_head_content() {

	//Title.
	$output = '<title>' . esc_html__( 'Donation Receipt', 'give' ) . '</title>';

	//Meta.
	$output .= '<meta charset="utf-8"/>
		<!-- Further disallowing of caching of this page -->
		<meta charset="utf-8"/>
		<meta http-equiv="cache-control" content="max-age=0"/>
		<meta http-equiv="cache-control" content="no-cache"/>
		<meta http-equiv="expires" content="0"/>
		<meta http-equiv="expires" content="Tue, 23 Oct 1977 05:00:00 PST"/>
		<meta http-equiv="pragma" content="no-cache"/>
		<meta name="robots" content="noindex, nofollow"/>';

	//CSS
	$output .= '<link rel="stylesheet" href="' . give_get_stylesheet_uri() . '?ver=' . GIVE_VERSION . '">';

	echo apply_filters( 'give_receipt_head_content', $output );

}

add_action( 'give_receipt_head', 'give_receipt_head_content' );