<?php
/**
 * Subscription Meta DB class
 *
 * @package     Give
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_Recurring_DB_Subscription_Meta
 *
 * This class is for interacting with the subscription meta database table.
 * @since 2.19.0 - migrated from give-recurring
 * @since 1.8
 */
class Give_Recurring_DB_Subscription_Meta extends Give_DB_Meta {

	/**
	 * Meta supports.
	 *
	 * @since  1.8
	 * @access protected
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Meta type
	 *
	 * @since  1.8
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'subscription';

	/**
	 * Give_Recurring_DB_Subscription_Meta constructor.
	 *
	 * @access  public
	 * @since   1.8
	 */
	public function __construct() {
		global $wpdb;

		$wpdb->subscriptionmeta = $this->table_name = $wpdb->prefix . 'give_subscriptionmeta';
		$this->primary_key      = 'meta_id';
		$this->version          = '1.0';

		parent::__construct();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   1.8
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'         => '%d',
			'subscription_id' => '%d',
			'meta_key'        => '%s',
			'meta_value'      => '%s',
		);
	}
}
