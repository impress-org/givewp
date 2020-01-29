<?php
/**
 * Donor List Table Class.
 *
 * The list view under WP-Admin > Donations > Donors.
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

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Give_Donor_List_Table Class.
 *
 * @since 1.0
 */
class Give_Donor_List_Table extends WP_List_Table {

	/**
	 * Number of items per page.
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 * Number of donors found.
	 *
	 * @var int
	 * @since 1.0
	 */
	public $count = 0;

	/**
	 * Total donors.
	 *
	 * @var int
	 * @since 1.0
	 */
	public $total = 0;

	/**
	 * Get things started.
	 *
	 * @since 1.0
	 * @see   WP_List_Table::__construct()
	 */
	public function __construct() {
		// Set parent defaults.
		parent::__construct(
			array(
				'singular' => __( 'Donor', 'give' ), // Singular name of the listed records.
				'plural'   => __( 'Donors', 'give' ), // Plural name of the listed records.
				'ajax'     => false, // Does this table support ajax?.
			)
		);

	}
	/**
	 * Add donors search filter.
	 *
	 * @since 2.4.0
	 * @return void
	 */
	public function advanced_filters() {
		$start_date = isset( $_GET['start-date'] ) ? strtotime( give_clean( $_GET['start-date'] ) ) : '';
		$end_date   = isset( $_GET['end-date'] ) ? strtotime( give_clean( $_GET['end-date'] ) ) : '';
		$status     = isset( $_GET['status'] ) ? give_clean( $_GET['status'] ) : '';
		$donor      = isset( $_GET['donor'] ) ? absint( $_GET['donor'] ) : '';
		$search     = $this->get_search();
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		?>
		<div id="give-donor-filters" class="give-filters">
			<div class="give-donor-search-box">
				<input type="text" id="give-donors-search-input" placeholder="<?php _e( 'Name, Email, or Donor ID', 'give' ); ?>" name="s" value="<?php echo $search; ?>">
				<?php
				submit_button(
					__( 'Search', 'give' ),
					'button',
					false,
					false,
					array(
						'ID' => 'donor-search-submit',
					)
				);
				?>
			</div>
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
			 * Action to add hidden fields and HTML in donor search.
			 *
			 * @since 2.4.0
			 */
			do_action( 'give_donor_table_advanced_filters' );

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
					<a href="<?php echo admin_url( 'edit.php?post_type=give_forms&page=give-donors' ); ?>"
					   class="button give-clear-filters-button"><?php _e( 'Clear Filters', 'give' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param array  $donor       Contains all the data of the donors.
	 * @param string $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Column Name.
	 */
	public function column_default( $donor, $column_name ) {

		switch ( $column_name ) {

			case 'num_donations':
				$value = sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . absint( $donor['id'] ) ),
					esc_html( $donor['num_donations'] )
				);
				break;

			case 'amount_spent':
				$value = give_currency_filter( give_format_amount( $donor[ $column_name ], array( 'sanitize' => false ) ) );
				break;

			case 'date_created':
				$value = date_i18n( give_date_format(), strtotime( $donor['date_created'] ) );
				break;

			default:
				$value = isset( $donor[ $column_name ] ) ? $donor[ $column_name ] : null;
				break;
		}

		return apply_filters( "give_donors_column_{$column_name}", $value, $donor['id'] );

	}

	/**
	 * For CheckBox Column
	 *
	 * @param array $donor Donor Data.
	 *
	 * @access public
	 * @since  1.8.16
	 *
	 * @return string
	 */
	public function column_cb( $donor ) {
		return sprintf(
			'<input class="donor-selector" type="checkbox" name="donor[]" value="%1$d" data-name="%2$s" />',
			$donor['id'],
			esc_attr( $donor['name'] )
		);
	}

	/**
	 * Column name.
	 *
	 * @param array $donor Donor Data.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string
	 */
	public function column_name( $donor ) {

		// Get donor's initials for non-gravatars
		$title_prefix                 = Give()->donor_meta->get_meta( $donor['id'], '_give_donor_title_prefix', true );
		$donor_name_without_prefix    = trim( str_replace( $title_prefix, '', $donor['name'] ) );
		$donor_name_array             = explode( ' ', $donor_name_without_prefix );
		$donor_name_args['firstname'] = ! empty( $donor_name_array[0] ) ? $donor_name_array[0] : '';
		$donor_name_args['lastname']  = ! empty( $donor_name_array[1] ) ? $donor_name_array[1] : '';
		$donor_name_initial           = give_get_name_initial( $donor_name_args );

		$donation_gravatar_image = sprintf(
			'<span class="give-donor__image give-donor-admin-avatar" data-donor_email="%1$s" data-has-valid-gravatar="%2$s">%3$s</span>',
			md5( strtolower( trim( $donor['email'] ) ) ),
			absint( give_validate_gravatar( $donor['email'] ) ),
			esc_attr( $donor_name_initial )
		);

		$name = ! empty( $donor['name'] )
			? sprintf(
				'%1$s<span class="give-donor-name-text">%2$s</span>',
				$donation_gravatar_image,
				esc_attr( $donor['name'] )
			)
			: sprintf(
				'<em>%1$s</em>',
				__( 'Unnamed Donor', 'give' )
			);

		$view_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor['id'] );
		$actions  = $this->get_row_actions( $donor );

