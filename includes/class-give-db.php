<?php
/**
 * Give DB
 *
 * @package     Give
 * @subpackage  Classes/Give_DB
 * @copyright   Copyright (c) 2016, WordImpress
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

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
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

		$column = esc_sql( $column );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
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

		$column = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
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

		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
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
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

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

		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
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

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the given table exists
	 *
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

		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
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

}
