<?php
/**
 * Donor Reports Table Class
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
 * Give_Donor_Reports_Table Class
 *
 * Renders the Donor Reports table
 *
 * @since 1.0
 */
class Give_Donor_Reports_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * Number of donors found
	 *
	 * @var int
	 * @since 1.0
	 */
	public $count = 0;

	/**
	 * Total donors
	 *
	 * @var int
	 * @since 1.0
	 */
	public $total = 0;

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
			'singular' => esc_html__( 'Donor', 'give' ),     // Singular name of the listed records
			'plural'   => esc_html__( 'Donors', 'give' ),    // Plural name of the listed records
			'ajax'     => false,// Does this table support ajax?
		) );

	}

	/**
	 * Remove default search field in favor for repositioned location
	 *
	 * Reposition the search field
	 *
	 * @since       1.0
	 * @access      public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return false
	 */
	public function search_box( $text, $input_id ) {
		return false;
	}

	/**
	 * Show the search field
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function give_search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box donor-search" role="search">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
	<?php
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
		<div class="tablenav give-clearfix <?php echo esc_attr( $which ); ?>">

			<?php if ( 'top' === $which ) { ?>
				<h2 class="alignleft reports-earnings-title">
					<?php esc_html_e( 'Donors Report', 'give' ); ?>
				</h2>
			<?php } ?>

			<div class="alignright tablenav-right">
				<div class="actions bulkactions">
					<?php
					if ( 'top' === $which ) {
						$this->give_search_box( esc_html__( 'Search Donors', 'give' ), 'give-donors-report-search' );
					}

					$this->bulk_actions( $which ); ?>

				</div>
				<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
				?>
			</div>


			<br class="clear"/>

		</div>
		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item        Contains all the data of the donors
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {

			case 'name' :
				$name = '#' . $item['id'] . ' ';
				$name .= ! empty( $item['name'] ) ? $item['name'] : '<em>' . esc_html__( 'Unnamed Donor', 'give' ) . '</em>';
				$view_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $item['id'] );
				$value    = '<a href="' . esc_url( $view_url ) . '">' . $name . '</a>';
				break;

			case 'num_donations' :
				$value = sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . absint( $item['id'] ) ),
						esc_html( $item['num_donations'] )
					);
				break;

			case 'amount_spent' :
				$value = give_currency_filter( give_format_amount( $item[ $column_name ], array( 'sanitize' => false ) ) );
				break;

			default:
				$value = isset( $item[ $column_name ] ) ? $item[ $column_name ] : null;
				break;
		}

		return apply_filters( "give_report_column_{$column_name}", $value, $item['id'] );
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
			'name'          => __( 'Name', 'give' ),
			'email'         => __( 'Email', 'give' ),
			'num_donations' => __( 'Donations', 'give' ),
			'amount_spent'  => __( 'Total Donated', 'give' ),
		);

		return apply_filters( 'give_report_donor_columns', $columns );

	}

	/**
	 * Get the sortable columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'id'            => array( 'id', true ),
			'name'          => array( 'name', true ),
			'num_donations' => array( 'purchase_count', false ),
			'amount_spent'  => array( 'purchase_value', false ),
		);
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
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since  1.0
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Build all the reports data
	 *
	 * @access public
	 * @since  1.0
	 * @global object $wpdb Used to query the database using the WordPress
	 *                      Database API
	 * @return array $reports_data All the data for donor reports
	 */
	public function reports_data() {
		global $wpdb;

		$data = array();

		// Get donor query.
		$args   = $this->get_donor_query();
		$donors = Give()->donors->get_donors( $args );

		if ( $donors ) {

			$this->count = count( $donors );

			foreach ( $donors as $donor ) {

				$user_id = ! empty( $donor->user_id ) ? absint( $donor->user_id ) : 0;

				$data[] = array(
					'id'            => $donor->id,
					'user_id'       => $user_id,
					'name'          => $donor->name,
					'email'         => $donor->email,
					'num_donations' => $donor->purchase_count,
					'amount_spent'  => $donor->purchase_value,
				);
			}
		}

		return $data;
	}

	/**
	 * Get donor count.
	 *
	 * @since 1.8.1
	 * @access private
	 */
	private function get_donor_count() {
		// Get donor query.
		$_donor_query = $this->get_donor_query();

		$_donor_query['number'] = -1;
		$_donor_query['offset'] = 0;
		$donors = Give()->donors->get_donors( $_donor_query );

		return count( $donors );
	}

	/**
	 * Get donor query.
	 *
	 * @since  1.8.1
	 * @access public
	 * @return array
	 */
	public function get_donor_query() {
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args = array(
			'number'  => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
		);

		if( $search ) {
			if ( is_email( $search ) ) {
				$args['email'] = $search;
			} elseif ( is_numeric( $search ) ) {
				$args['id'] = $search;
			} else {
				$args['name'] = $search;
			}
		}

		return $args;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Donor_Reports_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Donor_Reports_Table::get_pagenum()
	 * @uses   Give_Donor_Reports_Table::get_total_donors()
	 * @return void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->reports_data();

		$this->total = $this->get_donor_count();

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page ),
		) );
	}
}
