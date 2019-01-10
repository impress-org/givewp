<?php
/**
 * Donor Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Donor Meta
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.6
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Donor_Meta
 *
 * This class is for interacting with the donor meta database table.
 *
 * @since 1.6
 */
class Give_DB_Donor_Meta extends Give_DB_Meta {

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
	 * @access public
	 *
	 * @var string
	 */
	public $meta_type = 'donor';


	/**
	 * Give_DB_Donor_Meta constructor.
	 *
	 * @access  public
	 * @since   1.6
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->donormeta   = $this->table_name = $wpdb->prefix . 'give_donormeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		parent::__construct();

		$this->bc_200_params();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   1.6
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'     => '%d',
			'donor_id' => '%d',
			'meta_key'    => '%s',
			'meta_value'  => '%s',
		);
	}

	/**
	 * Add backward compatibility for old table name
	 *
	 * @since  2.0
	 * @access private
	 * @global wpdb $wpdb
	 */
	private function bc_200_params() {
		/* @var wpdb $wpdb */
		global $wpdb;

		if (
			! give_has_upgrade_completed( 'v20_rename_donor_tables' ) &&
			$wpdb->query( $wpdb->prepare( "SHOW TABLES LIKE %s","{$wpdb->prefix}give_customermeta" ) )
		) {
			$wpdb->donormeta = $this->table_name = "{$wpdb->prefix}give_customermeta";
			$this->meta_type = 'customer';
		}

		$wpdb->customermeta = $wpdb->donormeta;
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
