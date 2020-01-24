<?php
/**
 * Logs UI
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the logs tab.
 *
 * @since 1.0
 * @return void
 */
function give_get_logs_tab() {

	require GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/logs.php';

	// Get current section.
	$current_section = $_GET['section'] = give_get_current_setting_section();

	/**
	 * Fires the in report page logs view.
	 *
	 * @since 1.0
	 */
	do_action( "give_logs_view_{$current_section}" );
}

/**
 * Update Logs
 *
 * @since 2.0.1
 *
 * @return void
 */
function give_logs_view_updates() {
	include GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-update-logs-list-table.php';

	$logs_table = new Give_Update_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="give-log-wrap">

		<?php
		/**
		 * Fires before displaying Payment Error logs.
		 *
		 * @since 2.0.1
		 */
		do_action( 'give_logs_update_top' );

		$logs_table->display();
		?>
		<input type="hidden" name="post_type" value="give_forms"/>
		<input type="hidden" name="page" value="give-tools"/>
		<input type="hidden" name="tab" value="logs"/>
		<input type="hidden" name="section" value="update"/>

		<?php
		/**
		 * Fires after displaying update logs.
		 *
		 * @since 2.0.1
		 */
		do_action( 'give_logs_update_bottom' );
		?>

	</div>
	<?php
}

add_action( 'give_logs_view_updates', 'give_logs_view_updates' );

/**
 * Gateway Error Logs
 *
 * @since 1.0
 * @uses  Give_File_Downloads_Log_Table::prepare_items()
 * @uses  Give_File_Downloads_Log_Table::display()
 * @return void
 */
function give_logs_view_gateway_errors() {
	include GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-gateway-error-logs-list-table.php';

	$logs_table = new Give_Gateway_Error_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="give-log-wrap">

		<?php
		/**
		 * Fires before displaying Payment Error logs.
		 *
		 * @since 1.8.12
		 */
		do_action( 'give_logs_payment_error_top' );

		$logs_table->display();
		?>
		<input type="hidden" name="post_type" value="give_forms"/>
		<input type="hidden" name="page" value="give-tools"/>
		<input type="hidden" name="tab" value="logs"/>
		<input type="hidden" name="section" value="gateway_errors"/>

		<?php
		/**
		 * Fires after displaying Payment Error logs.
		 *
		 * @since 1.8.12
		 */
		do_action( 'give_logs_payment_error_bottom' );
		?>

	</div>
	<?php
}

add_action( 'give_logs_view_gateway_errors', 'give_logs_view_gateway_errors' );

/**
 * API Request Logs
 *
 * @since 1.0
 * @uses  Give_API_Request_Log_Table::prepare_items()
 * @uses  Give_API_Request_Log_Table::display()
 * @return void
 */
function give_logs_view_api_requests() {
	include GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-api-requests-logs-list-table.php';

	$logs_table = new Give_API_Request_Log_Table();
	$logs_table->prepare_items();

	/**
	 * Fires before displaying API requests logs.
	 *
	 * @since 1.0
	 */
	do_action( 'give_logs_api_requests_top' );

	$logs_table->search_box( esc_html__( 'Search', 'give' ), 'give-api-requests' );
	$logs_table->display();
	?>
	<input type="hidden" name="post_type" value="give_forms"/>
	<input type="hidden" name="page" value="give-tools"/>
	<input type="hidden" name="tab" value="logs"/>
	<input type="hidden" name="section" value="api_requests"/>

	<?php
	/**
	 * Fires after displaying API requests logs.
	 *
	 * @since 1.0
	 */
	do_action( 'give_logs_api_requests_bottom' );
}
add_action( 'give_logs_view_api_requests', 'give_logs_view_api_requests' );

/**
 * Spam Logs
 *
 * @since 2.5.14
 * @uses  Give_Spam_Log_Table::prepare_items()
 * @uses  Give_Spam_Log_Table::display()
 * @return void
 */
function give_logs_view_spam() {
	include GIVE_PLUGIN_DIR . 'includes/admin/tools/logs/class-spam-logs-list-table.php';

	$logs_table = new Give_Spam_Log_Table();
	$logs_table->prepare_items();
	?>
	<div class="give-log-wrap">

		<?php
		/**
		 * Fires before displaying spam logs.
		 *
		 * @since 2.5.14
		 */
		do_action( 'give_logs_spam_top' );

		$logs_table->search_box( esc_html__( 'Search', 'give' ), 'give-api-requests' );
		$logs_table->display();
		?>
		<input type="hidden" name="post_type" value="give_forms"/>
		<input type="hidden" name="page" value="give-tools"/>
		<input type="hidden" name="tab" value="logs"/>
		<input type="hidden" name="section" value="spam"/>

		<?php
		/**
		 * Fires after displaying spam logs.
		 *
		 * @since 2.5.14
		 */
		do_action( 'give_logs_spam_bottom' );
		?>

	</div>
	<?php
}

add_action( 'give_logs_view_spam', 'give_logs_view_spam' );

/**
 * Renders the log views drop down.
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

/**
 * Set Get form method for tools page.
 *
 * Prevents Tools from displaying a "Settings Saved" notice.
 *
 * @since 1.8.12
 *
 * @return string
 */
function give_tools_set_form_method( $method ) {
	return 'get';
}
add_filter( 'give-tools_form_method_tab_logs', 'give_tools_set_form_method', 10 );
