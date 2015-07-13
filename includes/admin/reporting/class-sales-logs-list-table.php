<?php
/**
 * Sales Log View Class
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
 * Give_Sales_Log_Table Class
 *
 * Renders the sales log list table
 *
 * @since 1.0
 */
class Give_Sales_Log_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @since 1.0
	 * @var int
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
		parent::__construct( array(
			'singular' => give_get_forms_label_singular(),    // Singular name of the listed records
			'plural'   => give_get_forms_label_plural(),        // Plural name of the listed records
			'ajax'     => false                        // Does this table support ajax?
		) );

		add_action( 'give_log_view_actions', array( $this, 'give_forms_filter' ) );
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
			case 'form' :
				return '<a href="' . esc_url( add_query_arg( 'form', $item[ $column_name ] ) ) . '" >' . get_the_title( $item[ $column_name ] ) . '</a>';

			case 'user_id' :
				return '<a href="' .
				       admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&user=' . ( ! empty( $item['user_id'] ) ? urlencode( $item['user_id'] ) : give_get_payment_user_email( $item['payment_id'] ) ) ) .
				       '">' . $item['user_name'] . '</a>';

			case 'amount' :
				return give_currency_filter( give_format_amount( $item['amount'] ) );

			case 'payment_id' :
				return '<a href="' . admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details&id=' . $item['payment_id'] ) . '">' . give_get_payment_number( $item['payment_id'] ) . '</a>';

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
			'ID'         => __( 'Log ID', 'give' ),
			'user_id'    => __( 'User', 'give' ),
			'form'       => give_get_forms_label_singular(),
			'amount'     => __( 'Item Amount', 'give' ),
			'payment_id' => __( 'Payment ID', 'give' ),
			'date'       => __( 'Date', 'give' )
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
	 * Retrieves the user we are filtering logs by, if any
	 *
	 * @access public
	 * @since  1.0
	 * @return mixed int If User ID, string If Email/Login
	 */
	public function get_filtered_user() {
		return isset( $_GET['user'] ) ? absint( $_GET['user'] ) : false;
	}

	/**
	 * Retrieves the ID of the give_form we're filtering logs by
	 *
	 * @access public
	 * @since  1.0
	 * @return int Download ID
	 */
	public function get_filtered_give_form() {
		return ! empty( $_GET['form'] ) ? absint( $_GET['form'] ) : false;
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
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query, user query, or form query
	 *
	 * @access public
	 * @since  1.0
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		$user = $this->get_filtered_user();

		$meta_query = array();

		if ( $user ) {
			// Show only logs from a specific user
			$meta_query[] = array(
				'key'   => '_give_log_user_id',
				'value' => $user
			);
		}

		$search = $this->get_search();
		if ( $search ) {
			if ( is_email( $search ) ) {
				// This is an email search. We use this to ensure it works for guest users and logged-in users
				$key     = '_give_log_user_info';
				$compare = 'LIKE';
			} else {
				// Look for a user
				$key     = '_give_log_user_id';
				$compare = 'LIKE';

				if ( ! is_numeric( $search ) ) {
					// Searching for user by username
					$user = get_user_by( 'login', $search );

					if ( $user ) {
						// Found one, set meta value to user's ID
						$search = $user->ID;
					} else {
						// No user found so let's do a real search query
						$users = new WP_User_Query( array(
							'search'         => $search,
							'search_columns' => array( 'user_url', 'user_nicename' ),
							'number'         => 1,
							'fields'         => 'ids'
						) );

						$found_user = $users->get_results();

						if ( $found_user ) {
							$search = $found_user[0];
						}
					}
				}
			}

			if ( ! $this->file_search ) {
				// Meta query only works for non file name searche
				$meta_query[] = array(
					'key'     => $key,
					'value'   => $search,
					'compare' => $compare
				);

			}
		}

		return $meta_query;
	}

	/**
	 * Outputs the log views
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place
		give_log_views();
	}

	/**
	 * Sets up the forms filter
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function give_forms_filter() {
		$give_forms = get_posts( array(
			'post_type'              => 'give_forms',
			'post_status'            => 'any',
			'posts_per_page'         => - 1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false
		) );

		if ( $give_forms ) {
			echo '<select name="form" id="give-log-form-filter">';
			echo '<option value="0">' . __( 'All', 'give' ) . '</option>';
			foreach ( $give_forms as $form ) {
				echo '<option value="' . $form . '"' . selected( $form, $this->get_filtered_give_form() ) . '>' . esc_html( get_the_title( $form ) ) . '</option>';
			}
			echo '</select>';
		}
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @access public
	 * @since  1.0
	 * @global object $give_logs Give Logs Object
	 * @return array $logs_data Array of all the Log entires
	 */
	public function get_logs() {
		global $give_logs;

		// Prevent the queries from getting cached. Without this there are occasional memory issues for some installs
		wp_suspend_cache_addition( true );

		$logs_data = array();
		$paged     = $this->get_paged();
		$give_form = empty( $_GET['s'] ) ? $this->get_filtered_give_form() : null;
		$user      = $this->get_filtered_user();

		$log_query = array(
			'post_parent' => $give_form,
			'log_type'    => 'sale',
			'paged'       => $paged,
			'meta_query'  => $this->get_meta_query()
		);

		$logs = $give_logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				$payment_id = get_post_meta( $log->ID, '_give_log_payment_id', true );

				// Make sure this payment hasn't been deleted
				if ( get_post( $payment_id ) ) :
					$user_info      = give_get_payment_meta_user_info( $payment_id );
					$payment_meta   = give_get_payment_meta( $payment_id );
					$payment_amount = give_get_payment_amount( $payment_id );

					$logs_data[] = array(
						'ID'         => '<span class="give-item-label give-item-label-gray">' . $log->ID . '</span>',
						'payment_id' => $payment_id,
						'form'       => $log->post_parent,
						'amount'     => $payment_amount,
						'user_id'    => $user_info['id'],
						'user_name'  => $user_info['first_name'] . ' ' . $user_info['last_name'],
						'date'       => get_post_field( 'post_date', $payment_id )
					);

				endif;
			}
		}

		return $logs_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 * @global object $give_logs Give Logs Object
	 * @uses   Give_Sales_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Sales_Log_Table::get_pagenum()
	 * @uses   Give_Sales_Log_Table::get_logs()
	 * @uses   Give_Sales_Log_Table::get_log_count()
	 * @return void
	 */
	public function prepare_items() {
		global $give_logs;

		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = $give_logs->get_log_count( $this->get_filtered_give_form(), 'sale', $this->get_meta_query() );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}
