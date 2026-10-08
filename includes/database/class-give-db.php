<?php
/**
 * Give DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_DB Class
 *
 * This class is for interacting with the database table.
 *
 * @since 1.0
 */
abstract class Give_DB {

	/**
	 * The name of our database table
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $table_name;

	/**
	 * Set Minimum Index Length
	 *
	 * @since  2.0.1
	 * @access public
	 *
	 * @var int
	 */
	public $min_index_length = 191;

	/**
	 * The version of our database table
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $version;

	/**
	 * The name of the primary column
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    string
	 */
	public $primary_key;

	/**
	 * Class Constructor
	 *
	 * Set up the Give DB Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		if ( is_multisite() ) {
			add_action( 'switch_blog', array( $this, 'handle_switch_blog' ), 10, 2 );
		}
	}

	/**
	 * Whitelist of columns
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @since TBD Use %i for table and column names.
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $row_id Row ID.
	 *
	 * @return object
	 */
	public function get( $row_id ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $row_id ) ) {
			return null;
		}

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE %i = %s LIMIT 1;', $this->table_name, $this->primary_key, $row_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @since TBD Use %i for table and column names.
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $column Column ID.
	 * @param  int $row_id Row ID.
	 *
	 * @return object
	 */
	public function get_by( $column, $row_id ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $column ) || empty( $row_id ) ) {
			return null;
		}

		return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM %i WHERE %i = %s LIMIT 1;', $this->table_name, $column, $row_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
	}

	/**
	 * Retrieve all rows by a specific column / value
	 * Note: currently support string comparision
	 *
	 * @since TBD Prepare each condition and allow only AND or OR as the relation.
	 * @since  2.2.4
	 * @access public
	 *
	 * @param array $column_args Array contains column key and expected value.
	 *
	 * @return array
	 */
	public function get_results_by( $column_args ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $column_args ) ) {
			return null;
		}

		$column_args = wp_parse_args(
			$column_args,
			array(
				'relation' => 'AND',
			)
		);

		$relation = 'OR' === strtoupper( (string) $column_args['relation'] ) ? 'OR' : 'AND';
		unset( $column_args['relation'] );

		$where = array();
		foreach ( $column_args as $column_name => $column_value ) {
			$where[] = $wpdb->prepare( '%i = %s', $column_name, $column_value );
		}
		$where = implode( " {$relation} ", $where );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- table_name is the class table property; $where is built from prepare() clauses and an allowlisted AND/OR above. Base class of the GiveWP custom tables; WordPress has no cache layer for them.
		return $wpdb->get_results( "SELECT * FROM {$this->table_name} WHERE {$where};" );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @since TBD Use %i for table and column names.
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $column Column ID.
	 * @param  int $row_id Row ID.
	 *
	 * @return string      Column value.
	 */
	public function get_column( $column, $row_id ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $column ) || empty( $row_id ) ) {
			return null;
		}

		return $wpdb->get_var( $wpdb->prepare( 'SELECT %i FROM %i WHERE %i = %s LIMIT 1;', $column, $this->table_name, $this->primary_key, $row_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @since TBD Use %i for table and column names.
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $column       Column ID.
	 * @param  string $column_where Column name.
	 * @param  string $column_value Column value.
	 *
	 * @return string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Bailout.
		if ( empty( $column ) || empty( $column_where ) || empty( $column_value ) ) {
			return null;
		}

		return $wpdb->get_var( $wpdb->prepare( 'SELECT %i FROM %i WHERE %i = %s LIMIT 1;', $column, $this->table_name, $column_where, $column_value ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
	}

	/**
	 * Insert a new row
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array  $data
	 * @param  string $type
	 *
	 * @return int
	 */
	public function insert( $data, $type = '' ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Set default values.
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		/**
		 * Fires before inserting data to the database.
		 *
		 * @since 1.0
		 *
		 * @param array $data
		 */
		do_action( "give_pre_insert_{$type}", $data );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		// $data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.

		/**
		 * Fires after inserting data to the database.
		 *
		 * @since 1.0
		 *
		 * @param int   $insert_id
		 * @param array $data
		 */
		do_action( "give_post_insert_{$type}", $wpdb->insert_id, $data );

		return $wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $row_id Column ID
	 * @param  array  $data
	 * @param  string $where  Column value
	 *
	 * @return bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( empty( $where ) ) {
			$where = $this->primary_key;
		}

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @since TBD Use %i for table and column names.
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $row_id Column ID.
	 *
	 * @return bool
	 */
	public function delete( $row_id = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );

		if ( empty( $row_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE %i = %d', $this->table_name, $this->primary_key, $row_id ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Base class of the GiveWP custom tables; WordPress has no cache layer for them.
			return false;
		}

		return true;
	}

	/**
	 * Check if the given table exists
	 *
	 * @since TBD Remove quotes around the %s placeholder.
	 * @since  1.3.2
	 * @access public
	 *
	 * @param  string $table The table name.
	 *
	 * @return bool          If the table name exists.
	 */
	public function table_exists( $table ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		$table = sanitize_text_field( $table );

		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checks whether a table exists with SHOW TABLES; WordPress has no function for it.
	}

	/**
	 * Checks whether column exists in a table or not.
	 *
	 * @param string $column_name Name of the Column in Database Table.
	 *
	 * @since 1.8.18
	 *
	 * @see https://gist.github.com/datafeedr/54e89e07f87232fb055121bb766743fe
	 *
	 * @return bool
	 */
	public function does_column_exist( $column_name ) {

		global $wpdb;

		$column = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Checks whether a column exists in INFORMATION_SCHEMA; WordPress has no function for it.
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
				DB_NAME,
				$this->table_name,
				$column_name
			)
		);

		if ( ! empty( $column ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the table was ever installed
	 *
	 * @since  1.6
	 * @access public
	 *
	 * @return bool Returns if the customers table was installed and upgrade routine run.
	 */
	public function installed() {
		return $this->table_exists( $this->table_name );
	}

	/**
	 * Register tables
	 *
	 * @since  1.8.9
	 * @access public
	 */
	public function register_table() {
		$current_version = get_option( $this->table_name . '_db_version' );
		if ( ! $current_version || version_compare( $current_version, $this->version, '<' ) ) {
			$this->create_table();
		}
	}

	/**
	 * Create table
	 *
	 * @since  1.8.9
	 * @access public
	 */
	public function create_table() {
	}


	/**
	 * Given a ID, make sure it's a positive number, greater than zero before inserting or adding.
	 *
	 * @access private
	 * @since  2.0
	 *
	 * @param  int $id A passed ID.
	 *
	 * @return int|bool                The normalized log ID or false if it's found to not be valid.
	 */
	public function sanitize_id( $id ) {
		if ( ! is_numeric( $id ) ) {
			return false;
		}

		$id = (int) $id;

		// We were given a non positive number.
		if ( absint( $id ) !== $id ) {
			return false;
		}

		if ( empty( $id ) ) {
			return false;
		}

		return absint( $id );

	}

	/**
	 * Handle switch blog on multi-site
	 *
	 * @since  2.0.4
	 *
	 * @access public
	 *
	 * @param $new_blog_id
	 * @param $prev_blog_id
	 */
	public function handle_switch_blog( $new_blog_id, $prev_blog_id ) {
		global $wpdb;

		// Bailout.
		if ( $new_blog_id === $prev_blog_id ) {
			return;
		}

		$this->table_name = str_replace(
			1 != $prev_blog_id ? $wpdb->get_blog_prefix( $prev_blog_id ) : $wpdb->base_prefix,
			1 != $new_blog_id ? $wpdb->get_blog_prefix( $new_blog_id ) : $wpdb->base_prefix,
			$this->table_name
		);

		if ( $this instanceof Give_DB_Meta ) {
			$wpdb->{$this->get_meta_type() . 'meta'} = $this->table_name;
		}

	}
}
