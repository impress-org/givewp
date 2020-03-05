<?php
/**
 * API Requests Log View Class
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
 * Give_API_Request_Log_Table List Table Class
 *
 * Renders the gateway errors list table
 *
 * @since 1.0
 */
class Give_API_Request_Log_Table extends WP_List_Table {
	/**
	 * Number of items per page
	 *
	 * @var int
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
				'ajax'     => false, // Does this table support ajax?
			)
		);
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
	public function search_box( $text, $input_id ) {
		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box" role="search">
			<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id; ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'ID'      => __( 'Log ID', 'give' ),
			'ip'      => __( 'Request IP', 'give' ),
			'date'    => __( 'Date', 'give' ),
			'details' => __( 'Request Details', 'give' ),
		);

		return $columns;
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array  $item        Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			default:
				return esc_attr( $item[ $column_name ] );
		}
	}

	/**
	 * Output Error Message column
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $item Contains all the data of the log
	 *
	 * @return void
	 */
	public function column_details( $item ) {
		echo Give()->tooltips->render_link(
			array(
				'label'       => __( 'View Request', 'give' ),
				'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
				'link'        => "#TB_inline?width=640&amp;inlineId=log-details-{$item['ID']}",
				'attributes'  => array(
					'class' => 'thickbox give-error-log-details-link button button-small',
				),
			)
		);
		?>
		<div id="log-details-<?php echo $item['ID']; ?>" style="display:none;">
			<?php
			// Print API Request.
			echo sprintf(
				'<p><strong>%1$s</strong></p><div>%2$s</div>',
				__( 'API Request:', 'give' ),
				Give()->logs->logmeta_db->get_meta( $item['ID'], '_give_log_api_query', true )
			);

			// Print Log Content, if not empty.
			if ( ! empty( $item['log_content'] ) ) {
				echo sprintf(
					'<p><strong>%1$s</strong></p><div>%2$s</div>',
					__( 'Error', 'give' ),
					esc_html( $item['log_content'] )
				);
			}

			// Print User who requested data using API.
			echo sprintf(
				'<p><strong>%1$s</strong></p><div>%2$s</div>',
				__( 'API User:', 'give' ),
				Give()->logs->logmeta_db->get_meta( $item['ID'], '_give_log_user', true )
			);

			// Print the logged key used by API.
			echo sprintf(
				'<p><strong>%1$s</strong></p><div>%2$s</div>',
				__( 'API Key:', 'give' ),
				Give()->logs->logmeta_db->get_meta( $item['ID'], '_give_log_key', true )
			);

			// Print the API Request Date.
			echo sprintf(
				'<p><strong>%1$s</strong></p><div>%2$s</div>',
				__( 'Request Date:', 'give' ),
				$item['log_date']
			);
			?>
		</div>
		<?php
	}

	/**
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string|bool String if search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}


	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see    : https://github.com/impress-org/give/issues/564
	 *
	 * @since  1.4.1
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
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $meta_query
	 */
	function get_meta_query() {

		$meta_query = array();
		$search     = $this->get_search();

		if ( $search ) {
			if ( filter_var( $search, FILTER_VALIDATE_IP ) ) {

				// This is an IP address search.
				$key = '_give_log_request_ip';

			} elseif ( is_email( $search ) ) {

				// This is an email search.
				$userdata = get_user_by( 'email', $search );

				if ( $userdata ) {
					$search = $userdata->ID;
				}

				$key = '_give_log_user';

			} elseif ( 32 === strlen( $search ) ) {

				// Look for an API key.
				$key = '_give_log_key';

			} elseif ( stristr( $search, 'token:' ) ) {

				// Look for an API token.
				$search = str_ireplace( 'token:', '', $search );
				$key    = '_give_log_token';

			} else {

				// This is (probably) a user ID search.
				$userdata = get_userdata( $search );

				if ( $userdata ) {
					$search = $userdata->ID;
				}

				$key = '_give_log_user';

			}

			// Setup the meta query.
			$meta_query[] = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => '=',
			);
		}

		return $meta_query;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  1.0
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
	 * @since  1.0
	 *
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		give_log_views();
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'       => 'api_request',
			'paged'          => $paged,
			'meta_query'     => $this->get_meta_query(),
			'posts_per_page' => $this->per_page,
		);

		$logs = Give()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'          => $log->ID,
					'ip'          => Give()->logs->logmeta_db->get_meta( $log->ID, '_give_log_request_ip', true ),
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
	 * @since  1.0
	 * @uses   Give_API_Request_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_API_Request_Log_Table::get_pagenum()
	 * @uses   Give_API_Request_Log_Table::get_logs()
	 * @uses   Give_API_Request_Log_Table::get_log_count()
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = Give()->logs->get_log_count( 0, 'api_request', $this->get_meta_query() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
