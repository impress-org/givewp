<?php
/**
 * Payment History Table Class
 *
 * @package     Give
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2016, Give
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
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
	 * Total number of processing payments
	 *
	 * @var int
	 * @since 1.8.9
	 */
	public $processing_count;

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
	 * Total number of pre-approved payments
	 *
	 * @var int
	 * @since 1.8.13
	 */
	public $preapproval_count;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 * @uses  Give_Payment_History_Table::get_payment_counts()
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {

		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => give_get_forms_label_singular(),    // Singular name of the listed records.
				'plural'   => give_get_forms_label_plural(),      // Plural name of the listed records.
				'ajax'     => false,                              // Does this table support ajax?
			)
		);

		$this->process_bulk_action();
		$this->get_payment_counts();
		$this->base_url = admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' );
	}

	/**
	 * Add donation search filter.
	 *
	 * @return void
	 */
	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] ) ? strtotime( give_clean( $_GET['start-date'] ) ) : '';
		$end_date   = isset( $_GET['end-date'] ) ? strtotime( give_clean( $_GET['end-date'] ) ) : '';
		$status     = isset( $_GET['status'] ) ? give_clean( $_GET['status'] ) : '';
		$donor      = isset( $_GET['donor'] ) ? absint( $_GET['donor'] ) : '';
		$search     = isset( $_GET['s'] ) ? give_clean( $_GET['s'] ) : '';
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		?>
		<div id="give-payment-filters" class="give-filters">
			<?php $this->search_box( __( 'Search', 'give' ), 'give-payments' ); ?>
			<div id="give-payment-date-filters">
				<div class="give-filter give-filter-half">
					<label for="start-date"
						   class="give-start-date-label"><?php _e( 'Start Date', 'give' ); ?></label>
					<input type="text"
						   id="start-date"
						   name="start-date"
						   class="give_datepicker"
						   autocomplete="off"
						   value="<?php echo $start_date ? date_i18n( give_date_format(), $start_date ) : ''; ?>"
						   data-standard-date="<?php echo $start_date ? date( 'Y-m-d', $start_date ) : $start_date; ?>"
						   placeholder="<?php _e( 'Start Date', 'give' ); ?>"
					/>
				</div>
				<div class="give-filter give-filter-half">
					<label for="end-date" class="give-end-date-label"><?php _e( 'End Date', 'give' ); ?></label>
					<input type="text"
						   id="end-date"
						   name="end-date"
						   class="give_datepicker"
						   autocomplete="off"
						   value="<?php echo $end_date ? date_i18n( give_date_format(), $end_date ) : ''; ?>"
						   data-standard-date="<?php echo $end_date ? date( 'Y-m-d', $end_date ) : $end_date; ?>"
						   placeholder="<?php _e( 'End Date', 'give' ); ?>"
					/>
				</div>
			</div>
			<div id="give-payment-form-filter" class="give-filter">
				<label for="give-donation-forms-filter"
					   class="give-donation-forms-filter-label"><?php _e( 'Form', 'give' ); ?></label>
				<?php
				// Filter Donations by Donation Forms.
				echo Give()->html->forms_dropdown(
					array(
						'name'     => 'form_id',
						'id'       => 'give-donation-forms-filter',
						'class'    => 'give-donation-forms-filter',
						'selected' => $form_id, // Make sure to have $form_id set to 0, if there is no selection.
						'chosen'   => true,
						'number'   => 30,
					)
				);
				?>
			</div>

			<?php
			/**
			 * Action to add hidden fields and HTML in Payment search.
			 *
			 * @since 1.8.18
			 */
			do_action( 'give_payment_table_advanced_filters' );

			if ( ! empty( $status ) ) {
				echo sprintf( '<input type="hidden" name="status" value="%s"/>', esc_attr( $status ) );
			}

			if ( ! empty( $donor ) ) {
				echo sprintf( '<input type="hidden" name="donor" value="%s"/>', absint( $donor ) );
			}
			?>

			<div class="give-filter">
				<?php submit_button( __( 'Apply', 'give' ), 'secondary', '', false ); ?>
				<?php
				// Clear active filters button.
				if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $donor ) || ! empty( $search ) || ! empty( $status ) || ! empty( $form_id ) ) :
					?>
					<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-payment-history' ); ?>"
					   class="button give-clear-filters-button"><?php _e( 'Clear Filters', 'give' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * Show the search field
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 *
	 * @since  1.0
	 * @access public
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
		<div class="give-filter give-filter-search" role="search">
			<?php
			/**
			 * Fires in the payment history search box.
			 *
			 * Allows you to add new elements before the search box.
			 *
			 * @since 1.7
			 */
			do_action( 'give_payment_history_search' );
			?>
			<label class="screen-reader-text" for="<?php echo $input_id; ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id; ?>" name="s"
				   value="<?php _admin_search_query(); ?>"
				   placeholder="<?php _e( 'Name, Email, or Donation ID', 'give' ); ?>" />
			<?php
			submit_button(
				$text,
				'button',
				false,
				false,
				array(
					'ID' => 'search-submit',
				)
			);
			?>
			<br />
		</div>
		<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$views   = array();
		$tabs    = array(
			'all'         => array(
				'total_count',
				__( 'All', 'give' ),
			),
			'publish'     => array(
				'complete_count',
				__( 'Completed', 'give' ),
			),
			'pending'     => array(
				'pending_count',
				__( 'Pending', 'give' ),
			),
			'processing'  => array(
				'processing_count',
				__( 'Processing', 'give' ),
			),
			'refunded'    => array(
				'refunded_count',
				__( 'Refunded', 'give' ),
			),
			'revoked'     => array(
				'revoked_count',
				__( 'Revoked', 'give' ),
			),
			'failed'      => array(
				'failed_count',
				__( 'Failed', 'give' ),
			),
			'cancelled'   => array(
				'cancelled_count',
				__( 'Cancelled', 'give' ),
			),
			'abandoned'   => array(
				'abandoned_count',
				__( 'Abandoned', 'give' ),
			),
			'preapproval' => array(
				'preapproval_count',
				__( 'Preapproval Pending', 'give' ),
			),
		);

		/**
		 * Remove Query from Args of the URL that are being pass to Donation Status.
		 *
		 * @since 1.8.18
		 */
		$args = (array) apply_filters( 'give_payments_table_status_remove_query_arg', array( 'paged', '_wpnonce', '_wp_http_referer' ) );

		// Build URL.
		$staus_url = remove_query_arg( $args );

		foreach ( $tabs as $key => $tab ) {
			$count_key = $tab[0];
			$name      = $tab[1];
			$count     = $this->$count_key;

			/**
			 * Filter can be used to show all the status inside the donation tabs.
			 *
			 * Filter can be used to show all the status inside the donation submenu tabs return true to show all the tab.
			 *
			 * @param string $key Current view tab value.
			 * @param int $count Number of donation inside the tab.
			 *
			 * @since 1.8.12
			 */
			if ( 'all' === $key || $key === $current || apply_filters( 'give_payments_table_show_all_status', 0 < $count, $key, $count ) ) {

				$staus_url = 'all' === $key ?
					add_query_arg( array( 'status' => false ), $staus_url ) :
					add_query_arg( array( 'status' => $key ), $staus_url );

				$views[ $key ] = sprintf(
					'<a href="%s"%s>%s&nbsp;<span class="count">(%s)</span></a>',
					esc_url( $staus_url ),
					( ( 'all' === $key && empty( $current ) ) ) ? ' class="current"' : ( $current == $key ? 'class="current"' : '' ),
					$name,
					$count
				);
			}
		}

		/**
		 * Filter the donation listing page views.
		 *
		 * @since 1.0
		 *
		 * @param array $views
		 * @param Give_Payment_History_Table
		 */
		return apply_filters( 'give_payments_table_views', $views, $this );
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
			'cb'            => '<input type="checkbox" />', // Render a checkbox instead of text.
			'donation'      => __( 'Donation', 'give' ),
			'donation_form' => __( 'Donation Form', 'give' ),
			'status'        => __( 'Status', 'give' ),
			'date'          => __( 'Date', 'give' ),
			'amount'        => __( 'Amount', 'give' ),
		);

		if ( current_user_can( 'view_give_payments' ) ) {
			$columns['details'] = __( 'Details', 'give' );
		}

		return apply_filters( 'give_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'donation'      => array( 'ID', true ),
			'donation_form' => array( 'donation_form', false ),
			'status'        => array( 'status', false ),
			'amount'        => array( 'amount', false ),
			'date'          => array( 'date', false ),
		);

		return apply_filters( 'give_payments_table_sortable_columns', $columns );
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since  1.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'donation';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param Give_Payment $payment     Payment ID.
	 * @param string       $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Column Name
	 */
	public function column_default( $payment, $column_name ) {

		$single_donation_url = esc_url( add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details' ) ) );
		$row_actions         = $this->get_row_actions( $payment );
		$value               = '';

		switch ( $column_name ) {
			case 'donation':
				$serial_code = Give()->seq_donation_number->get_serial_code( $payment );
				if ( current_user_can( 'view_give_payments' ) ) {
					$value = Give()->tooltips->render_link(
						array(
							'label'       => sprintf( __( 'View Donation %s', 'give' ), $serial_code ),
							'tag_content' => $serial_code,
							'link'        => $single_donation_url,
						)
					);
				} else {
					$value = $serial_code;
				}

				$value .= sprintf(
					'&nbsp;%1$s&nbsp;%2$s<br>',
					__( 'by', 'give' ),
					$this->get_donor( $payment )
				);

				$value .= $this->get_donor_email( $payment );
				$value .= $this->row_actions( $row_actions );
				break;

			case 'amount':
				$value  = give_donation_amount( $payment, true );
				$value .= sprintf( '<br><small>%1$s %2$s</small>', __( 'via', 'give' ), give_get_gateway_admin_label( $payment->gateway ) );
				break;

			case 'donation_form':
				$form_title = empty( $payment->form_title ) ? sprintf( __( 'Untitled (#%s)', 'give' ), $payment->form_id ) : $payment->form_title;
				$value      = '<a href="' . admin_url( 'post.php?post=' . $payment->form_id . '&action=edit' ) . '">' . $form_title . '</a>';
				$level      = give_get_donation_form_title(
					$payment,
					array(
						'only_level' => true,
					)
				);

				if ( ! empty( $level ) ) {
					$value .= $level;
				}

				break;

			case 'date':
				$date  = strtotime( $payment->date );
				$value = date_i18n( give_date_format(), $date );
				break;

			case 'status':
				$value = $this->get_payment_status( $payment );
				break;

			case 'details':
				if ( current_user_can( 'view_give_payments' ) ) {
					$value = Give()->tooltips->render_link(
						array(
							'label'       => sprintf( __( 'View Donation #%s', 'give' ), $payment->ID ),
							'tag_content' => '<span class="dashicons dashicons-visibility"></span>',
							'link'        => $single_donation_url,
							'attributes'  => array(
								'class' => 'give-payment-details-link button button-small',
							),
						)
					);

					$value = "<div class=\"give-payment-details-link-wrap\">{$value}</div>";
				}
				break;

			default:
				$value = isset( $payment->$column_name ) ? $payment->$column_name : '';
				break;

		}// End switch().

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, $column_name );
	}

	/**
	 * Get donor email html.
	 *
	 * @param object $payment Contains all the data of the payment.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Data shown in the Email column
	 */
	public function get_donor_email( $payment ) {

		$email = give_get_payment_user_email( $payment->ID );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'give' );
		}

		$value = Give()->tooltips->render_link(
			array(
				'link'        => "mailto:{$email}",
				'label'       => __( 'Email donor', 'give' ),
				'tag_content' => $email,
			)
		);

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'email' );
	}

	/**
	 * Get Row Actions
	 *
	 * @param object $payment Payment Data.
	 *
	 * @since 1.6
	 *
	 * @return array $actions
	 */
	function get_row_actions( $payment ) {

		$actions = array();
		$email   = give_get_payment_user_email( $payment->ID );

		// Add search term string back to base URL.
		$search_terms = ( isset( $_GET['s'] ) ? trim( $_GET['s'] ) : '' );
		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		if ( give_is_payment_complete( $payment->ID ) && ! empty( $email ) ) {

			$actions['email_links'] = sprintf(
				'<a class="resend-single-donation-receipt" href="%1$s" aria-label="%2$s">%3$s</a>',
				wp_nonce_url(
					add_query_arg(
						array(
							'give-action' => 'email_links',
							'purchase_id' => $payment->ID,
						),
						$this->base_url
					),
					'give_payment_nonce'
				),
				sprintf( __( 'Resend Donation %s Receipt', 'give' ), $payment->ID ),
				__( 'Resend Receipt', 'give' )
			);

		}

		if ( current_user_can( 'view_give_payments' ) ) {
			$actions['delete'] = sprintf(
				'<a class="delete-single-donation" href="%1$s" aria-label="%2$s">%3$s</a>',
				wp_nonce_url(
					add_query_arg(
						array(
							'give-action' => 'delete_payment',
							'purchase_id' => $payment->ID,
						),
						$this->base_url
					),
					'give_donation_nonce'
				),
				sprintf( __( 'Delete Donation %s', 'give' ), $payment->ID ),
				__( 'Delete', 'give' )
			);
		}

		return apply_filters( 'give_payment_row_actions', $actions, $payment );
	}


	/**
	 *  Get payment status html.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param Give_Payment $payment Contains all the data of the payment.
	 *
	 * @return string Data shown in the Email column
	 */
	function get_payment_status( $payment ) {
		$value = sprintf(
			'<div class="give-donation-status status-%1$s"><span class="give-donation-status-icon"></span>&nbsp;%2$s</div>',
			$payment->status,
			give_get_payment_status( $payment, true )
		);

		if ( $payment->mode == 'test' ) {
			$value .= Give()->tooltips->render_span(
				array(
					'label'       => __( 'This donation was made in test mode.', 'give' ),
					'tag_content' => __( 'Test', 'give' ),
					'attributes'  => array(
						'class' => 'give-item-label give-item-label-orange give-test-mode-transactions-label',
					),

				)
			);
		}

		if ( true === $payment->import && true === (bool) apply_filters( 'give_payment_show_importer_label', false ) ) {
			$value .= sprintf(
				'&nbsp;<span class="give-item-label give-item-label-orange give-test-mode-transactions-label" data-tooltip="%1$s">%2$s</span>',
				__( 'This donation was imported.', 'give' ),
				__( 'Import', 'give' )
			);
		}

		return $value;
	}

	/**
	 * Get checkbox html.
	 *
	 * @param object $payment Contains all the data for the checkbox column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $payment ) {
		return sprintf( '<input type="checkbox" name="%1$s[]" value="%2$s" />', 'payment', $payment->ID );
	}

	/**
	 * Get payment ID html.
	 *
	 * @param object $payment Contains all the data for the checkbox column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Displays a checkbox.
	 */
	public function get_payment_id( $payment ) {
		return '<span class="give-payment-id">' . give_get_payment_number( $payment->ID ) . '</span>';
	}

	/**
	 * Get donor html.
	 *
	 * @param object $payment Contains all the data of the payment.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Data shown in the User column
	 */
	public function get_donor( $payment ) {

		$donor_id           = give_get_payment_donor_id( $payment->ID );
		$donor_billing_name = give_get_donor_name_by( $payment->ID, 'donation' );
		$donor_name         = give_get_donor_name_by( $donor_id, 'donor' );

		$value = '';
		if ( ! empty( $donor_id ) ) {

			// Check whether the donor name and WP_User name is same or not.
			if ( sanitize_title( $donor_billing_name ) !== sanitize_title( $donor_name ) ) {
				$value .= $donor_billing_name . ' (';
			}

			$value .= '<a href="' . esc_url( admin_url( "edit.php?post_type=give_forms&page=give-donors&view=overview&id=$donor_id" ) ) . '">' . $donor_name . '</a>';

			// Check whether the donor name and WP_User name is same or not.
			if ( sanitize_title( $donor_billing_name ) != sanitize_title( $donor_name ) ) {
				$value .= ')';
			}
		} else {
			$email  = give_get_payment_user_email( $payment->ID );
			$value .= '<a href="' . esc_url( admin_url( "edit.php?post_type=give_forms&page=give-payment-history&s=$email" ) ) . '">' . __( '(donor missing)', 'give' ) . '</a>';
		}

		return apply_filters( 'give_payments_table_column', $value, $payment->ID, 'donor' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete', 'give' ),
			'set-status-publish'     => __( 'Set To Completed', 'give' ),
			'set-status-pending'     => __( 'Set To Pending', 'give' ),
			'set-status-processing'  => __( 'Set To Processing', 'give' ),
			'set-status-refunded'    => __( 'Set To Refunded', 'give' ),
			'set-status-revoked'     => __( 'Set To Revoked', 'give' ),
			'set-status-failed'      => __( 'Set To Failed', 'give' ),
			'set-status-cancelled'   => __( 'Set To Cancelled', 'give' ),
			'set-status-abandoned'   => __( 'Set To Abandoned', 'give' ),
			'set-status-preapproval' => __( 'Set To Preapproval', 'give' ),
			'resend-receipt'         => __( 'Resend Email Receipts', 'give' ),
		);

		return apply_filters( 'give_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since  1.0
	 *
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

			// Detect when a bulk action is being triggered.
			switch ( $this->current_action() ) {

				case 'delete':
					give_delete_donation( $id );
					break;

				case 'set-status-publish':
					give_update_payment_status( $id, 'publish' );
					break;

				case 'set-status-pending':
					give_update_payment_status( $id, 'pending' );
					break;

				case 'set-status-processing':
					give_update_payment_status( $id, 'processing' );
					break;

				case 'set-status-refunded':
					give_update_payment_status( $id, 'refunded' );
					break;
				case 'set-status-revoked':
					give_update_payment_status( $id, 'revoked' );
					break;

				case 'set-status-failed':
					give_update_payment_status( $id, 'failed' );
					break;

				case 'set-status-cancelled':
					give_update_payment_status( $id, 'cancelled' );
					break;

				case 'set-status-abandoned':
					give_update_payment_status( $id, 'abandoned' );
					break;

				case 'set-status-preapproval':
					give_update_payment_status( $id, 'preapproval' );
					break;

				case 'resend-receipt':
					/**
					 * Fire the action
					 *
					 * @since 2.0
					 */
					do_action( 'give_donation-receipt_email_notification', $id );
					break;
			}// End switch().

			/**
			 * Fires after triggering bulk action on payments table.
			 *
			 * @param int    $id             The ID of the payment.
			 * @param string $current_action The action that is being triggered.
			 *
			 * @since 1.7
			 */
			do_action( 'give_payments_table_do_bulk_action', $id, $this->current_action() );
		}// End foreach().

	}

	/**
	 * Retrieve the payment counts
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return object
	 */
	public function get_payment_counts() {

		$args = array();

		if ( isset( $_GET['user'] ) ) {
			$args['user'] = urldecode( $_GET['user'] );
		} elseif ( isset( $_GET['donor'] ) ) {
			$args['donor'] = absint( $_GET['donor'] );
		} elseif ( isset( $_GET['s'] ) ) {
			$is_user = strpos( $_GET['s'], strtolower( 'user:' ) ) !== false;
			if ( $is_user ) {
				$args['user'] = absint( trim( str_replace( 'user:', '', strtolower( $_GET['s'] ) ) ) );
				unset( $args['s'] );
			} else {
				$args['s'] = sanitize_text_field( $_GET['s'] );
			}
		}

		if ( ! empty( $_GET['start-date'] ) ) {
			$args['start-date'] = urldecode( $_GET['start-date'] );
		}

		if ( ! empty( $_GET['end-date'] ) ) {
			$args['end-date'] = urldecode( $_GET['end-date'] );
		}

		$args['form_id'] = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;
		$args['gateway'] = ! empty( $_GET['gateway'] ) ? give_clean( $_GET['gateway'] ) : null;

		$payment_count           = give_count_payments( $args );
		$this->complete_count    = $payment_count->publish;
		$this->pending_count     = $payment_count->pending;
		$this->processing_count  = $payment_count->processing;
		$this->refunded_count    = $payment_count->refunded;
		$this->failed_count      = $payment_count->failed;
		$this->revoked_count     = $payment_count->revoked;
		$this->cancelled_count   = $payment_count->cancelled;
		$this->abandoned_count   = $payment_count->abandoned;
		$this->preapproval_count = $payment_count->preapproval;

		foreach ( $payment_count as $count ) {
			$this->total_count += $count;
		}

		return $payment_count;
	}

	/**
	 * Retrieve all the data for all the payments.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array  objects in array containing all the data for the payments
	 */
	public function payments_data() {
		$per_page   = $this->per_page;
		$orderby    = isset( $_GET['orderby'] ) ? urldecode( $_GET['orderby'] ) : 'ID';
		$order      = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$user       = isset( $_GET['user'] ) ? $_GET['user'] : null;
		$donor      = isset( $_GET['donor'] ) ? $_GET['donor'] : null;
		$status     = isset( $_GET['status'] ) ? $_GET['status'] : give_get_payment_status_keys();
		$meta_key   = isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;
		$year       = isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month      = isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day        = isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$start_date = ! empty( $_GET['start-date'] )
			? give_clean( $_GET['start-date'] )
			: date( 'Y-m-d', 0 );
		$end_date   = ! empty( $_GET['end-date'] )
			? give_clean( $_GET['end-date'] )
			: date( 'Y-m-d', current_time( 'timestamp' ) );
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;
		$gateway    = ! empty( $_GET['gateway'] ) ? give_clean( $_GET['gateway'] ) : null;

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'user'       => $user,
			'donor'      => $donor,
			'status'     => $status,
			'meta_key'   => $meta_key,
			'year'       => $year,
			'month'      => $month,
			'day'        => $day,
			's'          => $search,
			'start_date' => $start_date,
			'gateway'    => $gateway,
			'end_date'   => $end_date,
			'give_forms' => $form_id,
		);

		if ( is_string( $search ) && false !== strpos( $search, 'txn:' ) ) {
			$args['search_in_notes'] = true;
			$args['s']               = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		/**
		 * Filter to modify payment table argument.
		 *
		 * @since 1.8.18
		 */
		$args = (array) apply_filters( 'give_payment_table_payments_query', $args );

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
	 *
	 * @return void
	 */
	public function prepare_items() {

		wp_reset_vars( array( 'action', 'payment', 'orderby', 'order', 's' ) );

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns.
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
			case 'processing':
				$total_items = $this->processing_count;
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
			case 'preapproval':
				$total_items = $this->preapproval_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-Give status.
				$count       = wp_count_posts( 'give_payment' );
				$total_items = isset( $count->{$status} ) ? $count->{$status} : 0;
				break;
		}

		$this->items = $data;

		/**
		 * Filter to modify total count of the pagination.
		 *
		 * @since 1.8.19
		 */
		$total_items = (int) apply_filters( 'give_payment_table_pagination_total_count', $total_items, $this );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				// We have to calculate the total number of items.
				'per_page'    => $this->per_page,
				// We have to determine how many items to show on a page.
				'total_pages' => ceil( $total_items / $this->per_page ),
				// We have to calculate the total number of pages.
			)
		);
	}
}
