<?php
/**
 * Spam Log View Class
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2020, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.5.14
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
 * Give_Spam_Log_Table List Table Class
 *
 * Renders the gateway errors list table
 *
 * @since 2.5.14
 */
class Give_Spam_Log_Table extends WP_List_Table {
	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 2.5.14
	 */
	public $per_page = 30;

	/**
	 * Get things started
	 *
	 * @since 2.5.14
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
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
	 * @since  2.5.14
	 * @access public
	 *
	 * @param string $text     Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		$input_id = "{$input_id}-search-input";

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
	 * @return array $columns Array of all the list table columns
	 * @since  2.5.14
	 */
	public function get_columns() {
		return array(
			'ID'      => __( 'Log ID', 'give' ),
			'error'   => __( 'Error', 'give' ),
			'date'    => __( 'Date', 'give' ),
			'details' => __( 'Log Details', 'give' ),
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 *
	 * @param array  $item        Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 * @since  2.5.14
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'error':
				$action      = 'akismet_deblacklist_spammed_email';
				$donor_email = Give()->logmeta_db->get_meta( $item['ID'], 'donor_email', true );

				return str_replace(
					'#noncelink',
					add_query_arg(
						array(
							'give-action' => $action,
							'email'       => $donor_email,
							'log'         => $item['ID'],
							'_wpnonce'    => wp_create_nonce( "give_{$action}_{$donor_email}" ),
						),
						admin_url()
					),
					$item[ $column_name ]
				);

			default:
				return esc_attr( $item[ $column_name ] );
		}
	}

	/**
	 * Output Error Message column
	 *
	 * @access public
	 *
	 * @param array $item Contains all the data of the log
	 *
	 * @return void
	 * @since  2.5.14
	 */
	public function column_details( $item ) {
		echo Give()->tooltips->render_link(
			array(
				'label'       => __( 'View Log Details', 'give' ),
				'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
				'link'        => "#TB_inline?width=640&amp;inlineId=log-details-{$item['ID']}",
				'attributes'  => array(
					'class' => 'thickbox give-error-log-details-link button button-small',
				),
			)
		);
		?>
		<div id="log-details-<?php echo $item['ID']; ?>" style="display:none;">
			<?php echo $item['log_content']; ?>
		</div>
		<?php
	}

	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see    : https://github.com/impress-org/give/issues/564
	 *
	 * @since  2.5.14
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
	 * Retrieves the search query string
	 *
	 * @access public
	 * @since  2.5.14
	 *
	 * @return string|bool String if search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @return int Current page number
	 * @since  2.5.14
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query
	 *
	 * @access public
	 * @since  2.5.14
	 *
	 * @return array $meta_query
	 */
	public function get_meta_query() {

		$meta_query = array( 'relation' => 'OR' );
		$search     = $this->get_search();

		if ( $search ) {
			$meta_query[] = array(
				'key'   => 'donor_email',
				'value' => $search,
			);
		}

		return $meta_query;
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @return array $logs_data Array of all the Log entires
	 * @since  2.5.14
	 */
	public function get_logs() {
		$logs_data = array();
		$paged     = $this->get_paged();
		$log_query = array(
			'log_type'       => 'spam',
			'paged'          => $paged,
			'meta_query'     => $this->get_meta_query(),
			'posts_per_page' => $this->per_page,
		);

		$logs = Give()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {

				$logs_data[] = array(
					'ID'          => $log->ID,
					'date'        => $log->log_date,
					'log_content' => wp_kses(
						$log->log_content,
						array(
							'p'      => array(),
							'pre'    => array(),
							'strong' => array(),
						)
					),
					'error'       => wp_kses(
						$log->log_title,
						array(
							'p'      => array(),
							'strong' => array(),
							'a'      => array(
								'href'   => array(),
								'title'  => array(),
								'target' => array(),
							),
						)
					),
				);
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @return void
	 * @uses   Give_Spam_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Spam_Log_Table::get_pagenum()
	 * @uses   Give_Spam_Log_Table::get_logs()
	 * @uses   Give_Spam_Log_Table::get_log_count()
	 *
	 * @since  2.5.14
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array(); // No hidden columns
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_logs();
		$total_items           = Give()->logs->get_log_count( 0, 'spam', $this->get_meta_query() );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
