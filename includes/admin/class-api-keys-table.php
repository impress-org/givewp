<?php
/**
 * API Key Table Class
 *
 * @package     Give
 * @subpackage  Admin/Tools/APIKeys
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.1
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
 * Give_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since 1.1
 */
class Give_API_Keys_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 1.1
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 1.1
	 */
	private $keys;

	/**
	 * Get things started
	 *
	 * @since 1.1
	 * @see   WP_List_Table::__construct()
	 *
	 * @global $status
	 * @global $page
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => esc_html__( 'API Key', 'give' ),     // Singular name of the listed records
				'plural'   => esc_html__( 'API Keys', 'give' ),    // Plural name of the listed records
				'ajax'     => false, // Does this table support ajax?
			)
		);

		$this->query();
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array  $item        Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Displays the public key rows
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array $item Contains all the data of the keys
	 *
	 * @return string Column Name
	 */
	public function column_key( $item ) {
		return '<input onClick="this.setSelectionRange(0, this.value.length)" readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item['key'] ) . '"/>';
	}

	/**
	 * Displays the token rows
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array $item Contains all the data of the keys
	 *
	 * @return string Column Name
	 */
	public function column_token( $item ) {
		return '<input onClick="this.setSelectionRange(0, this.value.length)" readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item['token'] ) . '"/>';
	}

	/**
	 * Displays the secret key rows
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param array $item Contains all the data of the keys
	 *
	 * @return string Column Name
	 */
	public function column_secret( $item ) {
		return '<input onClick="this.setSelectionRange(0, this.value.length)" readonly="readonly" type="text" class="large-text" value="' . esc_attr( $item['secret'] ) . '"/>';
	}

	/**
	 * Renders the column for the user field
	 *
	 * @access public
	 * @since  1.1
	 * @return string
	 */
	public function column_user( $item ) {

		$actions = array();

		if ( apply_filters( 'give_api_log_requests', true ) ) {
			$actions['view'] = sprintf(
				'<a href="%s">%s</a>',
				esc_url(
					add_query_arg(
						array(
							'section'   => 'api_requests',
							'post_type' => 'give_forms',
							'page'      => 'give-tools',
							'tab'       => 'logs',
							's'         => $item['email'],
						),
						'edit.php'
					)
				),
				esc_html__( 'View API Log', 'give' )
			);
		}

		$actions['reissue'] = sprintf(
			'<a href="%s" class="give-regenerate-api-key">%s</a>',
			esc_url(
				wp_nonce_url(
					add_query_arg(
						array(
							'user_id'          => $item['id'],
							'give_action'      => 'process_api_key',
							'give_api_process' => 'regenerate',
						)
					),
					'give-api-nonce'
				)
			),
			esc_html__( 'Reissue', 'give' )
		);
		$actions['revoke']  = sprintf(
			'<a href="%s" class="give-revoke-api-key give-delete">%s</a>',
			esc_url(
				wp_nonce_url(
					add_query_arg(
						array(
							'user_id'          => $item['id'],
							'give_action'      => 'process_api_key',
							'give_api_process' => 'revoke',
						)
					),
					'give-api-nonce'
				)
			),
			esc_html__( 'Revoke', 'give' )
		);

		$actions = apply_filters( 'give_api_row_actions', array_filter( $actions ) );

		return sprintf( '%1$s %2$s', $item['user'], $this->row_actions( $actions ) );
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
		return 'user';
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since  1.1
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'user'   => esc_html__( 'Username', 'give' ),
			'key'    => esc_html__( 'Public Key', 'give' ),
			'token'  => esc_html__( 'Token', 'give' ),
			'secret' => esc_html__( 'Secret Key', 'give' ),
		);

		return $columns;
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since  3.1.0
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
	 * Display the key generation form
	 *
	 * @access public
	 * @since  1.1
	 *
	 * @param  string $which
	 *
	 * @return void
	 */
	function bulk_actions( $which = '' ) {
		// These aren't really bulk actions but this outputs the markup in the right place.
		static $give_api_is_bottom;

		if ( $give_api_is_bottom ) {
			return;
		}
		?>
		<input type="hidden" name="give_action" value="process_api_key"/>
		<input type="hidden" name="give_api_process" value="generate"/>
		<?php
		wp_nonce_field( 'give-api-nonce' );
		/**
		 * API Key user search.
		 */
		$args = array(
			'id'   => 'give-api-user-search',
			'name' => 'user_id',
		);

		echo Give()->html->ajax_user_search( $args );

		submit_button( esc_html__( 'Generate New API Keys', 'give' ), 'secondary', 'submit', false );

		$give_api_is_bottom = true;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since  1.1
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Performs the key query
	 *
	 * @access public
	 * @since  1.1
	 * @return array
	 */
	public function query() {
		$users = get_users(
			array(
				'meta_value' => 'give_user_secret_key',
				'number'     => $this->per_page,
				'offset'     => $this->per_page * ( $this->get_paged() - 1 ),
			)
		);
		$keys  = array();

		foreach ( $users as $user ) {
			$keys[ $user->ID ]['id']    = $user->ID;
			$keys[ $user->ID ]['email'] = $user->user_email;
			$keys[ $user->ID ]['user']  = '<a href="' . esc_url( add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) ) . '"><strong>' . $user->user_login . '</strong></a>';

			$keys[ $user->ID ]['key']    = Give()->api->get_user_public_key( $user->ID );
			$keys[ $user->ID ]['secret'] = Give()->api->get_user_secret_key( $user->ID );
			$keys[ $user->ID ]['token']  = Give()->api->get_token( $user->ID );
		}

		return $keys;
	}


	/**
	 * Retrieve count of total users with keys
	 *
	 * @access public
	 * @since  1.1
	 * @return int
	 */
	public function total_items() {
		global $wpdb;

		$total_items = Give_Cache::get( 'give_total_api_keys', true );

		if ( ! $total_items ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT count(user_id)
					FROM {$wpdb->usermeta} WHERE meta_value=%s",
					'give_user_secret_key'
				)
			);

			Give_Cache::set( 'give_total_api_keys', $total_items, HOUR_IN_SECONDS, true );
		}

		return $total_items;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since  1.1
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden   = array(); // No hidden columns
		$sortable = array(); // Not sortable... for now

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->query();

		$total_items = $this->total_items();

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page ),
			)
		);
	}
}
