<?php
/**
 * Contextual Help
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Reports contextual help.
 *
 * @access      private
 * @since       1.0
 * @return      void
 */
function give_reporting_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download_page_give-reports' ) {
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
		'id'      => 'give-reports',
		'title'   => __( 'Reports', 'give' ),
		'content' => '<p>' . __( 'This screen provides you with reports for your earnings, downloads, customers and taxes.', 'give' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'      => 'give-reports-export',
		'title'   => __( 'Export', 'give' ),
		'content' =>
			'<p>' . __( 'This screen allows you to export your reports into a PDF or CSV format.', 'give' ) . '</p>' .
			'<p>' . __( '<strong>Sales and Earnings</strong> - This report exports all of the sales and earnings that you have made in the current year. This report includes your sales and earnings for each product as well a graphs of sales and earnings so you can compare them for each month.', 'give' ) . '</p>' .
			'<p>' . __( '<strong>Payment History</strong> - This report exports all of payments you have received on your EDD store in a CSV format.  The report includes the contact details of the customer, the products they have purchased as well as any discount codes they have used and the final price they have paid.', 'give' ) . '</p>' .
			'<p>' . __( "<strong>Customers</strong> - This report exports all of your customers in a CSV format. It exports the customer's name and email address and the amount of products they have purchased as well as the final price of their total purchases.", 'give' ) . '</p>' .
			'<p>' . __( '<strong>Download History</strong> - This report exports all of the downloads you have received in the current month into a CSV. It exports the date the file was downloaded, the customer it was downloaded by, their IP address, the name of the product and the file they downloaded.', 'give' ) . '</p>'
	) );

	if ( ! empty( $_GET['tab'] ) && 'logs' == $_GET['tab'] ) {
		$screen->add_help_tab( array(
			'id'      => 'give-reports-log-search',
			'title'   => __( 'Search File Downloads', 'give' ),
			'content' =>
				'<p>' . __( 'The file download log can be searched in several different ways:', 'give' ) . '</p>' .
				'<ul>
					<li>' . __( 'You can enter the customer\'s email address', 'give' ) . '</li>
					<li>' . __( 'You can enter the customer\'s IP address', 'give' ) . '</li>
					<li>' . __( 'You can enter the download file\'s name', 'give' ) . '</li>
				</ul>'
		) );
	}

	do_action( 'give_reports_contextual_help', $screen );
}

add_action( 'load-download_page_give-reports', 'give_reporting_contextual_help' );