<?php
/**
 * Donors Query
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.14
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donors_Query Class
 *
 * This class is for retrieving donors data.
 *
 * Donors can be retrieved for date ranges and pre-defined periods.
 *
 * @since 1.8.14
 */
class Give_Donors_Query {

	/**
	 * The args to pass to the give_get_donors() query
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $args = [];

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $donors = [];

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    string
	 */
	public $table_name = '';

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    string
	 */
	public $meta_table_name = '';

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    string
	 */
	public $meta_type = '';

	/**
	 * Preserve args
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @var    array
	 */
	public $_args = [];

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @param  $args array The array of arguments that can be passed in and used for setting up this payment query.
	 */
	public function __construct( $args = [] ) {
		$defaults = [
			'number'          => 20,
			'offset'          => 0,
			'paged'           => 1,
			'orderby'         => 'id',
			'order'           => 'DESC',
			'user'            => null,
			'email'           => null,
			'donor'           => null,
			'meta_query'      => [],
			'date_query'      => [],
			's'               => null,
			'fields'          => 'all', // Supports donors (all fields) or valid column as string or array list.
			'count'           => false,
			'give_forms'      => [],
			'start_date'      => false,
			'end_date'        => false,

			/**
			 * donation_amount will contain value like:
			 * array(
			 *     'compare' => *compare symbol* (by default set to > )
			 *     'amount'  => *numeric_value*
			 * )
			 *
			 * You can also pass number value to this param then compare symbol will auto set to >
			 */
			'donation_amount' => [],
		];

		$this->args            = $this->_args = wp_parse_args( $args, $defaults );
		$this->table_name      = Give()->donors->table_name;
		$this->meta_table_name = Give()->donor_meta->table_name;
		$this->meta_type       = Give()->donor_meta->meta_type;

		$this->date_filter_pre();
	}

	/**
	 * Modify the query/query arguments before we retrieve donors.
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @return void
	 */
	public function init() {
	}


	/**
	 * Retrieve donors.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @global wpdb $wpdb
	 *
	 * @return array|object|string|null
	 */
	public function get_donors() {
		global $wpdb;

		/**
		 * Fires before retrieving donors.
		 *
		 * @since 1.8.14
		 *
		 * @param Give_Donors_Query $this Donors query object.
		 */
		do_action( 'give_pre_get_donors', $this );

		$cache_key = Give_Cache::get_key( 'give_donor', $this->get_sql(), false );

		// Get donors from cache.
		$this->donors = Give_Cache::get_db_query( $cache_key );

		if ( null === $this->donors ) {
			if ( empty( $this->args['count'] ) ) {
				$this->donors = $wpdb->get_results( $this->get_sql() );
				self::update_meta_cache( wp_list_pluck( (array) $this->donors, 'id' ) );
			} else {
				$this->donors = $wpdb->get_var( $this->get_sql() );
			}

			Give_Cache::set_db_query( $cache_key, $this->donors );
		}

		/**
		 * Fires after retrieving donors.
		 *
		 * @since 1.8.14
		 *
		 * @param Give_Donors_Query $this Donors query object.
		 */
		do_action( 'give_post_get_donors', $this );

		return $this->donors;
	}

