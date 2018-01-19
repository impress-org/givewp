<?php
/**
 * Donors Query
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2017, WordImpress
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
	public $args = array();

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $donors = array();

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $table_name = '';

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $meta_table_name = '';

	/**
	 * The donors found based on the criteria set
	 *
	 * @since  1.8.14
	 * @access public
	 *
	 * @var    array
	 */
	public $meta_type = '';

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
	public function __construct( $args = array() ) {
		$defaults = array(
			'number'     => 20,
			'offset'     => 0,
			'paged'      => 1,
			'orderby'    => 'id',
			'order'      => 'DESC',
			'user'       => null,
			'email'      => null,
			'donor'      => null,
			'meta_query' => array(),
			'date_query' => array(),
			's'          => null,
			'fields'     => 'all', // Support donors (all fields) or valid column  as string or array list
			'count'      => false,
			// 'form'       => array(),
		);

		$this->args = wp_parse_args( $args, $defaults );
		$this->table_name      = Give()->donors->table_name;
		$this->meta_table_name = Give()->donor_meta->table_name;
		$this->meta_type       = Give()->donor_meta->meta_type;
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
	 * @return array
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

		$cache_key        = Give_Cache::get_key( 'give_donor', $this->get_sql(), false );

		// Get donors from cache.
		$this->donors = Give_Cache::get_db_query( $cache_key );

		if ( is_null( $this->donors  ) ) {
			if ( empty( $this->args['count'] ) ) {
				$this->donors = $wpdb->get_results( $this->get_sql() );
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

		$sql = $wpdb->prepare(
			"SELECT {$fields} FROM {$this->table_name} LIMIT %d,%d;",
			absint( $this->args['offset'] ),
			absint( $this->args['number'] )
		);

		// $where, $orderby and order already prepared query they can generate notice if you re prepare them in above.
		// WordPress consider LIKE condition as placeholder if start with s,f, or d.
		$sql = str_replace( 'LIMIT', "{$where} {$orderby} {$this->args['order']} LIMIT", $sql );

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
		$where = '';

		// Get sql query for meta.
		if ( ! empty( $this->args['meta_query'] ) ) {
			$meta_query_object = new WP_Meta_Query( $this->args['meta_query'] );
			$meta_query        = $meta_query_object->get_sql(
				$this->meta_type,
				$this->table_name,
				'id'
			);

			$where = implode( '', $meta_query );
		}

		$where .= 'WHERE 1=1 ';
		$where .= $this->get_where_search();
		$where .= $this->get_where_email();
		$where .= $this->get_where_donor();
		$where .= $this->get_where_user();
		$where .= $this->get_where_date();

		return trim( $where );
		
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
			$date_query_object = new WP_Date_Query(
				is_array( $this->args['date_query'] ) ? $this->args['date_query'] : wp_parse_args( $this->args['date_query'] ),
				"{$this->table_name}.date_created"
			);

			$where .= str_replace(
				array(
					"\n",
					'(   (',
					'))',
				),
				array(
					'',
					'( (',
					') )',
				),
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

		// Donors created for a specific date or in a date range
		if ( ! empty( $this->args['s'] ) && false !== strpos( $this->args['s'], ':' ) ) {
			$search_parts = explode( ':', $this->args['s'] );

			if ( ! empty( $search_parts[0] ) ) {
				switch ( $search_parts[0] ) {
					case 'name':
						$where = "AND {$this->table_name}.name LIKE '%{$search_parts[1]}%'";
						break;

					case 'note':
						$where = "AND {$this->table_name}.notes LIKE '%{$search_parts[1]}%'";
						break;
				}
			}
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

		$this->args['orderby'] = ! array_key_exists( $this->args['orderby'], $table_columns ) ?
			'id' :
			$this->args['orderby'];

		$this->args['orderby'] = esc_sql( $this->args['orderby'] );
		$this->args['order']   = esc_sql( $this->args['order'] );

		switch ( $table_columns[ $this->args['orderby'] ] ) {
			case '%d':
			case '%f':
				$query = "ORDER BY {$this->table_name}.{$this->args['orderby']}+0";
				break;

			default:
				$query = "ORDER BY {$this->table_name}.{$this->args['orderby']}";
		}

		return $query;
	}
}
