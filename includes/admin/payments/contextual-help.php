<?php
/**
 * Contextual Help
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Payments contextual help.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_payments_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download_page_edd-payment-history' ) {
		return;
	}

	$screen->set_help_sidebar(
		'<p><strong>' . sprintf( __( 'For more information:', 'give' ) . '</strong></p>' .
		                         '<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Easy Digital Downloads website.', 'give' ), esc_url( 'https://easydigitaldownloads.com/documentation/' ) ) ) . '</p>' .
		'<p>' . sprintf(
			__( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'give' ),
			esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues' ),
			esc_url( 'https://github.com/easydigitaldownloads/Easy-Digital-Downloads' ),
			esc_url( 'https://easydigitaldownloads.com/extensions/' ),
			esc_url( 'https://easydigitaldownloads.com/themes/' )
		) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'      => 'give-payments-overview',
		'title'   => __( 'Overview', 'give' ),
		'content' =>
			'<p>' . __( "This screen provides access to all of your store's transactions.", 'give' ) . '</p>' .
			'<p>' . __( 'Payments can be searched by email address, user name, or filtered by status (completed, pending, etc.)', 'give' ) . '</p>' .
			'<p>' . __( 'You also have the option to bulk delete payment should you wish.', 'give' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'      => 'give-payments-search',
		'title'   => __( 'Search Payments', 'give' ),
		'content' =>
			'<p>' . __( 'The payment history can be searched in several different ways:', 'give' ) . '</p>' .
			'<ul>
				<li>' . __( 'You can enter the customer\'s email address', 'give' ) . '</li>
				<li>' . __( 'You can enter the customer\'s name or ID prefexed by \'user:\'', 'give' ) . '</li>
				<li>' . __( 'You can enter the 32-character purchase key', 'give' ) . '</li>
				<li>' . __( 'You can enter the purchase ID', 'give' ) . '</li>
				<li>' . __( 'You can enter a transaction ID prefixed by \'txn:\'', 'give' ) . '</li>
				<li>' . sprintf( __( 'You can enter the %s ID prefixed by \'#\'', 'give' ), give_get_forms_label_singular() ) . '</li>
			</ul>'
	) );

	$screen->add_help_tab( array(
		'id'      => 'give-payments-details',
		'title'   => __( 'Payment Details', 'give' ),
		'content' =>
			'<p>' . __( 'Each payment can be further inspected by clicking the corresponding <em>View Order Details</em> link. This will provide more information including:', 'give' ) . '</p>' .

			'<ul>
				<li><strong>Purchased File</strong> - ' . __( 'The file associated with the purchase.', 'give' ) . '</li>
				<li><strong>Purchase Date</strong> - ' . __( 'The exact date and time the payment was completed.', 'give' ) . '</li>
				<li><strong>Discount Used</strong> - ' . __( 'If a coupon or discount was used during the checkout process.', 'give' ) . '</li>
				<li><strong>Name</strong> - ' . __( "The buyer's name.", 'give' ) . '</li>
				<li><strong>Email</strong> - ' . __( "The buyer's email address.", 'give' ) . '</li>
				<li><strong>Payment Notes</strong> - ' . __( 'Any customer-specific notes related to the payment.', 'give' ) . '</li>
				<li><strong>Payment Method</strong> - ' . __( 'The name of the payment gateway used to complete the payment.', 'give' ) . '</li>
				<li><strong>Purchase Key</strong> - ' . __( 'A unique key used to identify the payment.', 'give' ) . '</li>
			</ul>'
	) );

	do_action( 'give_payments_contextual_help', $screen );
}

add_action( 'load-download_page_give-payment-history', 'give_payments_contextual_help' );