		return sprintf(
			'<a href="%1$s" class="give-donor-name">%2$s</a>%3$s',
			esc_url( $view_url ),
			$name,
			$this->row_actions( $actions )
		);
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = array(
			'cb'            => '<input type="checkbox" />', // Render a checkbox instead of text.
			'name'          => __( 'Name', 'give' ),
			'email'         => __( 'Email', 'give' ),
			'num_donations' => __( 'Donations', 'give' ),
			'amount_spent'  => __( 'Total Donated', 'give' ),
			'date_created'  => __( 'Date Created', 'give' ),
		);

		return apply_filters( 'give_list_donors_columns', $columns );

	}

	/**
	 * Get the sortable columns.
	 *
	 * @access public
	 * @since  2.1
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {

		$columns = array(
			'date_created'  => array( 'date_created', true ),
			'name'          => array( 'name', true ),
			'num_donations' => array( 'purchase_count', false ),
			'amount_spent'  => array( 'purchase_value', false ),
		);

		return apply_filters( 'give_list_donors_sortable_columns', $columns );
	}

	/**
	 * Retrieve row actions.
	 *
	 * @param array $donor Donor Data.
	 *
	 * @since  1.7
	 * @access public
	 *
	 * @return array An array of action links.
	 */
	public function get_row_actions( $donor ) {

		$actions = array(
			'id'     => '<span class="give-donor-id">ID: ' . $donor['id'] . '  </span>',
			'view'   => sprintf( '<a href="%1$s" aria-label="%2$s">%3$s</a>', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor['id'] ), sprintf( esc_attr__( 'View "%s"', 'give' ), esc_attr( $donor['name'] ) ), __( 'View Donor', 'give' ) ),
			'delete' => sprintf( '<a class="%1$s" data-id="%2$s" href="#" aria-label="%3$s">%4$s</a>', 'give-single-donor-delete', $donor['id'], sprintf( esc_attr__( 'Delete "%s"', 'give' ), esc_attr( $donor['name'] ) ), __( 'Delete', 'give' ) ),
		);

		return apply_filters( 'give_donor_row_actions', $actions, $donor );

	}

	/**
	 * Retrieve the current page number.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return int Current page number.
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Retrieves the search query string.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return mixed string If search is present, false otherwise.
	 */
	public function get_search() {
		return ! empty( $_GET['s'] ) ? urldecode( trim( $_GET['s'] ) ) : false;
	}

	/**
	 * Get the Bulk Actions.
	 *
	 * @access public
	 * @since  1.8.16
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'give' ),
		);

		return $actions;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @param string $which Position to trigger i.e. Top/Bottom.
	 *
	 * @access protected
	 * @since  1.8.16
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-donors', '_wpnonce', false );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Retrieves the donor data from db.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return array $data The Donor data.
	 */
	public function donor_data() {

		$data = array();

		// Get donor query.
		$args   = $this->get_donor_query();
		$donors = Give()->donors->get_donors( $args );

		if ( $donors ) {

			foreach ( $donors as $donor ) {

				$user_id      = ! empty( $donor->user_id ) ? intval( $donor->user_id ) : 0;
				$title_prefix = Give()->donor_meta->get_meta( $donor->id, '_give_donor_title_prefix', true );

				// If title prefix is set, then update the donor name.
				$donor->name = give_get_donor_name_with_title_prefixes( $title_prefix, $donor->name );

				$data[] = array(
					'id'            => $donor->id,
					'user_id'       => $user_id,
					'name'          => $donor->name,
					'email'         => $donor->email,
					'num_donations' => $donor->purchase_count,
					'amount_spent'  => $donor->purchase_value,
					'date_created'  => $donor->date_created,
				);
			}
		}

		return apply_filters( 'give_donors_column_query_data', $data );
	}

	/**
	 * Get donor count.
	 *
	 * @since  1.8.1
	 * @access private
	 */
	private function get_donor_count() {
		// Get donor query.
		$_donor_query = $this->get_donor_query();

		$_donor_query['number'] = - 1;
		$_donor_query['offset'] = 0;
		$_donor_query['count']  = true;

		return Give()->donors->get_donors( $_donor_query );
	}

	/**
	 * Get donor query.
	 *
	 * @since  1.8.1
	 * @access public
	 *
	 * @return array
	 */
	public function get_donor_query() {
		$per_page   = $this->per_page;
		$paged      = $this->get_paged();
		$donor      = isset( $_GET['donor'] ) ? $_GET['donor'] : null;
		$start_date = ! empty( $_GET['start-date'] ) ? strtotime( give_clean( $_GET['start-date'] ) ) : false;
		$end_date   = ! empty( $_GET['end-date'] ) ? strtotime( give_clean( $_GET['end-date'] ) ) : false;
		$form_id    = ! empty( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : null;
		$offset     = $this->per_page * ( $paged - 1 );
		$search     = $this->get_search();
		$order      = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC';
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id';

		$args = array(
			'output'     => 'payments',
			'number'     => $per_page,
			'offset'     => $offset,
			'page'       => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'orderby'    => $orderby,
			'order'      => $order,
			'donor'      => $donor,
			's'          => $search,
			'start_date' => $start_date,
			'end_date'   => $end_date,
			'give_forms' => $form_id,
		);

		/**
		 * Filter to modify donor table argument.
		 *
		 * @since 2.4.0
		 */
		$args = (array) apply_filters( 'give_donor_table_query', $args );

		return $args;
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item.
	 *
	 * @since  1.8.17
	 * @access public
	 */
	public function single_row( $item ) {
		echo sprintf( '<tr id="donor-%1$d" data-id="%2$d" data-name="%3$s">', $item['id'], $item['id'], esc_attr( $item['name'] ) );
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Display the final donor table
	 *
	 * @since  1.8.17
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );

		$get_data = give_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

		$order    = ! empty( $get_data['order'] ) ? $get_data['order'] : 'DESC';
		$order_by = ! empty( $get_data['orderby'] ) ? $get_data['orderby'] : 'id';
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"
			<?php
			if ( $singular ) {
				echo " data-wp-lists='list:$singular'";
			}
			?>
			>
			<tr class="hidden"></tr>
			<tr id="give-bulk-delete"
				class="inline-edit-row inline-edit-row-page inline-edit-page bulk-edit-row bulk-edit-row-page bulk-edit-page inline-editor"
				style="display: none;">
				<td colspan="6" class="colspanchange">

					<fieldset class="inline-edit-col-left">
						<legend class="inline-edit-legend"><?php esc_attr_e( 'BULK DELETE', 'give' ); ?></legend>
						<div class="inline-edit-col">
							<div id="bulk-titles">
								<div id="give-bulk-donors" class="give-bulk-donors">

								</div>
							</div>
					</fieldset>

					<fieldset class="inline-edit-col-right">
						<div class="inline-edit-col">
							<label>
								<input class="give-donor-delete-confirm" type="checkbox"
									   name="give-donor-delete-confirm"/>
								<?php esc_attr_e( 'Are you sure you want to delete the selected donor(s)?', 'give' ); ?>
							</label>
							<label>
								<input class="give-donor-delete-records" type="checkbox"
									   name="give-donor-delete-records"/>
								<?php esc_attr_e( 'Delete all associated donations and records?', 'give' ); ?>
							</label>
						</div>
					</fieldset>

					<p class="submit inline-edit-save">
						<input type="hidden" name="give_action" value="delete_bulk_donor"/>
						<input type="hidden" name="orderby" value="<?php echo esc_html( $order_by ); ?>"/>
						<input type="hidden" name="order" value="<?php echo esc_html( $order ); ?>"/>
						<button type="button" id="give-bulk-delete-cancel"
								class="button cancel alignleft"><?php esc_attr_e( 'Cancel', 'give' ); ?></button>
						<input type="submit" id="give-bulk-delete-button" disabled
							   class="button button-primary alignright"
							   value="<?php esc_attr_e( 'Delete', 'give' ); ?>">
						<br class="clear">
					</p>
				</td>
			</tr>
			<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<tfoot>
			<tr>
				<?php $this->print_column_headers( false ); ?>
			</tr>
			</tfoot>

		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return void
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns.
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->donor_data();

		$this->total = $this->get_donor_count();

		$this->set_pagination_args(
			array(
				'total_items' => $this->total,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $this->total / $this->per_page ),
			)
		);
	}
}
