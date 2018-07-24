<?php
/**
 * Donor List Table Class.
 *
 * The list view under WP-Admin > Donations > Donors.
 *
 * @package     Give
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2016, WordImpress
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
		parent::__construct( array(
			'singular' => __( 'Donor', 'give' ), // Singular name of the listed records.
			'plural'   => __( 'Donors', 'give' ), // Plural name of the listed records.
			'ajax'     => false, // Does this table support ajax?.
		) );

	}

	/**
	 * Show the search field.
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
			echo sprintf( '<input type="hidden" name="orderby" value="%1$s" />', esc_attr( $_REQUEST['orderby'] ) );
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo sprintf( '<input type="hidden" name="order" value="%1$s" />', esc_attr( $_REQUEST['order'] ) );
		}
		?>
		<p class="search-box" role="search">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( $text, 'button', false, false, array(
				'ID' => 'search-submit',
			) ); ?>
		</p>
		<?php
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @param array  $donor        Contains all the data of the donors.
	 * @param string $column_name The name of the column.
	 *
	 * @access public
	 * @since  1.0
	 *
	 * @return string Column Name.
	 */
	public function column_default( $donor, $column_name ) {

		switch ( $column_name ) {

			case 'num_donations' :
				$value = sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'edit.php?post_type=give_forms&page=give-payment-history&donor=' . absint( $donor['id'] ) ),
					esc_html( $donor['num_donations'] )
				);
				break;

			case 'amount_spent' :
				$value = give_currency_filter( give_format_amount( $donor[ $column_name ], array( 'sanitize' => false ) ) );
				break;

			case 'date_created' :
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
			'<input class="donor-selector" type="checkbox" name="%1$s[]" value="%2$d" data-name="%3$s" />',
			$this->_args['singular'],
			$donor['id'],
			$donor['name']
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
		$name     = ! empty( $donor['name'] ) ? $donor['name'] : '<em>' . __( 'Unnamed Donor', 'give' ) . '</em>';
		$view_url = admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor['id'] );
		$actions  = $this->get_row_actions( $donor );

		return '<a href="' . esc_url( $view_url ) . '">' . $name . '</a>' . $this->row_actions( $actions );
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
			'view'   => sprintf( '<a href="%1$s" aria-label="%2$s">%3$s</a>', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . $donor['id'] ), sprintf( esc_attr__( 'View "%s"', 'give' ), $donor['name'] ), __( 'View Donor', 'give' ) ),
			'notes'  => sprintf( '<a href="%1$s" aria-label="%2$s">%3$s</a>', admin_url( 'edit.php?post_type=give_forms&page=give-donors&view=notes&id=' . $donor['id'] ), sprintf( esc_attr__( 'Notes for "%s"', 'give' ), $donor['name'] ), __( 'Notes', 'give' ) ),
			'delete' => sprintf( '<a class="%1$s" data-id="%2$s" href="#" aria-label="%3$s">%4$s</a>', 'give-single-donor-delete', $donor['id'],sprintf( esc_attr__( 'Delete "%s"', 'give' ), $donor['name'] ), __( 'Delete', 'give' ) ),
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
			wp_nonce_field( 'bulk-' . $this->_args['plural'], '_wpnonce', false );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
			<?php endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>
			<br class="clear" />
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
		$donors                 = Give()->donors->get_donors( $_donor_query );

		return count( $donors );
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

		if ( $search ) {
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
	 * Generates content for a single row of the table
	 *
	 * @param object $item The current item.
	 *
	 * @since  1.8.17
	 * @access public
	 */
	public function single_row( $item ) {
		echo sprintf( '<tr id="donor-%1$d" data-id="%2$d" data-name="%3$s">', $item['id'], $item['id'], $item['name'] );
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Display the final donor table
	 *
	 * @since 1.8.17
	 * @access public
	 */
	public function display() {
		$singular = $this->_args['singular'];

		$this->display_tablenav( 'top' );

		$this->screen->render_screen_reader_content( 'heading_list' );

		$get_data = give_clean( $_GET ); // WPCS: input var ok, sanitization ok, CSRF ok.

		$search_keyword = ! empty( $get_data['s'] ) ? $get_data['s'] : '';
		$order          = ! empty( $get_data['order'] ) ? $get_data['order'] : 'DESC';
		$order_by       = ! empty( $get_data['orderby'] ) ? $get_data['orderby'] : 'ID';
		?>
		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
			<tr>
				<?php $this->print_column_headers(); ?>
			</tr>
			</thead>

			<tbody id="the-list"<?php
			if ( $singular ) {
				echo " data-wp-lists='list:$singular'";
			} ?>>
			<tr class="hidden"></tr>
			<tr id="give-bulk-delete" class="inline-edit-row inline-edit-row-page inline-edit-page bulk-edit-row bulk-edit-row-page bulk-edit-page inline-editor" style="display: none;" >
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
								<input class="give-donor-delete-confirm" type="checkbox" name="give-donor-delete-confirm"/>
								<?php esc_attr_e( 'Are you sure you want to delete the selected donor(s)?', 'give' ); ?>
							</label>
							<label>
								<input class="give-donor-delete-records" type="checkbox" name="give-donor-delete-records"/>
								<?php esc_attr_e( 'Delete all associated donations and records?', 'give' ); ?>
							</label>
						</div>
					</fieldset>

					<p class="submit inline-edit-save">
						<input type="hidden" name="give_action" value="delete_bulk_donor"/>
						<input type="hidden" name="s" value="<?php echo esc_html( $search_keyword ); ?>"/>
						<input type="hidden" name="orderby" value="<?php echo esc_html( $order_by ); ?>"/>
						<input type="hidden" name="order" value="<?php echo esc_html( $order ); ?>"/>
						<button type="button" id="give-bulk-delete-cancel" class="button cancel alignleft"><?php esc_attr_e( 'Cancel', 'give' ); ?></button>
						<input type="submit" id="give-bulk-delete-button" disabled class="button button-primary alignright" value="<?php esc_attr_e( 'Delete', 'give' ); ?>">
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

		$this->set_pagination_args( array(
			'total_items' => $this->total,
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->total / $this->per_page ),
		) );
	}
}
