<?php
/**
 * Gateways Reports Table Class
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

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give_Gateway_Reports_Table Class
 *
 * Renders the Download Reports table
 *
 * @since 1.0
 */
class Give_Gateway_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0
	 */
	public $per_page = 30;


	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => give_get_forms_label_singular(),    // Singular name of the listed records
				'plural'   => give_get_forms_label_plural(),        // Plural name of the listed records
				'ajax'     => false,                        // Does this table support ajax?
			)
		);

	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item        Contains all the data of the form
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$donation_list_page_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );

		switch ( $column_name ) {
			case 'complete_sales':
				$value = $item[ $column_name ] ?
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
								'status'  => 'publish',
								'gateway' => $item['ID'],
							),
							$donation_list_page_url
						),
						$item[ $column_name ]
					) :
					$item[ $column_name ];
				break;

			case 'pending_sales':
				$value = $item[ $column_name ] ?
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
								'status'  => 'pending',
								'gateway' => $item['ID'],
							),
							$donation_list_page_url
						),
						$item[ $column_name ]
					) :
					$item[ $column_name ];
				break;

			case 'total_sales':
				$value = $item[ $column_name ] ?
					sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(
							array(
								'gateway' => $item['ID'],
							),
							$donation_list_page_url
						),
						$item[ $column_name ]
					) :
					$item[ $column_name ];

				break;

			default:
				$value = $item[ $column_name ];
		}

		return $value;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'label'           => esc_attr__( 'Gateway', 'give' ),
			'complete_sales'  => esc_attr__( 'Complete Payments', 'give' ),
			'pending_sales'   => esc_attr__( 'Pending / Failed Payments', 'give' ),
			'total_sales'     => esc_attr__( 'Total Payments', 'give' ),
			'total_donations' => esc_attr__( 'Total Donated', 'give' ),
		);

		return $columns;
	}

	/**
	 * Get the sortable columns
	 *
	 * @access public
	 * @since  1.8.12
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'total_donations' => array( 'total_donations', false ),
		);
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
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {

	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since  1.0
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav gateways-report-tablenav give-clearfix <?php echo esc_attr( $which ); ?>">

			<?php if ( 'top' === $which ) { ?>
				<h2 class="alignleft reports-earnings-title screen-reader-text">
					<?php _e( 'Donation Methods Report', 'give' ); ?>
				</h2>
			<?php } ?>

			<div class="alignright tablenav-right">
				<div class="actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
				?>
			</div>


			<br class="clear" />

		</div>
		<?php
	}

	/**
	 * Reorder User Defined Array
	 *
	 * @param $old_value
	 * @param $new_value
	 *
	 * @access public
	 * @since  1.8.12
	 *
	 * @return int
	 */
	public function give_sort_total_donations( $old_value, $new_value ) {
		// If no sort, default to label.
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'label';

		// If no order, default to asc.
		$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'asc';

		// Determine sort order.
		$result = strcmp( $old_value[ $orderby ], $new_value[ $orderby ] );

		return ( $order === 'asc' ) ? $result : -$result;
	}


	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since  1.0
	 * @return array $reports_data All the data for donor reports
	 */
	public function reports_data() {

		$reports_data = array();
		$gateways     = give_get_payment_gateways();
		$stats        = new Give_Payment_Stats();

		foreach ( $gateways as $gateway_id => $gateway ) {

			$complete_count = give_count_sales_by_gateway( $gateway_id, 'publish' );
			$pending_count  = give_count_sales_by_gateway( $gateway_id, array( 'pending', 'failed' ) );

			$reports_data[] = array(
				'ID'              => $gateway_id,
				'label'           => $gateway['admin_label'],
				'complete_sales'  => $complete_count,
				'pending_sales'   => $pending_count,
				'total_sales'     => $complete_count + $pending_count,
				'total_donations' => give_currency_filter( give_format_amount( $stats->get_earnings( 0, strtotime( '04/13/2015' ), current_time( 'timestamp' ), $gateway_id ), array( 'sanitize' => false ) ) ),
			);
		}

		return $reports_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Gateway_Reports_Table::get_columns()
	 * @uses   Give_Gateway_Reports_Table::get_sortable_columns()
	 * @uses   Give_Gateway_Reports_Table::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->reports_data();

		// Sort Array when we are sorting data in array.
		usort( $this->items, array( $this, 'give_sort_total_donations' ) );

	}
}