	/**
	 * Get sql query from queried array.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	public function get_sql() {
		global $wpdb;

		if ( $this->args['number'] < 1 ) {
			$this->args['number'] = 99999999999;
		}

		$where = $this->get_where_query();

		// Set offset.
		if ( empty( $this->args['offset'] ) && ( 0 < $this->args['paged'] ) ) {
			$this->args['offset'] = $this->args['number'] * ( $this->args['paged'] - 1 );
		}

		// Set fields.
		$fields = "{$this->table_name}.*";
		if ( ! empty( $this->args['fields'] ) && 'all' !== $this->args['fields'] ) {
			if ( is_string( $this->args['fields'] ) ) {
				$fields = "{$this->table_name}.{$this->args['fields']}";
			} elseif ( is_array( $this->args['fields'] ) ) {
				$fields = "{$this->table_name}." . implode( " , {$this->table_name}.", $this->args['fields'] );
			}
		}

		// Set count.
		if ( ! empty( $this->args['count'] ) ) {
			$fields = "COUNT({$this->table_name}.id)";
		}

		$orderby = $this->get_order_query();

		$sql = $wpdb->prepare( "SELECT {$fields} FROM {$this->table_name} LIMIT %d,%d;", absint( $this->args['offset'] ), absint( $this->args['number'] ) );

		// $where, $orderby and order already prepared query they can generate notice if you re prepare them in above.
		// WordPress consider LIKE condition as placeholder if start with s,f, or d.
		$sql = str_replace( 'LIMIT', "{$where} {$orderby} LIMIT", $sql );

		return $sql;
	}

	/**
	 * Set query where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_query() {

		// Get sql query for meta.
		if ( ! empty( $this->args['meta_query'] ) ) {
			$meta_query_object = new WP_Meta_Query( $this->args['meta_query'] );
			$meta_query        = $meta_query_object->get_sql( $this->meta_type, $this->table_name, 'id' );

			$where[] = implode( '', $meta_query );
		}

		$where[] = 'WHERE 1=1';
		$where[] = $this->get_where_search();
		$where[] = $this->get_where_email();
		$where[] = $this->get_where_donor();
		$where[] = $this->get_where_user();
		$where[] = $this->get_where_date();
		$where[] = $this->get_where_donation_amount();
		$where[] = $this->get_where_donation_count();
		$where[] = $this->get_where_give_forms();

		$where = array_filter( $where );

		return trim( implode( ' ', array_map( 'trim', $where ) ) );

	}

	/**
	 * Set email where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_email() {
		global $wpdb;

		$where = '';

		if ( ! empty( $this->args['email'] ) ) {

			if ( is_array( $this->args['email'] ) ) {

				$emails_count       = count( $this->args['email'] );
				$emails_placeholder = array_fill( 0, $emails_count, '%s' );
				$emails             = implode( ', ', $emails_placeholder );

				$where .= $wpdb->prepare( "AND {$this->table_name}.email IN( $emails )", $this->args['email'] );
			} else {
				$where .= $wpdb->prepare( "AND {$this->table_name}.email = %s", $this->args['email'] );
			}
		}

		return $where;
	}

	/**
	 * Set donor where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_donor() {
		$where = '';

		// Specific donors.
		if ( ! empty( $this->args['donor'] ) ) {
			if ( ! is_array( $this->args['donor'] ) ) {
				$this->args['donor'] = explode( ',', $this->args['donor'] );
			}
			$donor_ids = implode( ',', array_map( 'intval', $this->args['donor'] ) );

			$where .= "AND {$this->table_name}.id IN( {$donor_ids} )";
		}

		return $where;
	}

	/**
	 * Set date where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_date() {
		$where = '';

		// Donors created for a specific date or in a date range
		if ( ! empty( $this->args['date_query'] ) ) {
			$date_query_object = new WP_Date_Query( is_array( $this->args['date_query'] ) ? $this->args['date_query'] : wp_parse_args( $this->args['date_query'] ), "{$this->table_name}.date_created" );

			$where .= str_replace(
				[
					"\n",
					'(   (',
					'))',
				],
				[
					'',
					'( (',
					') )',
				],
				$date_query_object->get_sql()
			);
		}

		return $where;
	}

	/**
	 * Set search where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_search() {
		$where = '';

		// Bailout.
		if ( empty( $this->args['s'] ) ) {
			return $where;
		}

		// Donors created for a specific date or in a date range
		if ( false !== strpos( $this->args['s'], ':' ) ) {
			$search_parts = explode( ':', $this->args['s'] );
			if ( ! empty( $search_parts[0] ) ) {
				switch ( $search_parts[0] ) {
					// Backward compatibility.
					case 'name':
						$where = "AND {$this->table_name}.name LIKE '%{$search_parts[1]}%'";
						break;
					case 'note':
						$where = "AND {$this->table_name}.notes LIKE '%{$search_parts[1]}%'";
						break;
				}
			}
		} elseif ( is_numeric( $this->args['s'] ) ) {
			$where = "AND {$this->table_name}.id ='{$this->args['s']}'";

		} else {
			$search_field = is_email( $this->args['s'] ) ? 'email' : 'name';
			$where        = "AND {$this->table_name}.$search_field LIKE '%{$this->args['s']}%'";
		}

		return $where;
	}

	/**
	 * Set user where clause.
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_user() {
		$where = '';

		// Donors create for specific wp user.
		if ( ! empty( $this->args['user'] ) ) {
			if ( ! is_array( $this->args['user'] ) ) {
				$this->args['user'] = explode( ',', $this->args['user'] );
			}
			$user_ids = implode( ',', array_map( 'intval', $this->args['user'] ) );

			$where .= "AND {$this->table_name}.user_id IN( {$user_ids} )";
		}

		return $where;
	}

	/**
	 * Set orderby query
	 *
	 * @since  1.8.14
	 * @access private
	 *
	 * @return string
	 */
	private function get_order_query() {
		$table_columns = Give()->donors->get_columns();

		$query    = [];
		$ordersby = $this->args['orderby'];

		if ( ! is_array( $ordersby ) ) {
			$ordersby = [
				$this->args['orderby'] => $this->args['order'],
			];
		}

		// Remove non existing column.
		// Filter orderby values.
		foreach ( $ordersby as $orderby => $order ) {
			if ( ! array_key_exists( $orderby, $table_columns ) ) {
				unset( $ordersby[ $orderby ] );
				continue;
			}

			$ordersby[ esc_sql( $orderby ) ] = esc_sql( $order );
		}

		if ( empty( $ordersby ) ) {
			$ordersby = [
				'id' => $this->args['order'],
			];
		}

		// Create query.
		foreach ( $ordersby as $orderby => $order ) {
			switch ( $table_columns[ $orderby ] ) {
				case '%d':
				case '%f':
					$query[] = "{$this->table_name}.{$orderby}+0 {$order}";
					break;

				default:
					$query[] = "{$this->table_name}.{$orderby} {$order}";
			}
		}

		return ! empty( $query ) ? 'ORDER BY ' . implode( ', ', $query ) : '';
	}

