<?php
/**
 * Payment Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Payment Meta
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Payment_Meta
 *
 * This class is for interacting with the payment meta database table.
 *
 * @since 2.0
 */
class Give_DB_Payment_Meta extends Give_DB_Meta {
	/**
	 * Post type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $post_type = 'give_payment';

	/**
	 * Meta type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'payment';

	/**
	 * Give_DB_Payment_Meta constructor.
	 *
	 * @access  public
	 * @since   2.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->paymentmeta = $this->table_name = $wpdb->prefix . 'give_paymentmeta';
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		$this->register_table();

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
			'payment_id' => '%d',
			'meta_key'   => '%s',
			'meta_value' => '%s',
		);
	}

	/**
	 * check if custom meta table enabled or not.
	 *
	 * @since  2.0
	 * @access protected
	 * @return bool
	 */
	protected function is_custom_meta_table_active() {
		return give_has_upgrade_completed( 'v20_move_metadata_into_new_table' );
	}
}
