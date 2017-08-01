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
 * @todo Modify this function to remove payment id dependency.
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

	$price = give_currency_filter( give_format_amount( 10.50, array( 'sanitize' => false ) ) );

	$gateway = 'PayPal';

	$receipt_id = strtolower( md5( uniqid() ) );

	$payment_id = rand( 1, 100 );

	$receipt_link_url = esc_url( add_query_arg( array( 'payment_key' => $receipt_id, 'give_action' => 'view_receipt' ), home_url() ) );
	$receipt_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		$receipt_link_url,
		esc_html__( 'View the receipt in your browser &raquo;', 'give' )
	);

	// Set user.
	$user = wp_get_current_user();

	$message = str_replace( '{name}', $user->display_name, $message );
	$message = str_replace( '{fullname}', $user->display_name, $message );
	$message = str_replace( '{username}', $user->user_login, $message );
	$message = str_replace( '{user_email}', $user->user_email, $message );
	$message = str_replace( '{billing_address}', "123 Test Street, Unit 222\nSomewhere Town, CA, 92101", $message );
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
 * Output Email Template Preview Buttons.
 *
 * @access private
 * @since  1.0
 * @since  1.8 Field arguments param added.
 *
 * @param array $field Field arguments.
 *
 * @return array
 */
function give_email_preview_buttons_callback( $field ) {
	$field_id = str_replace( '_preview_buttons', '', $field['id'] );

	ob_start();

	echo sprintf(
		'<a href="%1$s" class="button-secondary" target="_blank">%2$s</a>',
		wp_nonce_url(
			add_query_arg(
				array( 'give_action' => 'preview_email', 'email_type' => $field_id ),
				home_url()
			), 'give-preview-email'
		),
		$field['name']
	);

	echo sprintf(
		' <a href="%1$s" aria-label="%2$s" class="button-secondary">%3$s</a>',
		wp_nonce_url(
				add_query_arg( array(
			'give_action'  => 'send_preview_email',
			'email_type' => $field_id,
			'give-message' => 'sent-test-email',
		) ), 'give-send-preview-email' ),
		esc_attr__( 'Send Test Email.', 'give' ),
		esc_html__( 'Send Test Email', 'give' )
	);

	echo ob_get_clean();
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
	$payment_id    = give_check_variable( give_clean( $_GET ), 'isset', 0, 'preview_id' );

	if ( $payment_count <= 0 ) {
		return false;
	}

	//Get payments.
	$payments = new Give_Payments_Query( array(
		'number' => 100
	) );
	$payments = $payments->get_payments();
	$options  = array();

	// Default option.
	$options[0] = esc_html__( 'No donations found.', 'give' );

	//Provide nice human readable options.
	if ( $payments ) {
		$options[0] = esc_html__( '- Select a donation -', 'give' );
		foreach ( $payments as $payment ) {

			$options[ $payment->ID ] = esc_html( '#' . $payment->ID . ' - ' . $payment->email . ' - ' . $payment->form_title );

		}
	}

	//Start constructing HTML output.
	$transaction_header = '<div style="margin:0;padding:10px 0;width:100%;background-color:#FFF;border-bottom:1px solid #eee; text-align:center;">';

	//Inline JS function for switching donations.
	$request_url = $_SERVER['REQUEST_URI'];

	// Remove payment id query param if set from request url.
	if ( $payment_id ) {
		$request_url_data = wp_parse_url( $_SERVER['REQUEST_URI'] );
		$query            = $request_url_data['query'];
		$query            = str_replace( "&preview_id={$payment_id}", '', $query );

		$request_url = home_url( '/?' . str_replace( '', '', $query ) );
	}


	$transaction_header .= '<script>
				 function change_preview(){
				  var transactions = document.getElementById("give_preview_email_payment_id");
			        var selected_trans = transactions.options[transactions.selectedIndex];
				        console.log(selected_trans);
				        if (selected_trans){
				            var url_string = "' . $request_url . '&preview_id=" + selected_trans.value;
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
		'select_atts'      => 'onchange="change_preview()"',
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