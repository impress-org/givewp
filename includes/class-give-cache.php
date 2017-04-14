<?php
/**
 * Class for managing cache
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache
 * @copyright   Copyright (c) 2017, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.8.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache {
	/**
	 * Instance.
	 *
	 * @since  1.8.7
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.7
	 * @access private
	 * Give_Cache constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.8.7
	 * @access public
	 * @return static
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since  1.8.7
	 * @access public
	 */
	public function setup_hooks() {
		// weekly delete all expired cache.
		add_action( 'give_weekly_scheduled_events', array( $this, 'delete_all_expired' ) );
	}

	/**
	 * Get cache key.
	 *
	 * @since  1.8.7
	 *
	 * @param  string $action     Cache key prefix.
	 * @param  array  $query_args Query array.
	 *
	 * @return string
	 */

	public static function get_key( $action, $query_args ) {
		$cache_key = "give_cache_{$action}";

		// Bailout.
		if ( ! empty( $query_args ) ) {
			$cache_key = "{$cache_key}_" . substr( md5( serialize( $query_args ) ), 0, 15 );
		}

		return $cache_key;
	}

	/**
	 * Get cache.
	 *
	 * @since  1.8.7
	 *
	 * @param  string $cache_key .
	 *
	 * @return mixed
	 */

	public static function get( $cache_key ) {
		if ( ! self::is_valid_cache_key( $cache_key ) ) {
			return new WP_Error( 'give_invalid_cache_key', __( 'Cache key format should be give_cache_*', 'give' ) );
		}

		$option = get_option( $cache_key );

		// Backward compatibility.
		if ( ! is_array( $option ) || empty( $option ) || ! array_key_exists( 'expiration', $option ) ) {
			return $option;
		}

		// Get current time.
		$current_time = current_time( 'timestamp', 1 );

		if ( empty( $option['expiration'] ) || ( $current_time < $option['expiration'] ) ) {
			$option = $option['data'];
		} else {
			$option = false;
		}
		
		return $option;
	}

	/**
	 * Set cache.
	 *
	 * @since  1.8.7
	 *
	 * @param  string   $cache_key
	 * @param  mixed    $data
	 * @param  int|null $expiration Timestamp should be in GMT format.
	 *
	 * @return mixed
	 */

	public static function set( $cache_key, $data, $expiration = null ) {
		if ( ! self::is_valid_cache_key( $cache_key ) ) {
			return new WP_Error( 'give_invalid_cache_key', __( 'Cache key format should be give_cache_*', 'give' ) );
		}

		$option_value = array(
			'data'       => $data,
			'expiration' => ! is_null( $expiration )
				? ( $expiration + current_time( 'timestamp', 1 ) )
				: null,
		);

		$result = add_option( $cache_key, $option_value, '', 'no' );

		return $result;
	}

	/**
	 * Delete cache.
	 *
	 * @since  1.8.7
	 *
	 * @param  string|array $cache_keys
	 */

	public static function delete( $cache_keys ) {
		if( ! empty( $cache_keys ) ) {
			$cache_keys = is_array( $cache_keys ) ? $cache_keys : array( $cache_keys );

			foreach ( $cache_keys as $cache_key ) {
				if( self::is_valid_cache_key( $cache_key ) ){
					delete_option( $cache_key );
				}
			}
		}
	}

	/**
	 * Delete all logging cache.
	 *
	 * @since  1.8.7
	 * @access public
	 * @global wpdb $wpdb
	 *
	 * @return bool
	 */
	public static function delete_all_expired() {
		global $wpdb;
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value
						FROM {$wpdb->options}
						Where option_name
						LIKE '%%%s%%'",
				'give_cache'
			),
			ARRAY_A
		);

		// Bailout.
		if ( empty( $options ) ) {
			return false;
		}

		$current_time = current_time( 'timestamp', 1 );

		// Delete log cache.
		foreach ( $options as $option ) {
			$option['option_value'] = maybe_unserialize( $option['option_value'] );

			if (
				! is_array( $option['option_value'] )
				|| ! array_key_exists( 'expiration', $option['option_value'] )
				|| empty( $option['option_value']['expiration'] )
				|| ( $current_time < $option['option_value']['expiration'] )
			) {
				continue;
			}

			self::delete( $option['option_name'] );
		}
	}


	/**
	 * Check cache key validity.
	 *
	 * @since  1.8.7
	 * @access public
	 *
	 * @param $cache_key
	 *
	 * @return bool|int
	 */
	public static function is_valid_cache_key( $cache_key ) {
		return ( false !== strpos( $cache_key, 'give_cache_' ) );
	}
}

// Initialize
Give_Cache::get_instance()->setup_hooks();
