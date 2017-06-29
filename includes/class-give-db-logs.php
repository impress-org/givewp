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
		$this->primary_key = 'id';
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
			'id'       => '%d',
			'title'    => '%s',
			'content'  => '%s',
			'parent'   => '%d',
			'type'     => '%s',
			'date'     => '%s',
			'date_gmt' => '%s',
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
			'id'       => 0,
			'title'    => '',
			'content'  => '',
			'parent'   => 0,
			'type'     => '',
			'date'     => $log_create_date,
			'date_gmt' => $log_create_date_gmt,
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

		if ( ! $log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %s LIMIT 1", $log_id ), ARRAY_A ) ) {
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

		$where = ' WHERE 1=1 ';

		// Specific logs.
		if ( ! empty( $args['log_id'] ) ) {

			if ( is_array( $args['log_id'] ) ) {
				$log_ids = implode( ',', array_map( 'intval', $args['log_id'] ) );
			} else {
				$log_ids = intval( $args['log_id'] );
			}

			$where .= " AND `log_id` IN( {$log_ids} ) ";

		}

		// Logs created for a specific date or in a date range
		if ( ! empty( $args['date'] ) ) {

			if ( is_array( $args['date'] ) ) {

				if ( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					$where .= " AND `date_created` >= '{$start}'";

				}

				if ( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					$where .= " AND `date_created` <= '{$end}'";

				}
			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				$where .= " AND $year = YEAR ( date_created ) AND $month = MONTH ( date_created ) AND $day = DAY ( date_created )";
			}
		}

		// Logs create for specific parent.
		if( ! empty( $args['parent'] ) ) {
			if ( is_array( $args['parent'] ) ) {
				$parent_ids = implode( ',', array_map( 'intval', $args['parent'] ) );
			} else {
				$parent_ids = intval( $args['parent'] );
			}

			$where .= " AND `parent` IN( {$parent_ids} ) ";
		}

		// Logs create for specific type.
		if ( ! empty( $args['type'] ) ) {
			if ( ! is_array( $args['type'] ) ) {
				$log_types = explode( ',', $args['type'] );
			}

			$log_types = implode( '\',\'', array_map( 'trim', $log_types ) );

			$where .= " AND `type` IN( '{$log_types}' ) ";
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'date' : $args['orderby'];

		$logs = Give_Cache::get( 'give_logs', true, $args );

		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		if ( $logs === false ) {
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;",
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
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title longtext NOT NULL,
        content longtext NOT NULL,
      	parent bigint(20) NOT NULL,
        type mediumtext NOT NULL,
        date datetime NOT NULL,
        date_gmt datetime NOT NULL,
        PRIMARY KEY  (id)
        ) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
