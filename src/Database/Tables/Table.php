<?php
namespace Give\Database\Tables;

use wpdb;

/**
 * Class Table
 * @package Give\Database\Tables
 *
 * @since 2.9.0
 */
abstract class Table {
	/**
	 * The name of our database table
	 *
	 * @since  2.9.0
	 * @access protected
	 *
	 * @var    string
	 */
	private $name;

	/**
	 * The database table name suffix.
	 *
	 * @since  2.9.0
	 * @access protected
	 *
	 * @var    string
	 */
	protected $nameSuffix;

	/**
	 * The version of our database table
	 *
	 * @since  2.9.0
	 * @access protected
	 *
	 * @var    string
	 */
	protected $version = '1.0';

	/**
	 * The name of the primary column
	 *
	 * @since  2.9.0
	 * @access protected
	 *
	 * @var    string
	 */
	protected $primaryKey;

	/**
	 * Database table columns.
	 *
	 * @since 2.9.0
	 *
	 * @var string[]
	 */
	protected $columns = [];

	/**
	 * Cache group name
	 *
	 * @since  2.9.0
	 * @access private
	 *
	 * @var string
	 */
	private $cacheGroupName;

	/**
	 * Cache incrementer name
	 * This properties helps to flush cache.
	 *
	 * @since  2.9.0
	 * @access private
	 *
	 * @var string
	 */
	private $cacheGroupIncrementerName;

	/**
	 * Database Accessor.
	 *
	 * @since 2.9.0
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Table constructor.
	 */
	public function __construct() {
		global $wpdb;

		$this->db   = $wpdb;
		$this->name = "{$this->db->prefix}{$this->nameSuffix}";

		$this->setCacheKeys();
	}

	/**
	 * Whitelist of columns
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @return array  Columns and formats.
	 */
	public function getColumns() {
		return [];
	}

	/**
	 * Default column values
	 *
	 * @since  2.9.0
	 * @access public
	 *
	 * @return array  Default column values.
	 */
	public function getColumnDefaults() {
		return [];
	}

	/**
	 * Get table version.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	public function getVersion() {
		return get_option( $this->name . '_db_version', '' );
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * Set table version.
	 *
	 * @since 2.9.0
	 *
	 * @return string
	 */
	protected function setVersion() {
		return update_option( $this->getName() . '_db_version', $this->version, false );
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
	public function tableExists( $table ) {
		return $this->db->get_var( $this->db->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $table;
	}

	/**
	 * Checks whether column exists in a table or not.
	 *
	 * @param string $column_name Name of the Column in Database Table.
	 *
	 * @since 2.9.0
	 *
	 * @return bool
	 */
	public function doesColumnExist( $column_name ) {

		$column = $this->db->get_results(
			$this->db->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
						WHERE TABLE_SCHEMA = %s
						AND TABLE_NAME = %s
						AND COLUMN_NAME = %s ',
				DB_NAME,
				$this->name,
				$column_name
			)
		);

		return ! empty( $column );
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
		return $this->tableExists( $this->name );
	}

	/**
	 * Register tables
	 *
	 * @since  2.9.0
	 * @access public
	 */
	public function register_table() {
		$current_version = $this->getVersion();

		if ( ! $current_version || version_compare( $current_version, $this->version, '<' ) ) {
			$this->createTable();
		}
	}

	/**
	 * Create table
	 *
	 * @since  2.9.0
	 * @access protected
	 */
	abstract protected function createTable();

	/**
	 * Setup cache group.
	 *
	 * @since 2.9.0
	 * @access protected
	 */
	protected function setCacheKeys() {
		$current_blog_id = get_current_blog_id();

		$this->cacheGroupIncrementerName = "give-cache-incrementer-{$this->name}-{$current_blog_id}";
		$incrementerValue                = wp_cache_get( $this->cacheGroupIncrementerName ) ?: microtime( true );

		$this->cacheGroupName = "{$this->cacheGroupName}_{$current_blog_id}_{$incrementerValue}";
	}

	/**
	 * Add new column to database table.
	 *
	 * @since 2.9.0
	 * @access public
	 *
	 * @param string $name Table column name
	 * @param string $placeholder Column placeholder
	 */
	public function addColumn( $name, $placeholder ) {
		$this->columns[ $name ] = $placeholder;
	}
}
