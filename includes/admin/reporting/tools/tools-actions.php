<?php
/**
 * Tools Actions
 *
 * @package     Give
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.5
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the recount batch processor
 * @since  1.5
 */
function give_register_batch_recount_store_earnings_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_recount_income_tool_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_recount_store_earnings_tool', 10 );

/**
 * Loads the tools batch processing class for recounting store earnings
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_recount_income_tool_batch_processor( $class ) {

	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-recount-income.php';

	if ( 'Give_Tools_Recount_Income' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}

/**
 * Register the recount form batch processor
 *
 * @since  1.5
 */
function give_register_batch_recount_form_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_recount_form_tool_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_recount_form_tool', 10 );

/**
 * Loads the tools batch processing class for recounting download stats
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_recount_form_tool_batch_processor( $class ) {

	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-recount-form-stats.php';

	if ( 'Give_Tools_Recount_Form_Stats' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}

/**
 * Register the recount all stats batch processor
 * @since  1.5
 */
function give_register_batch_recount_all_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_recount_all_tool_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_recount_all_tool', 10 );

/**
 * Loads the tools batch processing class for recounting all stats
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_recount_all_tool_batch_processor( $class ) {
	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-recount-all-stats.php';
	if ( 'Give_Tools_Recount_All_Stats' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}

/**
 * Register the reset stats batch processor
 * 
 * @since  1.5
 */
function give_register_batch_reset_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_reset_tool_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_reset_tool', 10 );

/**
 * Loads the tools batch processing class for resetting store and product earnings
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_reset_tool_batch_processor( $class ) {

	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-reset-stats.php';

	if ( 'Give_Tools_Reset_Stats' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}

/**
 * Register the reset customer stats batch processor
 * @since  1.5
 */
function give_register_batch_customer_recount_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_customer_recount_tool_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_customer_recount_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all customer stats
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_customer_recount_tool_batch_processor( $class ) {

	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-recount-customer-stats.php';

	if ( 'Give_Tools_Recount_Customer_Stats' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}

/**
 * Register the delete test transactions batch processor
 * @since  1.5
 */
function give_register_batch_delete_test_transactions_tool() {
	add_action( 'give_batch_export_class_include', 'give_include_delete_test_transactions_batch_processor', 10, 1 );
}

add_action( 'give_register_batch_exporter', 'give_register_batch_delete_test_transactions_tool', 10 );

/**
 * Loads the tools batch processing class for resetting all customer stats
 *
 * @since  1.5
 *
 * @param  string $class The class being requested to run for the batch export
 *
 * @return void
 */
function give_include_delete_test_transactions_batch_processor( $class ) {

	$file_path = GIVE_PLUGIN_DIR . 'includes/admin/reporting/tools/class-give-tools-delete-test-transactions.php';

	if ( 'Give_Tools_Delete_Test_Transactions' === $class && file_exists( $file_path ) ) {
		require_once $file_path;
	}

}
