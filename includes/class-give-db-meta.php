<?php
/**
 * Give DB Meta
 *
 * @package     Give
 * @subpackage  Classes/Give_DB_Meta
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_DB_Meta extends Give_DB {
	/**
	 * Post type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $post_type = '';

	/**
	 * Meta type
	 *
	 * @since  2.0
	 * @access protected
	 * @var bool
	 */
	protected $meta_type = '';

	/**
	 * Flag to handle result type
	 *
	 * @since  2.0
	 * @access protected
	 */
	protected $raw_result = false;

	/**
	 * Flag for short circuit of meta function
	 *
	 * @since  2.0
	 * @access protected
	 */
	protected $check = false;


	/**
	 * Meta supports.
	 *
	 * @since  2.0
	 * @access protected
	 * @var array
	 */
	protected $supports = array(
		'add_post_metadata',
		'get_post_metadata',
		'update_post_metadata',
		'delete_post_metadata',
		'posts_where',
		'posts_join',
	);

	/**
	 * Give_DB_Meta constructor.
	 *
	 * @since 2.0
	 */
	function __construct() {
		// Bailout.
		if ( empty( $this->supports ) || ! $this->is_custom_meta_table_active() ) {
			return;
		}

		if ( in_array( 'add_post_metadata', $this->supports ) ) {
			add_filter( 'add_post_metadata', array( $this, '__add_meta' ), 0, 5 );
		}

		if ( in_array( 'get_post_metadata', $this->supports ) ) {
			add_filter( 'get_post_metadata', array( $this, '__get_meta' ), 0, 4 );
		}

		if ( in_array( 'update_post_metadata', $this->supports ) ) {
			add_filter( 'update_post_metadata', array( $this, '__update_meta' ), 0, 5 );
		}

		if ( in_array( 'delete_post_metadata', $this->supports ) ) {
			add_filter( 'delete_post_metadata', array( $this, '__delete_meta' ), 0, 5 );
		}

		if ( in_array( 'posts_where', $this->supports ) ) {
			add_filter( 'posts_where', array( $this, '__posts_where' ), 10, 2 );
		}

		if ( in_array( 'posts_join', $this->supports ) ) {
			add_filter( 'posts_join', array( $this, '__posts_join' ), 10, 2 );
		}
	}


	/**
	 * Retrieve payment meta field for a payment.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @param   int    $id       Pst Type  ID.
	 * @param   string $meta_key The meta key to retrieve.
	 * @param   bool   $single   Whether to return a single value.
	 *
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 */
	public function get_meta( $id = 0, $meta_key = '', $single = false ) {
		$id = $this->sanitize_id( $id );

		// Bailout.
		if ( ! $this->is_valid_post_type( $id ) ) {
			return $this->check;
		}

		if ( $this->raw_result ) {
			if ( ! ( $value = get_metadata( $this->meta_type, $id, $meta_key, false ) ) ) {
				$value = '';
			}

			// Reset flag.
			$this->raw_result = false;

		} else {
			$value = get_metadata( $this->meta_type, $id, $meta_key, $single );
		}

		return $value;
	}


	/**
	 * Add meta data field to a payment.
	 *
	 * For internal use only. Use Give_Payment->add_meta() for public usage.
	 *
	 * @access  private
	 * @since   2.0
	 *
	 * @param   int    $id         Post Type ID.
	 * @param   string $meta_key   Metadata name.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   bool   $unique     Optional, default is false. Whether the same key should not be added.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function add_meta( $id = 0, $meta_key = '', $meta_value, $unique = false ) {
		$id = $this->sanitize_id( $id );

		// Bailout.
		if ( ! $this->is_valid_post_type( $id ) ) {
			return $this->check;
		}

		return add_metadata( $this->meta_type, $id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update payment meta field based on Post Type ID.
	 *
	 * For internal use only. Use Give_Payment->update_meta() for public usage.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and Post Type ID.
	 *
	 * If the meta field for the payment does not exist, it will be added.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @param   int    $id         Post Type ID.
	 * @param   string $meta_key   Metadata key.
	 * @param   mixed  $meta_value Metadata value.
	 * @param   mixed  $prev_value Optional. Previous value to check before removing.
	 *
	 * @return  bool                  False on failure, true if success.
	 */
	public function update_meta( $id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		$id = $this->sanitize_id( $id );

		// Bailout.
		if ( ! $this->is_valid_post_type( $id ) ) {
			return $this->check;
		}

		return update_metadata( $this->meta_type, $id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a payment.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @access  public
	 * @since   2.0
	 *
	 * @param   int    $id         Post Type ID.
	 * @param   string $meta_key   Metadata name.
	 * @param   mixed  $meta_value Optional. Metadata value.
	 * @param   mixed  $delete_all Optional.
	 *
	 * @return  bool                  False for failure. True for success.
	 */
	public function delete_meta( $id = 0, $meta_key = '', $meta_value = '', $delete_all = '' ) {
		$id = $this->sanitize_id( $id );

		// Bailout.
		if ( ! $this->is_valid_post_type( $id ) ) {
			return $this->check;
		}

		return delete_metadata( $this->meta_type, $id, $meta_key, $meta_value, $delete_all );
	}

	/**
	 * Filter where clause of every query for new payment meta table
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string   $where
	 * @param WP_Query $wp_query
	 *
	 * @return string
	 */
	public function __posts_where( $where, $wp_query ) {
		global $wpdb;

		$is_payment_post_type = false;

		// Check if it is payment query.
		if ( ! empty( $wp_query->query['post_type'] ) ) {
			if ( is_string( $wp_query->query['post_type'] ) && $this->post_type === $wp_query->query['post_type'] ) {
				$is_payment_post_type = true;
			} elseif ( is_array( $wp_query->query['post_type'] ) && in_array( $this->post_type, $wp_query->query['post_type'] ) ) {
				$is_payment_post_type = true;
			}
		}

		// Add new table to sql query.
		if ( $is_payment_post_type && ! empty( $wp_query->meta_query->queries ) ) {
			$where = str_replace( $wpdb->postmeta, $this->table_name, $where );
		}

		return $where;
	}


	/**
	 * Filter join clause of every query for new payment meta table
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param string   $join
	 * @param WP_Query $wp_query
	 *
	 * @return string
	 */
	public function __posts_join( $join, $wp_query ) {
		global $wpdb;

		$is_payment_post_type = false;

		// Check if it is payment query.
		if ( ! empty( $wp_query->query['post_type'] ) ) {
			if ( is_string( $wp_query->query['post_type'] ) && $this->post_type === $wp_query->query['post_type'] ) {
				$is_payment_post_type = true;
			} elseif ( is_array( $wp_query->query['post_type'] ) && in_array( $this->post_type, $wp_query->query['post_type'] ) ) {
				$is_payment_post_type = true;
			}
		}

		// Add new table to sql query.
		if ( $is_payment_post_type && ! empty( $wp_query->meta_query->queries ) ) {
			$join = str_replace( "{$wpdb->postmeta}.post_id", "{$this->table_name}.payment_id", $join );
			$join = str_replace( $wpdb->postmeta, $this->table_name, $join );
		}

		return $join;
	}

	/**
	 * Add support for hidden functions.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case '__add_meta':
				$this->check = $arguments[0];
				$id          = $arguments[1];
				$meta_key    = $arguments[2];
				$meta_value  = $arguments[3];
				$unique      = $arguments[4];

				return $this->add_meta( $id, $meta_key, $meta_value, $unique );

			case '__get_meta':
				$this->check = $arguments[0];
				$id          = $arguments[1];
				$meta_key    = $arguments[2];
				$single      = $arguments[3];

				$this->raw_result = true;

				return $this->get_meta( $id, $meta_key, $single );

			case '__update_meta':
				$this->check = $arguments[0];
				$id          = $arguments[1];
				$meta_key    = $arguments[2];
				$meta_value  = $arguments[3];

				return $this->update_meta( $id, $meta_key, $meta_value );

			case '__delete_meta':
				$this->check = $arguments[0];
				$id          = $arguments[1];
				$meta_key    = $arguments[2];
				$meta_value  = $arguments[3];
				$delete_all  = $arguments[3];

				return $this->delete_meta( $id, $meta_key, $meta_value, $delete_all );
		}
	}

	/**
	 * Check if current id of post type or not
	 *
	 * @since  2.0
	 * @access protected
	 *
	 * @param $ID
	 *
	 * @return bool
	 */
	protected function is_valid_post_type( $ID ) {
		return $ID && ( $this->post_type === get_post_type( $ID ) );
	}

	/**
	 * check if custom meta table enabled or not.
	 *
	 * @since  2.0
	 * @access protected
	 * @return bool
	 */
	protected function is_custom_meta_table_active() {
		return false;
	}
}