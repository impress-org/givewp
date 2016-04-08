<?php
/**
 * Exports Functions
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     Give
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/class-export.php';

/**
 * Exports earnings for a specified time period
 * Give_Earnings_Export class.
 *
 * @since 1.0
 * @return void
 */
function give_export_earnings() {
	require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/class-export-earnings.php';

	$earnings_export = new Give_Earnings_Export();

	$earnings_export->export();
}

add_action( 'give_earnings_export', 'give_export_earnings' );

/**
 * Exports all the payments stored in Payment History to a CSV file using the
 * Give_Export class.
 *
 * @since 1.0
 * @return void
 */
function give_export_payment_history() {
	require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/class-export-payments.php';

	$payments_export = new Give_Payments_Export();

	$payments_export->export();
}

add_action( 'give_payment_export', 'give_export_payment_history' );

/**
 * Export all the donors to a CSV file.
 *
 * Note: The WordPress Database API is being used directly for performance
 * reasons (workaround of calling all posts and fetch data respectively)
 *
 * @since 1.0
 * @return void
 */
function give_export_all_donors() {
	require_once GIVE_PLUGIN_DIR . 'includes/admin/reporting/class-export-customers.php';

	$donor_export = new Give_Donors_Export();

	$donor_export->export();
}

add_action( 'give_email_export', 'give_export_all_donors' );