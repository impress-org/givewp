<?php
/**
 * Give - Stripe Core | Stripe Log View Class
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 * @since      2.5.0
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
 * Give_Stripe_Log_Table List Table Class
 *
 * Renders the stripe log list table
 *
 * @since 2.5.8
 */
class Give_Stripe_Log_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var int
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => give_get_forms_label_singular(), // Singular name of the listed records.
				'plural'   => give_get_forms_label_plural(), // Plural name of the listed records.
				'ajax'     => false, // Does this table support ajax?
			)
		);
	}

	/**
	 * Show the search field
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  2.0.8
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'      => __( 'Log ID', 'give' ),
			'error'   => __( 'Error', 'give' ),
			'date'    => __( 'Date', 'give' ),
			'details' => __( 'Process Details', 'give' ),
		);

		return $columns;
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  2.5.0
	 *
	 * @param array  $item        Contains all the data of the discount code.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'ID':
				return sprintf(
					'<span class="give-item-label give-item-label-gray">%1$s</span>',
					esc_attr( $item[ $column_name ] )
				);
			case 'error':
				return esc_attr( $item['title'] );

			default:
				return esc_attr( $item[ $column_name ] );
		}
	}

	/**
	 * Output Error Message column
	 *
	 * @access public
	 * @since  2.5.0
	 *
	 * @param array $item Contains all the data of the log.
	 *
	 * @return void
	 */
	public function column_details( $item ) {
		echo wp_kses_post(
			Give()->tooltips->render_link(
				array(
					'label'       => __( 'View Stripe Log', 'give' ),
					'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
					'link'        => "#TB_inline?width=640&amp;inlineId=log-details-{$item['ID']}",
					'attributes'  => array(
						'class' => 'thickbox give-error-log-details-link button button-small',
					),
				)
			)
		);
		?>
		<div id="log-details-<?php echo esc_attr( $item['ID'] ); ?>" style="display:none;">
			<?php

			// Print Log Content, if not empty.
			if ( ! empty( $item['log_content'] ) ) {
				echo sprintf(
					'<p><pre>%1$s</pre></div>',
					esc_html( $item['log_content'] )
				);
			}
			?>
		</div>
		<?php
	}


	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see    : https://github.com/WordImpress/Give/issues/564
	 *
	 * @since  2.5.0
	 * @access protected
	 *
	 * @param string $which Top or bottom.
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
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  2.5.0
	 *
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Outputs the log views
	 *
	 * @param string $which Top or Bottom.
	 *
	 * @access public
	 * @since  2.0.8
	 *
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since  2.0.8
	 *
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'       => 'stripe',
			'paged'          => $paged,
			'posts_per_page' => $this->per_page,
		);

		$logs = Give()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'          => $log->ID,
					'title'       => $log->log_title,
					'date'        => $log->log_date,
					'log_content' => $log->log_content,
					'log_date'    => $log->log_date,
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  2.0.8
	 * @uses   Give_Stripe_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Stripe_Log_Table::get_pagenum()
	 * @uses   Give_Stripe_Log_Table::get_logs()
	 * @uses   Give_Stripe_Log_Table::get_log_count()
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns.
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = Give()->logs->get_log_count( 0, 'stripe' );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
