<?php
/**
 * Donors DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Logs
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB_Logs Class
 *
 * This class is for interacting with the log database table.
 *
 * @since 2.0
 */
class Give_DB_Logs extends Give_DB {

	/**
	 * Give_DB_Logs constructor.
	 *
	 * Set up the Give DB Donor class.
	 *
	 * @since  2.0
	 * @access public
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'give_logs';
		$this->primary_key = 'ID';
		$this->version     = '1.0';

		// Install table.
		$this->register_table();

	}

	/**
	 * Get columns and formats
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'ID'           => '%d',
			'log_title'    => '%s',
			'log_content'  => '%s',
			'log_parent'   => '%d',
			'log_type'     => '%s',
			'log_date'     => '%s',
			'log_date_gmt' => '%s',
		);
	}

	/**
	 * Get default column values
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		$log_create_date     = current_time( 'mysql', 0 );
		$log_create_date_gmt = get_gmt_from_date( $log_create_date );

		return array(
			'ID'           => 0,
			'log_title'    => '',
			'log_content'  => '',
			'log_parent'   => 0,
			'log_type'     => '',
			'log_date'     => $log_create_date,
			'log_date_gmt' => $log_create_date_gmt,
		);
	}

	/**
	 * Add a log
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param  array $data
	 *
	 * @return bool|int
	 */
	public function add( $data = array() ) {
		// Valid table columns.
		$table_columns = array_keys( $this->get_columns() );

		// Filter data.
		foreach ( $data as $table_column => $column_data ) {
			if ( ! in_array( $table_column, $table_columns ) ) {
				unset( $data[ $table_column ] );
			}
		}

		// Set default values.
		$current_log_data = wp_parse_args( $data, $this->get_column_defaults() );

		// Log parent should be an int.
		$current_log_data['parent'] = absint( $current_log_data['parent'] );

		// Get log.
		$existing_log = $this->get_log_by( $current_log_data['id'] );

		// Update an existing log.
		if ( $existing_log ) {

			// Create new log data from existing and new log data.
			$current_log_data = array_merge( $current_log_data, $existing_log );

			// Update log data.
			$this->update( $current_log_data['id'], $current_log_data );

			$log_id = $current_log_data['id'];

		} else {
			$log_id = $this->insert( $current_log_data, 'log' );
		}

		return $log_id;
	}


	/**
	 * Retrieves a single log from the database
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int $log_id
	 *
	 * @return bool|null|array
	 */
	public function get_log_by( $log_id = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Make sure $log_id is int.
		$log_id = absint( $log_id );

		// Bailout.
		if ( empty( $log_id ) ) {
			return null;
		}

		if ( ! $log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE ID = %s LIMIT 1", $log_id ), ARRAY_A ) ) {
			return false;
		}

		return $log;
	}

	/**
	 * Retrieve logs from the database.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return mixed
	 */
	public function get_logs( $args = array() ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'log_id'  => 0,
			'orderby' => 'date',
			'order'   => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		// Where clause for primary table.
		$where = '';

		// Get sql query for meta.
		if ( ! empty( $args['meta_query'] ) ) {
			$meta_query_object = new WP_Meta_Query( $args['meta_query'] );
			$meta_query        = $meta_query_object->get_sql( 'log', $this->table_name, 'id' );
			$where             = implode( '', $meta_query );
		}

		$where .= ' WHERE 1=1 ';

		// Set offset.
		if ( empty( $args['offset'] ) && ( 0 < $args['paged'] ) ) {
			$args['offset'] = $args['number'] * ( $args['paged'] - 1 );
		}

		// Specific logs.
		if ( ! empty( $args['log_id'] ) ) {

			if ( is_array( $args['log_id'] ) ) {
				$log_ids = implode( ',', array_map( 'intval', $args['log_id'] ) );
			} else {
				$log_ids = intval( $args['log_id'] );
			}

			$where .= " AND {$this->table_name}.ID IN( {$log_ids} ) ";

		}

		// Logs created for a specific date or in a date range
		if ( ! empty( $args['date_query'] ) ) {
			$date_query_object = new WP_Date_Query( $args['date_query'], "{$this->table_name}.date" );
			$where             .= $date_query_object->get_sql();
		}

		// Logs create for specific parent.
		if ( ! empty( $args['parent'] ) ) {
			if ( is_array( $args['parent'] ) ) {
				$parent_ids = implode( ',', array_map( 'intval', $args['parent'] ) );
			} else {
				$parent_ids = intval( $args['parent'] );
			}

			$where .= " AND {$this->table_name}.log_parent IN( {$parent_ids} ) ";
		}

		// Logs create for specific type.
		if ( ! empty( $args['type'] ) ) {
			if ( ! is_array( $args['type'] ) ) {
				$args['type'] = explode( ',', $args['type'] );
			}

			$log_types = implode( '\',\'', array_map( 'trim', $args['type'] ) );

			$where .= " AND {$this->table_name}.log_type IN( '{$log_types}' ) ";
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'log_date' : $args['orderby'];

		// Get log from cache.
		$logs = Give_Cache::get( 'give_logs', true, $args );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $logs === false ) {
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT {$this->table_name}.* FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;",
					absint( $args['offset'] ),
					absint( $args['number'] )
				)
			);
			Give_Cache::set( 'give_logs', $logs, 3600, true, $args );
		}

		return $logs;
	}


	/**
	 * Count the total number of logs in the database
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param  array $args
	 *
	 * @return int
	 */
	public function count( $args = array() ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		$where = ' WHERE 1=1 ';

		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				$end   = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

				$where .= " AND `date_created` >= '{$start}' AND `date_created` <= '{$end}'";

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}

		}


		$cache_key = Give_Cache::get( 'give_logs_count', true, $args );

		$count = wp_cache_get( $cache_key, 'donors' );

		if ( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			Give_Cache::set( 'give_logs_count', $count, 3600, true, $args );
		}

		return absint( $count );
	}

	/**
	 * Create the table
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return void
	 */
	public function create_table() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$this->table_name} (
        ID bigint(20) NOT NULL AUTO_INCREMENT,
        log_title longtext NOT NULL,
        log_content longtext NOT NULL,
      	log_parent bigint(20) NOT NULL,
        log_type mediumtext NOT NULL,
        log_date datetime NOT NULL,
        log_date_gmt datetime NOT NULL,
        PRIMARY KEY  (ID)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
