<?php
/**
 * Gateway Error Log View Class.
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give_Gateway_Error_Log_Table Class.
 *
 * Renders the gateway errors list table.
 *
 * @access      private
 * @since       1.0
 */
class Give_Gateway_Error_Log_Table extends WP_List_Table {

	/**
	 * Number of items per page.
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		// Set parent defaults.
		parent::__construct( array(
			'singular' => give_get_forms_label_singular(),    // Singular name of the listed records.
			'plural'   => give_get_forms_label_plural(),        // Plural name of the listed records.
			'ajax'     => false                        // Does this table support ajax?.
		) );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item Contains all the data of the log item.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name.
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'ID' :
				return $item['ID_label'];
			case 'payment_id' :
				return empty( $item['payment_id'] ) ? esc_html__( 'n/a', 'give' ) : sprintf( "<a href=\"%s\" target=\"_blank\">{$item['payment_id']}</a>", get_edit_post_link( $item['payment_id'] ) );
			case 'gateway' :
				return empty( $item['gateway'] ) ? esc_html__( 'n/a', 'give' ) : $item['gateway'];
			case 'error' :
				return get_the_title( $item['ID'] ) ? get_the_title( $item['ID'] ) : esc_html__( 'Payment Error', 'give' );
			default:
				return $item[ $column_name ];
		}
	}

	/**
	 * Output Error Message Column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $item Contains all the data of the log.
	 *
	 * @return void
	 */
	public function column_message( $item ) { ?>
		<a href="#TB_inline?width=640&amp;inlineId=log-message-<?php echo $item['ID']; ?>" class="thickbox give-error-log-details-link button button-small" data-tooltip="<?php esc_attr_e( 'View Log Message', 'give' ); ?>"><span class="dashicons dashicons-visibility"></span></a>
		<div id="log-message-<?php echo $item['ID']; ?>" style="display:none;">
			<?php

			$log_message = get_post_field( 'post_content', $item['ID'] );

			$serialized = strpos( $log_message, '{"' );

			// Check to see if the log message contains serialized information
			if ( $serialized !== false ) {
				$length = strlen( $log_message ) - $serialized;
				$intro  = substr( $log_message, 0, - $length );
				$data   = substr( $log_message, $serialized, strlen( $log_message ) - 1 );

				echo wpautop( $intro );
				echo wpautop( '<strong>' . esc_html__( 'Log data:', 'give' ) . '</strong>' );
				echo '<div style="word-wrap: break-word;">' . wpautop( $data ) . '</div>';
			} else {
				// No serialized data found
				echo wpautop( $log_message );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @access public
	 * @since  1.0
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'ID'         => esc_html__( 'Log ID', 'give' ),
			'error'      => esc_html__( 'Error', 'give' ),
			'gateway'    => esc_html__( 'Gateway', 'give' ),
			'payment_id' => esc_html__( 'Donation ID', 'give' ),
			'date'       => esc_html__( 'Date', 'give' ),
			'message'    => esc_html__( 'Details', 'give' )
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  1.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		give_log_views();
	}

	/**
	 * Gets the log entries for the current view.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $logs_data Array of all the Log entries.
	 */
	public function get_logs() {

		$give_logs = new Give_Logging();

		// Prevent the queries from getting cached.
		// Without this there are occasional memory issues for some installs.
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type' => 'gateway_error',
			'paged'    => $paged
		);

		$logs = $give_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'         => $log->ID,
					'ID_label'   => '<span class=\'give-item-label give-item-label-gray\'>' . $log->ID . '</span>',
					'payment_id' => $log->post_parent,
					'error'      => 'error',
					'gateway'    => give_get_payment_gateway( $log->post_parent ),
					'date'       => $log->post_date
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Display Tablenav (extended).
	 *
	 * Display the table navigation above or below the table even when no items in the logs,
	 * so nav doesn't disappear.
	 *
	 * @see: https://github.com/WordImpress/Give/issues/564
	 *
	 * @since 1.4.1
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @uses   Give_Gateway_Error_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Gateway_Error_Log_Table::get_pagenum()
	 * @uses   Give_Gateway_Error_Log_Table::get_logs()
	 * @uses   Give_Gateway_Error_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {

		$give_logs             = new Give_logging();
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = $give_logs->get_log_count( 0, 'gateway_error' );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}