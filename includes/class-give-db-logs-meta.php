<?php
/**
 * Logs Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Log Meta
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Log_Meta
 *
 * This class is for interacting with the log meta database table.
 *
 * @since 2.0
 */
class Give_DB_Log_Meta extends Give_DB_Meta {
	/**
	 * Meta supports.
	 *
	 * @since  2.0
	 * @access protected
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Meta type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'log';

	/**
	 * Give_DB_Log_Meta constructor.
	 *
	 * @access  public
	 * @since   2.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->logmeta     = $this->table_name = $wpdb->prefix . 'give_logmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		parent::__construct();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'    => '%d',
			'log_id'     => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	}

	/**
	 * Delete all log meta
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param int $log_id
	 *
	 * @return bool
	 */
	public function delete_row( $log_id = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Row ID must be positive integer
		$log_id = absint( $log_id );

		if ( empty( $log_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE log_id = %d", $log_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current id is valid
	 *
	 * @since  2.0
	 * @access protected
	 *
	 * @param $ID
	 *
	 * @return bool
	 */
	protected function is_valid_post_type( $ID ) {
		return $ID && true;
	}
}
