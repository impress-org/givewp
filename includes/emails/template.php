<?php
/**
 * Email Template
 *
 * @package     Give
 * @subpackage  Emails
 * @copyright   Copyright (c) 2016, GiveWP
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
	$templates = new Give_Emails();

	return $templates->get_templates();
}

/**
 * Email Template Tags.
 *
 * @todo Modify this function to remove payment id dependency.
 *
 * @since 1.0
 *
 * @param string $message      Message with the template tags.
 * @param array  $payment_data Payment Data.
 * @param int    $payment_id   Payment ID.
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
 * @param string $message Email message with template tags.
 *
 * @return string $message Fully formatted message
 */
function give_email_preview_template_tags( $message ) {

	$user             = wp_get_current_user();
	$gateway          = 'PayPal';
	$donation_id      = rand( 1, 100 );
	$receipt_link     = give_get_receipt_link( $donation_id );
	$receipt_link_url = give_get_receipt_url( $donation_id );
	$price            = give_currency_filter(
		give_format_amount(
			10.50,
			array(
				'sanitize' => false,
			)
		)
	);

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
	$message = str_replace( '{payment_method}', $gateway, $message );
	$message = str_replace( '{sitename}', get_bloginfo( 'name' ), $message );
	$message = str_replace( '{payment_id}', $donation_id, $message );
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
				array(
					'give_action' => 'preview_email',
					'email_type'  => $field_id,
				),
				home_url()
			),
			'give-preview-email'
		),
		$field['name']
	);

	echo sprintf(
		' <a href="%1$s" aria-label="%2$s" class="button-secondary">%3$s</a>',
		wp_nonce_url(
			add_query_arg(
				array(
					'give_action'     => 'send_preview_email',
					'email_type'      => $field_id,
					'give-messages[]' => 'sent-test-email',
				)
			),
			'give-send-preview-email'
		),
		esc_attr__( 'Send Test Email.', 'give' ),
		esc_html__( 'Send Test Email', 'give' )
	);

	echo ob_get_clean();
}


/**
 * Give Preview Email Header.
 *
 * Displays a header bar with the ability to change donations to preview actual data within the preview. Will not display if
 *
 * @since 1.6
 */
function give_get_preview_email_header() {

	// Payment receipt switcher
	$payment_count = give_count_payments()->publish;
	$payment_id    = give_check_variable( give_clean( $_GET ), 'isset', 0, 'preview_id' );

	if ( $payment_count <= 0 ) {
		return false;
	}

	// Get payments.
	$donations = new Give_Payments_Query(
		array(
			'number' => 100,
			'output' => '',
			'fields' => 'ids',
		)
	);
	$donations = $donations->get_payments();
	$options   = array();

	// Default option.
	$options[0] = esc_html__( 'No donations found.', 'give' );

	// Provide nice human readable options.
	if ( $donations ) {
		$options[0] = esc_html__( '- Select a donation -', 'give' );
		foreach ( $donations as $donation_id ) {

			$options[ $donation_id ] = sprintf(
				'#%1$s - %2$s - %3$s',
				$donation_id,
				give_get_donation_donor_email( $donation_id ),
				get_the_title( $donation_id )
			);
		}
	}

	// Start constructing HTML output.
	$transaction_header = '<div style="margin:0;padding:10px 0;width:100%;background-color:#FFF;border-bottom:1px solid #eee; text-align:center;">';

	// Remove payment id query param if set from request url.
	$request_url_data = wp_parse_url( $_SERVER['REQUEST_URI'] );
	$query            = $request_url_data['query'];
	$query            = remove_query_arg( array( 'preview_id' ), $query );

	$request_url = home_url( '/?' . str_replace( '', '', $query ) );

	$transaction_header .= '<script>
				 function change_preview(){
				  var transactions = document.getElementById("give_preview_email_payment_id");
			        var selected_trans = transactions.options[transactions.selectedIndex];
				        if (selected_trans){
				            var url_string = "' . $request_url . '&preview_id=" + selected_trans.value;
				                window.location = url_string;
				        }
				    }
			    </script>';

	$transaction_header .= '<label for="give_preview_email_payment_id" style="font-size:12px;color:#333;margin:0 4px 0 0;">' . esc_html__( 'Preview email with a donation:', 'give' ) . '</label>';

	// The select field with 100 latest transactions
	$transaction_header .= Give()->html->select(
		array(
			'name'             => 'preview_email_payment_id',
			'selected'         => $payment_id,
			'id'               => 'give_preview_email_payment_id',
			'class'            => 'give-preview-email-payment-id',
			'options'          => $options,
			'chosen'           => false,
			'select_atts'      => 'onchange="change_preview()"',
			'show_option_all'  => false,
			'show_option_none' => false,
		)
	);

	// Closing tag
	$transaction_header .= '</div>';

	return apply_filters( 'give_preview_email_receipt_header', $transaction_header );

}