	/**
	 * Set donation count value where clause.
	 *
	 * @todo: add phpunit test
	 *
	 * @since  2.2.0
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_donation_count() {
		$where = '';

		if ( ! empty( $this->args['donation_count'] ) ) {
			$compare = '>';
			$amount  = $this->args['donation_count'];
			if ( is_array( $this->args['donation_count'] ) ) {
				$compare = $this->args['donation_count'] ['compare'];
				$amount  = $this->args['donation_count']['amount'];
			}

			$where .= "AND {$this->table_name}.purchase_count{$compare}{$amount}";
		}

		return $where;
	}

	/**
	 * Set purchase value where clause.
	 *
	 * @todo: add phpunit test
	 *
	 * @since  2.1.0
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_donation_amount() {
		$where = '';

		if ( ! empty( $this->args['donation_amount'] ) ) {
			$compare = '>';
			$amount  = $this->args['donation_amount'];
			if ( is_array( $this->args['donation_amount'] ) ) {
				$compare = $this->args['donation_amount'] ['compare'];
				$amount  = $this->args['donation_amount']['amount'];
			}

			$where .= "AND {$this->table_name}.purchase_value{$compare}{$amount}";
		}

		return $where;
	}

	/**
	 * Set give_forms where clause.
	 *
	 * @todo   : add phpunit test
	 *
	 * @since  2.1.0
	 * @access private
	 *
	 * @global wpdb $wpdb
	 * @return string
	 */
	private function get_where_give_forms() {
		global $wpdb;
		$where = '';

		if ( ! empty( $this->args['give_forms'] ) ) {
			if ( ! is_array( $this->args['give_forms'] ) ) {
				$this->args['give_forms'] = explode( ',', $this->args['give_forms'] );
			}

			$form_ids        = implode( ',', array_map( 'intval', $this->args['give_forms'] ) );
			$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';

			$query = $wpdb->prepare(
				"
			SELECT DISTINCT meta_value as donor_id
			FROM {$wpdb->donationmeta}
			WHERE meta_key=%s
			AND {$donation_id_col} IN(
				SELECT {$donation_id_col}
				FROM {$wpdb->paymentmeta}
				WHERE meta_key=%s
				AND meta_value IN (%s)
			)
			",
				'_give_payment_donor_id',
				'_give_payment_form_id',
				$form_ids
			);

			$donor_ids = $wpdb->get_results( $query, ARRAY_A );

			if ( ! empty( $donor_ids ) ) {
				$donor_ids = wp_list_pluck( $donor_ids, 'donor_id' );
				$donor_ids = implode( ',', array_map( 'intval', $donor_ids ) );
				$where    .= "AND {$this->table_name}.id IN ({$donor_ids})";
			} else {
				$where .= "AND {$this->table_name}.id IN ('0')";
			}
		}

		return $where;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 * Note: This function currently only accept dates with admin defined core date format
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @return void
	 */
	public function date_filter_pre() {
		if (
			! empty( $this->args['date_query'] )
			|| empty( $this->args['start_date'] )
			|| empty( $this->args['end_date'] )
		) {
			return;
		}

		$date_query = [];

		if ( ! empty( $this->args['start_date'] ) ) {
			$date_query['after'] = date(
				'Y-m-d H:i:s',
				is_numeric( $this->args['start_date'] )
					? $this->args['start_date']
					: strtotime( $this->args['start_date'] )
			);
		}

		if ( ! empty( $this->args['end_date'] ) ) {
			$date_query['before'] = date(
				'Y-m-d H:i:s',
				is_numeric( $this->args['end_date'] )
					? $this->args['end_date']
					: strtotime( $this->args['end_date'] )
			);
		}

		// Include Start Date and End Date while querying.
		$date_query['inclusive'] = true;

		$this->__set( 'date_query', $date_query );
	}

	/**
	 * Update donors meta cache
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @param array $donor_ids
	 */
	public static function update_meta_cache( $donor_ids ) {
		// Exit.
		if ( empty( $donor_ids ) ) {
			return;
		}

		update_meta_cache( Give()->donor_meta->get_meta_type(), $donor_ids );
	}

	/**
	 * Set a query variable.
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @param $query_var
	 * @param $value
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, [ 'meta_query', 'tax_query' ] ) ) {
			$this->args[ $query_var ][] = $value;
		} else {
			$this->args[ $query_var ] = $value;
		}
	}

}
