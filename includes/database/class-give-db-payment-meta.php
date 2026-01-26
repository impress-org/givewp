<?php
/**
 * Payment Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/DB Payment Meta
 * @copyright   Copyright (c) 2016, GiveWP
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
	protected $meta_type = 'donation';

	/**
	 * Give_DB_Payment_Meta constructor.
	 *
	 * @access  public
	 *
	 * @since 4.14.0 Remove {$wpdb->paymentmeta} registration in favor of {$wpdb->donationmeta}
	 * @since   2.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Set donationmeta table name (preferred name to avoid conflicts with other plugins).
		// Note: We no longer set $wpdb->paymentmeta to prevent conflicts with plugins that also
		// use paymentmeta. All internal code has been updated to use $wpdb->donationmeta instead.
		$wpdb->donationmeta = $this->table_name = $wpdb->prefix . 'give_donationmeta';
		$this->version     = '1.0';

		// Backward compatibility for sites that haven't completed the v2.2.0 upgrade.
		if ( ! give_has_upgrade_completed( 'v220_rename_donation_meta_type' ) ) {
			$this->meta_type = 'payment';
			$this->table_name = $wpdb->prefix . 'give_paymentmeta';
			$wpdb->donationmeta = $this->table_name;
		}

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
			'meta_id'               => '%d',
			"{$this->meta_type}_id" => '%d',
			'meta_key'              => '%s',
			'meta_value'            => '%s',
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
