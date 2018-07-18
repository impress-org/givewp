<?php
/**
 * Sales Log View Class
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
			'ajax'     => false,// Does this table support ajax?
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

		/* @var Give_Payment $payment */
		$payment = give_get_payment_by( 'id', $item['payment_id'] );

		switch ( $column_name ) {
			case 'form' :
				$form_title = get_the_title( $item[ $column_name ] );
				$form_title = empty( $form_title ) ? sprintf( __( 'Untitled (#%s)', 'give' ), $item[ $column_name ] ) : $form_title;
				return '<a href="' . esc_url( add_query_arg( 'form', $item[ $column_name ] ) ) . '" >' . esc_html( $form_title ). '</a>';

			case 'amount' :
				$value = give_currency_filter( give_format_amount( $item['amount'], array( 'sanitize' => false, 'donation_id' => $item['payment_id'] ) ), array( 'currency_code' => give_get_payment_currency_code( $item['payment_id'] ) ) );
				$value .= sprintf( '<br><small>%1$s %2$s</small>', __( 'via', 'give' ), give_get_gateway_admin_label( $payment->gateway ) );

				return $value;

			case 'status' :

				$value = '<div class="give-donation-status status-' . sanitize_title( give_get_payment_status( $payment, true ) ) . '"><span class="give-donation-status-icon"></span> ' . give_get_payment_status( $payment, true ) . '</div>';

				if ( $payment->mode == 'test' ) {
					$value .= Give()->tooltips->render_span( array(
						'label'       => __( 'This donation was made in test mode.', 'give' ),
						'tag_content' => __( 'Test', 'give' ),
						'attributes'  => array(
							'class' => 'give-item-label give-item-label-orange give-test-mode-transactions-label',
						),
					) );
				}

				return $value;

			case 'donation' :
				$serial_code = Give()->seq_donation_number->get_serial_code( $payment );
				$value = Give()->tooltips->render_link( array(
					/* translators: %s Sequential Donation ID. */
					'label'       => sprintf( __( 'View Donation %s', 'give' ), $serial_code ),
					'tag_content' => $serial_code,
					'link'        => esc_url( add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details' ) ) ),
				) );

				if ( ! empty( $item['donor_id'] ) ) {
					$title_prefix = Give()->donor_meta->get_meta( $item['donor_id'], '_give_donor_title_prefix', true );

					$value .= sprintf(
						'&nbsp;%1$s&nbsp;<a href="%2$s">%3$s</a><br>',
						esc_html__( 'by', 'give' ),
						admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . $item['donor_id'] ),
						give_get_donor_name_with_title_prefixes( $title_prefix, $item['donor_name'] )
					);
				} else {
					$value .= sprintf(
						'&nbsp;%1$s&nbsp;%2$s<br>',
						esc_html__( 'by', 'give' ),
						__( 'No donor attached', 'give' )
					);
				}

				return $value;

			default:
				return $item[ $column_name ];
		} // End switch().
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
			'ID'       => __( 'Log ID', 'give' ),
			'donation' => __( 'Donation', 'give' ),
			'form'     => __( 'Form', 'give' ),
			'status'   => __( 'Status', 'give' ),
			'amount'   => __( 'Donation Amount', 'give' ),
			'date'     => __( 'Date', 'give' ),
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
	 * @return string|bool string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}


	/**
	 * Display Tablenav (extended)
	 *
	 * Display the table navigation above or below the table even when no items in the logs, so nav doesn't disappear
	 *
	 * @see    : https://github.com/WordImpress/Give/issues/564
	 *
	 * @since  1.4.1
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( 'top' === $which ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php endif; ?>

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
	 * This is used to return log entries that match our search query, user query, or form query
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		$user = $this->get_filtered_user();
		$give_form = $this->get_filtered_give_form();

		$meta_query = array();

		if ( $user ) {
			// Show only logs from a specific user.
			$meta_query[] = array(
				'key'   => '_give_log_user_id',
				'value' => $user,
			);
		}

		if ( $give_form ) {
			$meta_query[] = array(
				'key'   => '_give_log_form_id',
				'value' => $give_form,
			);
		}

		$search = $this->get_search();
		if ( $search ) {
			if ( is_email( $search ) ) {
				// This is an email search. We use this to ensure it works for guest users and logged-in users.
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
						// Found one, set meta value to user's ID.
						$search = $user->ID;
					} else {
						// No user found so let's do a real search query.
						$users = new WP_User_Query( array(
							'search'         => $search,
							'search_columns' => array( 'user_url', 'user_nicename' ),
							'number'         => 1,
							'fields'         => 'ids',
						) );

						$found_user = $users->get_results();

						if ( $found_user ) {
							$search = $found_user[0];
						}
					}
				}
			}

			if ( ! $this->file_search ) {
				// Meta query only works for non file name search.
				$meta_query[] = array(
					'key'     => $key,
					'value'   => $search,
					'compare' => $compare,
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
	 * @param string $which
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
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
		echo Give()->html->forms_dropdown( array(
			'selected' => $this->get_filtered_give_form(),
			'name'   => 'form',
			'id'     => 'give-log-form-filter',
			'chosen' => true,
		) );
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
		$log_query = $this->get_query_params();
		$logs = Give()->logs->get_connected_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/* @var Give_payment $payment */
				$payment = new Give_Payment( $log->log_parent );

				// Make sure this payment hasn't been deleted
				if ( get_post( $payment->ID ) ) :
					$logs_data[] = array(
						'ID'         => '<span class="give-item-label give-item-label-gray">' . $log->ID . '</span>',
						'payment_id' => $payment->ID,
						'form'       => $payment->form_id,
						'amount'     => $payment->total,
						'donor_id'    => $payment->customer_id,
						'donor_name' => trim( "{$payment->first_name} $payment->last_name" ),
						'date'       => $payment->date,
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
	 * @uses   Give_Sales_Log_Table::get_columns()
	 * @uses   WP_List_Table::get_sortable_columns()
	 * @uses   Give_Sales_Log_Table::get_pagenum()
	 * @uses   Give_Sales_Log_Table::get_logs()
	 * @uses   Give_Sales_Log_Table::get_log_count()
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$current_page          = $this->get_pagenum();
		$this->items           = $this->get_logs();
		$total_items           = Give()->logs->get_log_count( 0, 'sale', $this->get_meta_query() );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}


	/**
	 * Get log query param.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_query_params() {
		$paged     = $this->get_paged();
		$user      = $this->get_filtered_user();

		$log_query = array(
			'log_type'   => 'sale',
			'paged'      => $paged,
			'meta_query' => $this->get_meta_query(),
			'number'     => $this->per_page,
		);

		return $log_query;
	}
}
