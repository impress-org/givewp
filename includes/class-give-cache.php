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

class Give_Cache{
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
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.8.7
	 * @access public
	 */
	public function setup_hooks() {
		// Delete give cache weekly.
		// add_action( 'give_weekly_scheduled_events', array( $this, 'delete_cache' ) );
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

	public static function get_cache_key( $action, $query_args ) {
		// Bailout.
		if ( ! is_array( $query_args ) || empty( $query_args ) ) {
			return '';
		}

		return "give_cache_{$action}_" . substr( md5( serialize( $query_args ) ), 0, 15 );
	}

	/**
	 * Delete all logging cache.
	 *
	 * @since  1.8.7
	 * @access public
	 *
	 * @return bool
	 */
	public function delete_cache() {
		global $wpdb;
		$cache_option_names = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} where option_name LIKE '%%%s%%'",
				'give_cache'
			),
			ARRAY_A
		);

		error_log( print_r( $cache_option_names, true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );

		// Bailout.
		if ( empty( $cache_option_names ) ) {
			return false;
		}

		// Delete log cache.
		foreach ( $cache_option_names as $option_name ) {
			delete_option( $option_name['option_name'] );
		}
	}
}

// Initialize
Give_Cache::get_instance()->setup_hooks();
