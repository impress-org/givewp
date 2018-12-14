<?php
/**
 * Tools Actions
 *
 * @package     Give
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the recount batch processor.
 *
 * @since  1.5
 */
function give_register_batch_recount_export_classes() {
	add_action( 'give_batch_export_class_include', 'give_include_batch_export_class', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_recount_export_classes', 10 );


/**
 * Loads the tools batch processing classes.
 *
 * @since  1.8
 *
 * @param  string $class The class being requested to run for the batch export.
 *
 * @return void
 */
function give_include_batch_export_class( $class ) {
	switch ( $class ) {

		case 'Give_Tools_Delete_Donors':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-test-donors.php';
			break;

		case 'Give_Tools_Import_Donors':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-import-donors.php';
			break;

		case 'Give_Tools_Delete_Test_Transactions':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-test-transactions.php';
			break;

		case 'Give_Tools_Recount_Donor_Stats':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-donor-stats.php';
			break;

		case 'Give_Tools_Reset_Stats':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-reset-stats.php';
			break;

		case 'Give_Tools_Recount_All_Stats':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-all-stats.php';
			break;

		case 'Give_Tools_Recount_Form_Stats':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-form-stats.php';
			break;

		case 'Give_Tools_Recount_Income':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-recount-income.php';
			break;

		case 'Give_Tools_Delete_Donations':
			require_once GIVE_PLUGIN_DIR . 'includes/admin/tools/data/class-give-tools-delete-donations.php';
			break;
	}
}
