<?php
/**
 * Comments Meta DB class
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Comment_Meta
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Give_DB_Comment_Meta
 *
 * This class is for interacting with the comment meta database table.
 *
 * @since 2.3.0
 */
class Give_DB_Comment_Meta extends Give_DB_Meta {
	/**
	 * Meta supports.
	 *
	 * @since  2.3.0
	 * @access protected
	 * @var array
	 */
	protected $supports = array();

	/**
	 * Meta type
	 *
	 * @since  2.3.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = 'give_comment';

	/**
	 * Give_DB_Comment_Meta constructor.
	 *
	 * @access  public
	 * @since   2.3.0
	 */
	public function __construct() {
		/* @var WPDB $wpdb */
		global $wpdb;

		$wpdb->give_commentmeta = $this->table_name = $wpdb->prefix . 'give_commentmeta';
		$this->primary_key      = 'meta_id';
		$this->version          = '1.0';

		parent::__construct();
	}

	/**
	 * Get table columns and data types.
	 *
	 * @access  public
	 * @since   2.3.0
	 *
	 * @return  array  Columns and formats.
	 */
	public function get_columns() {
		return array(
			'meta_id'         => '%d',
			'give_comment_id' => '%d',
			'meta_key'        => '%s',
			'meta_value'      => '%s',
		);
	}

	/**
	 * Delete all comment meta
	 *
	 * @since  2.3.0
	 * @access public
	 *
	 * @param int $comment_id
	 *
	 * @return bool
	 */
	public function delete_row( $comment_id = 0 ) {
		/* @var WPDB $wpdb */
		global $wpdb;

		// Row ID must be positive integer
		$comment_id = absint( $comment_id );

		if ( empty( $comment_id ) ) {
			return false;
		}

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$this->table_name} WHERE give_comment_id = %d", $comment_id ) ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current id is valid
	 *
	 * @since  2.3.0
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
