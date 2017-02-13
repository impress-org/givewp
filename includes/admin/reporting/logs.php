<?php
/**
 * Logs UI
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sales Log View
 *
 * @since 1.0
 * @uses  Give_Sales_Log_Table::prepare_items()
 * @uses  Give_Sales_Log_Table::display()
 * @return void
 */
function give_logs_view_sales() {
	include( dirname( __FILE__ ) . '/class-sales-logs-list-table.php' );

	$logs_table = new Give_Sales_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();

}

add_action( 'give_logs_view_sales', 'give_logs_view_sales' );


/**
 * Gateway Error Logs
 *
 * @since 1.0
 * @uses  Give_File_Downloads_Log_Table::prepare_items()
 * @uses  Give_File_Downloads_Log_Table::display()
 * @return void
 */
function give_logs_view_gateway_errors() {
	include( dirname( __FILE__ ) . '/class-gateway-error-logs-list-table.php' );

	$logs_table = new Give_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	$logs_table->display();
}

add_action( 'give_logs_view_gateway_errors', 'give_logs_view_gateway_errors' );

/**
 * API Request Logs
 *
 * @since 1.0
 * @uses  Give_API_Request_Log_Table::prepare_items()
 * @uses  Give_API_Request_Log_Table::search_box()
 * @uses  Give_API_Request_Log_Table::display()
 * @return void
 */
function give_logs_view_api_requests() {
	include( dirname( __FILE__ ) . '/class-api-requests-logs-list-table.php' );

	$logs_table = new Give_API_Request_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="wrap">

		<?php
		/**
		 * Fires before displaying API requests logs.
		 *
		 * @since 1.0
		 */
		do_action( 'give_logs_api_requests_top' );
		?>

		<form id="give-logs-filter" method="get" action="<?php echo 'edit.php?post_type=give_forms&page=give-tools&tab=logs'; ?>">
			<?php
			$logs_table->search_box( esc_html__( 'Search', 'give' ), 'give-api-requests' );
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-tools"/>
			<input type="hidden" name="tab" value="logs"/>
		</form>
		<?php
		/**
		 * Fires after displaying API requests logs.
		 *
		 * @since 1.0
		 */
		do_action( 'give_logs_api_requests_bottom' );
		?>

	</div>
	<?php
}

add_action( 'give_logs_view_api_requests', 'give_logs_view_api_requests' );

/**
 * Renders the Reports page views drop down
 *
 * @since 1.0
 * @return void
 */
function give_log_views() {
	$current_section = give_get_current_setting_section();

	// If there are not any event attach to action then do not show form.
	if ( ! has_action( 'give_log_view_actions' ) ) {
		return;
	}
	?>
	<form id="give-logs-filter" method="get" action="<?php echo 'edit.php?post_type=give_forms&page=give-tools&tab=logs&section=' . $current_section; ?>">
		<?php
		/**
		 * Fires after displaying the reports page views drop down.
		 *
		 * Allows you to add view actions.
		 *
		 * @since 1.0
		 */
		do_action( 'give_log_view_actions' );
		?>

		<input type="hidden" name="post_type" value="give_forms"/>
		<input type="hidden" name="page" value="give-tools"/>
		<input type="hidden" name="tab" value="logs"/>

		<?php submit_button( esc_html__( 'Apply', 'give' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
}
