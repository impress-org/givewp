<?php
/**
 * Logs UI
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
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
		<?php do_action( 'give_logs_api_requests_top' ); ?>
		<form id="give-logs-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-reports&tab=logs' ); ?>">
			<?php
			$logs_table->search_box( __( 'Search', 'give' ), 'give-api-requests' );
			$logs_table->display();
			?>
			<input type="hidden" name="post_type" value="give_forms"/>
			<input type="hidden" name="page" value="give-reports"/>
			<input type="hidden" name="tab" value="logs"/>
		</form>
		<?php do_action( 'give_logs_api_requests_bottom' ); ?>
	</div>
	<?php
}

add_action( 'give_logs_view_api_requests', 'give_logs_view_api_requests' );


/**
 * Default Log Views
 *
 * @since 1.0
 * @return array $views Log Views
 */
function give_log_default_views() {
	$views = array(
		'sales'          => __( 'Donations', 'give' ),
		'gateway_errors' => __( 'Payment Errors', 'give' ),
		'api_requests'   => __( 'API Requests', 'give' )
	);

	$views = apply_filters( 'give_log_views', $views );

	return $views;
}

/**
 * Renders the Reports page views drop down
 *
 * @since 1.0
 * @return void
 */
function give_log_views() {
	$views        = give_log_default_views();
	$current_view = isset( $_GET['view'] ) && array_key_exists( $_GET['view'], give_log_default_views() ) ? sanitize_text_field( $_GET['view'] ) : 'sales';
	?>
	<form id="give-logs-filter" method="get" action="edit.php">
		<select id="give-logs-view" name="view">
			<optgroup label="Log Type:">
				<?php foreach ( $views as $view_id => $label ): ?>
					<option value="<?php echo esc_attr( $view_id ); ?>" <?php selected( $view_id, $current_view ); ?>><?php echo $label; ?></option>
				<?php endforeach; ?>
			</optgroup>
		</select>

		<?php do_action( 'give_log_view_actions' ); ?>

		<input type="hidden" name="post_type" value="give_forms"/>
		<input type="hidden" name="page" value="give-reports"/>
		<input type="hidden" name="tab" value="logs"/>

		<?php submit_button( __( 'Apply', 'give' ), 'secondary', 'submit', false ); ?>
	</form>
	<?php
}