<?php
/**
 * Download Reports Table Class
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

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give_Form_Reports_Table Class
 *
 * Renders the Form Reports table
 *
 * @since 1.0
 */
class Give_Form_Reports_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 1.0
	 */
	private $products;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular' => give_get_forms_label_singular(),    // Singular name of the listed records
			'plural'   => give_get_forms_label_plural(),        // Plural name of the listed records
			'ajax'     => false                        // Does this table support ajax?
		) );

		add_action( 'give_report_view_actions', array( $this, 'category_filter' ) );
		$this->query();

	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item        Contains all the data of the downloads
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'earnings' :
				return give_currency_filter( give_format_amount( $item[ $column_name ] ) );
			case 'average_sales' :
				return round( $item[ $column_name ] );
			case 'average_earnings' :
				return give_currency_filter( give_format_amount( $item[ $column_name ] ) );
			case 'details' :
				return '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-reports&view=forms&form-id=' . $item['ID'] ) . '">' . __( 'View Detailed Report', 'give' ) . '</a>';
			default:
				return $item[ $column_name ];
		}
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
			'title'            => give_get_forms_label_singular(),
			'sales'            => __( 'Donations', 'give' ),
			'earnings'         => __( 'Income', 'give' ),
			'average_sales'    => __( 'Monthly Average Donations', 'give' ),
			'average_earnings' => __( 'Monthly Average Income', 'give' ),
			'details'          => __( 'Detailed Report', 'give' )
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'title'    => array( 'title', true ),
			'sales'    => array( 'sales', false ),
			'earnings' => array( 'earnings', false ),
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
	 * Retrieve the category being viewed
	 *
	 * @access public
	 * @since  1.0
	 * @return int Category ID
	 */
	public function get_category() {
		return isset( $_GET['category'] ) ? absint( $_GET['category'] ) : 0;
	}


	/**
	 * Retrieve the total number of forms
	 *
	 * @access public
	 * @since  1.0
	 * @return int $total Total number of donation forms
	 */
	public function get_total_forms() {
		$total  = 0;
		$counts = wp_count_posts( 'give_forms', 'readable' );
		foreach ( $counts as $status => $count ) {
			$total += $count;
		}

		return $total;
	}

	/**
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		give_report_views();
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

		if ( 'top' == $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>
		<div class="tablenav give-clearfix <?php echo esc_attr( $which ); ?>">

			<h3 class="alignleft reports-earnings-title"><span><?php _e( 'Form Report', 'give' ); ?></span></h3>

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
	 * Attaches the category filter to the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function category_filter() {

		$categories = get_terms( 'form_category' );
		if ( $categories && ! is_wp_error( $categories ) ) {
			echo Give()->html->category_dropdown( 'category', $this->get_category() );
		}
	}


	/**
	 * Performs the products query
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function query() {

		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$category = $this->get_category();

		$args = array(
			'post_type'        => 'give_forms',
			'post_status'      => 'publish',
			'order'            => $order,
			'fields'           => 'ids',
			'posts_per_page'   => $this->per_page,
			'paged'            => $this->get_paged(),
			'suppress_filters' => true
		);

		if ( ! empty( $category ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'form_category',
					'terms'    => $category
				)
			);
		}

		switch ( $orderby ) :
			case 'title' :
				$args['orderby'] = 'title';
				break;

			case 'sales' :
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_give_form_sales';
				break;

			case 'earnings' :
				$args['orderby']  = 'meta_value_num';
				$args['meta_key'] = '_give_form_earnings';
				break;
		endswitch;

		$args = apply_filters( 'give_form_reports_prepare_items_args', $args, $this );

		$this->products = new WP_Query( $args );

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

		$give_forms = $this->products->posts;

		if ( $give_forms ) {
			foreach ( $give_forms as $form ) {
				$reports_data[] = array(
					'ID'               => $form,
					'title'            => get_the_title( $form ),
					'sales'            => give_get_form_sales_stats( $form ),
					'earnings'         => give_get_form_earnings_stats( $form ),
					'average_sales'    => give_get_average_monthly_form_sales( $form ),
					'average_earnings' => give_get_average_monthly_form_earnings( $form )
				);
			}
		}

		return $reports_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.5
	 * @uses   Give_Form_Reports_Table::get_columns()
	 * @uses   Give_Form_Reports_Table::get_sortable_columns()
	 * @uses   Give_Form_Reports_Table::reports_data()
	 * @uses   Give_Form_Reports_Table::get_pagenum()
	 * @uses   Give_Form_Reports_Table::get_total_forms()
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->reports_data();

		$total_items = $this->get_total_forms();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}
