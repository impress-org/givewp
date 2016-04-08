<?php
/**
 * Payment History Table Class
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, Give
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
 * Give_Payment_History_Table Class
 *
 * Renders the Payment History table on the Payment History page
 *
 * @since 1.0
 */
class Give_Payment_History_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.0.1
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Total number of complete payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $complete_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $pending_count;

	/**
	 * Total number of refunded payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $refunded_count;

	/**
	 * Total number of failed payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $failed_count;

	/**
	 * Total number of revoked payments
	 *
	 * @var int
	 * @since 1.0
	 */
	public $revoked_count;

	/**
	 * Total number of cancelled payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $cancelled_count;

	/**
	 * Total number of abandoned payments
	 *
	 * @var int
	 * @since 1.6
	 */
	public $abandoned_count;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @uses  Give_Payment_History_Table::get_payment_counts()
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

		$this->get_payment_counts();
		$this->process_bulk_action();
		$this->base_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );
	}

	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : null;
		$status     = isset( $_GET['status'] ) ? $_GET['status'] : '';
		?>
		<div id="give-payment-filters">
			<span id="give-payment-date-filters">
				<label for="start-date" class="give-start-date-label"><?php _e( 'Start Date:', 'give' ); ?></label>
				<input type="text" id="start-date" name="start-date" class="give_datepicker" value="<?php echo $start_date; ?>" placeholder="mm/dd/yyyy" />
				<label for="end-date" class="give-end-date-label"><?php _e( 'End Date:', 'give' ); ?></label>
				<input type="text" id="end-date" name="end-date" class="give_datepicker" value="<?php echo $end_date; ?>" placeholder="mm/dd/yyyy" />
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'give' ); ?>" />
			</span>
			<?php if ( ! empty( $status ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" />
			<?php endif; ?>
			<?php if ( ! empty( $start_date ) || ! empty( $end_date ) ) : ?>
				<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'give' ); ?></a>
			<?php endif; ?>
			<?php $this->search_box( __( 'Search', 'give' ), 'give-payments' ); ?>
		</div>

		<?php
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
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>
		<p class="search-box">
			<?php do_action( 'give_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?><br />
		</p>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since  1.0
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current         = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count     = '&nbsp;<span class="count">(' . $this->total_count . ')</span>';
		$complete_count  = '&nbsp;<span class="count">(' . $this->complete_count . ')</span>';
		$cancelled_count = '&nbsp;<span class="count">(' . $this->cancelled_count . ')</span>';
		$pending_count   = '&nbsp;<span class="count">(' . $this->pending_count . ')</span>';
		$refunded_count  = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$failed_count    = '&nbsp;<span class="count">(' . $this->failed_count . ')</span>';
		$abandoned_count = '&nbsp;<span class="count">(' . $this->abandoned_count . ')</span>';
		$revoked_count   = '&nbsp;<span class="count">(' . $this->revoked_count . ')</span>';

		$views = array(
			'all'       => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array(
				'status',
				'paged'
			) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'give' ) . $total_count ),
			'publish'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'publish',
				'paged'  => false
			) ) ), $current === 'publish' ? ' class="current"' : '', __( 'Completed', 'give' ) . $complete_count ),
			'pending'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'pending',
				'paged'  => false
			) ) ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'give' ) . $pending_count ),
			'refunded'  => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'refunded',
				'paged'  => false
			) ) ), $current === 'refunded' ? ' class="current"' : '', __( 'Refunded', 'give' ) . $refunded_count ),
			'revoked'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'revoked',
				'paged'  => false
			) ) ), $current === 'revoked' ? ' class="current"' : '', __( 'Revoked', 'give' ) . $revoked_count ),
			'failed'    => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'failed',
				'paged'  => false
			) ) ), $current === 'failed' ? ' class="current"' : '', __( 'Failed', 'give' ) . $failed_count ),
			'cancelled' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'cancelled',
				'paged'  => false
			) ) ), $current === 'cancelled' ? ' class="current"' : '', __( 'Cancelled', 'give' ) . $cancelled_count ),
			'abandoned' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array(
				'status' => 'abandoned',
				'paged'  => false
			) ) ), $current === 'abandoned' ? ' class="current"' : '', __( 'Abandoned', 'give' ) . $abandoned_count )
		);

		return apply_filters( 'give_payments_table_views', $views );
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
			'cb'      => '<input type="checkbox" />', //Render a checkbox instead of text
			'email'   => __( 'Email', 'give' ),
			'details' => __( 'Details', 'give' ),
			'amount'  => __( 'Amount', 'give' ),
			'status'  => __( 'Status', 'give' ),
			'date'    => __( 'Date', 'give' ),
			'donor'   => __( 'Donor', 'give' ),
			'ID'      => __( 'ID', 'give' ),
		);

		return apply_filters( 'give_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since  1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID'     => array( 'ID', true ),
			'amount' => array( 'amount', false ),
			'date'   => array( 'date', false )
		);

		return apply_filters( 'give_payments_table_sortable_columns', $columns );
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
	public function column_default( $payment, $column_name ) {
		switch ( $column_name ) {
			case 'amount' :
				$amount = ! empty( $payment->total ) ? $payment->total : 0;
				$value  = give_currency_filter( give_format_amount( $amount ), give_get_payment_currency_code( $payment->ID ) );
				break;
			case 'date' :
				$date  = strtotime( $payment->date );
				$value = date_i18n( get_option( 'date_format' ), $date );
				break;
			case 'status' :
				$payment = get_post( $payment->ID );
				$value   = '<div class="give-donation-status status-' . sanitize_title( give_get_payment_status( $payment, true ) ) . '"><span class="give-donation-status-icon"></span> ' . give_get_payment_status( $payment, true ) . '</div>';
				break;
			case 'details' :
				$value = '<div class="give-payment-details-link-wrap"><a href="' . esc_url( add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-order-details' ) ) ) . '" class="give-payment-details-link button button-small">' . __( 'View Donation Details', 'give' ) . '</a></div>';
				break;
			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;

		}

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Render the Email Column
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $payment Contains all the data of the payment
	 *
	 * @return string Data shown in the Email column
	 */
	public function column_email( $payment ) {

		$row_actions = array();

		if ( give_is_payment_complete( $payment->ID ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg( array(
					'give-action' => 'email_links',
					'purchase_id' => $payment->ID
				), $this->base_url ) . '">' . __( 'Resend Donation Receipt', 'give' ) . '</a>';

		}

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array(
				'give-action' => 'delete_payment',
				'purchase_id' => $payment->ID
			), $this->base_url ), 'give_payment_nonce' ) . '">' . __( 'Delete', 'give' ) . '</a>';

		$row_actions = apply_filters( 'give_payment_row_actions', $row_actions, $payment );

		if ( ! isset( $payment->user_info['email'] ) ) {
			$payment->user_info['email'] = __( '(unknown)', 'give' );
		}

		$value = '<span class="give-email-column-value">' . $payment->user_info['email'] . '</span>' . $this->row_actions( $row_actions );

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $payment Contains all the data for the checkbox column
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $payment ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$payment->ID
		);
	}

	/**
	 * Render the ID column
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $payment Contains all the data for the checkbox column
	 *
	 * @return string Displays a checkbox
	 */
	public function column_ID( $payment ) {
		return '<span class="give-payment-id">' . give_get_payment_number( $payment->ID ) . '</span>';
	}

	/**
	 * Render the User Column
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @param array $payment Contains all the data of the payment
	 *
	 * @return string Data shown in the User column
	 */
	public function column_donor( $payment ) {

		//		$user_id = give_get_payment_user_id( $payment->ID );

		$customer_id = give_get_payment_customer_id( $payment->ID );
		$customer    = new Give_Customer( $customer_id );

		$view_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $customer_id );

		$value = '<a href="' . esc_url( $view_url ) . '" data-tooltip="' . __( 'Click here to view this Donor\'s profile', 'give' ) . '">' . $customer->name . '</a>';

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'donor' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'               => __( 'Delete', 'give' ),
			'set-status-publish'   => __( 'Set To Completed', 'give' ),
			'set-status-pending'   => __( 'Set To Pending', 'give' ),
			'set-status-refunded'  => __( 'Set To Refunded', 'give' ),
			'set-status-revoked'   => __( 'Set To Revoked', 'give' ),
			'set-status-failed'    => __( 'Set To Failed', 'give' ),
			'set-status-cancelled' => __( 'Set To Cancelled', 'give' ),
			'set-status-abandoned' => __( 'Set To Abandoned', 'give' ),
			'resend-receipt'       => __( 'Resend Email Receipts', 'give' )
		);

		return apply_filters( 'give_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids    = isset( $_GET['payment'] ) ? $_GET['payment'] : false;
		$action = $this->current_action();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $action ) ) {
			return;
		}

		foreach ( $ids as $id ) {

			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				give_delete_purchase( $id );
			}

			if ( 'set-status-publish' === $this->current_action() ) {
				give_update_payment_status( $id, 'publish' );
			}

			if ( 'set-status-pending' === $this->current_action() ) {
				give_update_payment_status( $id, 'pending' );
			}

			if ( 'set-status-refunded' === $this->current_action() ) {
				give_update_payment_status( $id, 'refunded' );
			}

			if ( 'set-status-revoked' === $this->current_action() ) {
				give_update_payment_status( $id, 'revoked' );
			}

			if ( 'set-status-failed' === $this->current_action() ) {
				give_update_payment_status( $id, 'failed' );
			}

			if ( 'set-status-cancelled' === $this->current_action() ) {
				give_update_payment_status( $id, 'cancelled' );
			}

			if ( 'set-status-abandoned' === $this->current_action() ) {
				give_update_payment_status( $id, 'abandoned' );
			}

			if ( 'resend-receipt' === $this->current_action() ) {
				give_email_donation_receipt( $id, false );
			}

			do_action( 'give_payments_table_do_bulk_action', $id, $this->current_action() );
		}

	}

	/**
	 * Retrieve the payment counts
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function get_payment_counts() {

		global $wp_query;

		$args = array();

		if ( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif ( isset( $_GET['s'] ) ) {
			$args['s'] = urldecode( $_GET['s'] );
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		$payment_count         = give_count_payments( $args );
		$this->complete_count  = $payment_count->publish;
		$this->pending_count   = $payment_count->pending;
		$this->refunded_count  = $payment_count->refunded;
		$this->failed_count    = $payment_count->failed;
		$this->revoked_count   = $payment_count->revoked;
		$this->cancelled_count = $payment_count->cancelled;
		$this->abandoned_count = $payment_count->abandoned;

		foreach ( $payment_count as $count ) {
			$this->total_count += $count;
		}
	}

	/**
	 * Retrieve all the data for all the payments
	 *
	 * @access public
	 * @since  1.0
	 * @return array $payment_data Array of all the data for the payments
	 */
	public function payments_data() {

		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] ) ? urldecode( $_GET['orderby'] ) : 'ID';
		$order      = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$user       = isset( $_GET['user'] ) ? $_GET['user'] : null;
		$status     = isset( $_GET['status'] ) ? $_GET['status'] : give_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;
		$year       = isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month      = isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day        = isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : $start_date;

		if ( ! empty( $search ) ) {
			$status = 'any'; // Force all payment statuses when searching
		}

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
		);

		if ( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {

			$args['search_in_notes'] = true;
			$args['s']               = trim( str_replace( 'txn:', '', $args['s'] ) );

		}

		$p_query = new Give_Payments_Query( $args );

		return $p_query->get_payments();

	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.0
	 * @uses   Give_Payment_History_Table::get_columns()
	 * @uses   Give_Payment_History_Table::get_sortable_columns()
	 * @uses   Give_Payment_History_Table::payments_data()
	 * @uses   WP_List_Table::get_pagenum()
	 * @uses   WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();
		$data     = $this->payments_data();
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		$this->_column_headers = array( $columns, $hidden, $sortable );

		switch ( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'revoked':
				$total_items = $this->revoked_count;
				break;
			case 'cancelled':
				$total_items = $this->cancelled_count;
				break;
			case 'abandoned':
				$total_items = $this->abandoned_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-Give status
				$count       = wp_count_posts( 'give_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				// WE have to calculate the total number of items
				'per_page'    => $this->per_page,
				// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $this->per_page )
				// WE have to calculate the total number of pages
			)
		);
	}
}
