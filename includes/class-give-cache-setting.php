<?php
/**
 * Class for managing plugin setting cache
 * Note: only use for internal purpose.
 *
 * @package     Give
 * @subpackage  Classes/Give_Cache_Setting
 * @copyright   Copyright (c) 2018, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Cache_Setting {
	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var Give_Cache_Setting
	 */
	static private $instance;

	/**
	 * Instance.
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $settings;

	/**
	 * List of core options
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $options = array(
		'give_settings',
		'give_version',
		'give_completed_upgrades',
	);

	/**
	 * Singleton pattern.
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.4.0
	 * @access public
	 * @return Give_Cache_Setting
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Setup
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function setup() {
		$this->load_plugin_settings();

		add_action( 'added_option', array( $this, '__reload_plugin_settings' )  );
		add_action( 'updated_option', array( $this, '__reload_plugin_settings' )  );
		add_action( 'deleted_option', array( $this, '__reload_plugin_settings' )  );
	}

	/**
	 * Load plugin settings
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function load_plugin_settings() {
		global $wpdb;

		$cache = wp_cache_get( 'giveAllOptions','options' );

		// Load options from cache.
		if( false !== $cache ) {
			self::$settings = $cache;
			return;
		}

		$options = '\'' . implode( '\',\'', self::$options ) . '\'';

		$tmp     = array();
		$sql     = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name IN ({$options}) ";
		$results = $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {

			/* @var  stdClass $result */
			foreach ( $results as $result ) {
				$tmp[ $result->option_name ] = maybe_unserialize( $result->option_value );
			}

			self::$settings = $tmp;
			wp_cache_set( 'giveAllOptions', $tmp, 'options' );
		}
	}

	/**
	 * Reload option when add, update or delete
	 * Note: only for internal logic
	 *
	 * @since 2.4.0
	 *
	 * @param $option_name
	 */
	public function __reload_plugin_settings( $option_name ) {
		// Bailout.
		if ( ! in_array( $option_name, self::$options ) ) {
			return;
		}

		wp_cache_delete( 'giveAllOptions', 'options' );
		$this->$this->load_plugin_settings();
	}


	/**
	 * Get option
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @param      $option_name
	 * @param bool $default
	 *
	 * @return mixed
	 */
	public static function get_option( $option_name, $default = false ) {
		if ( in_array( $option_name, self::$options ) ) {
			$value = ! empty( self::$settings[ $option_name ] )
				? self::$settings[ $option_name ]
				: $default;

		} else {

			$value = self::get_option( $option_name, $default );
		}

		return $value;
	}

	/**
	 * Get option
	 *
	 * @since  2.4.0
	 * @access public
	 *
	 * @param      $setting_name
	 * @param bool $default
	 *
	 * @return mixed
	 */
	public static function get_give_option( $setting_name, $default = false ) {
		$give_settings = self::$settings['give_settings'];

		$value = ! empty( $give_settings[ $setting_name ] )
			? self::$settings[ $setting_name ]
			: $default;

		/**
		 * Filter the option
		 *
		 * @since 2.4.0
		 */
		$value = apply_filters( 'give_get_option', $value, $setting_name, $default );


		/**
		 * filter the specific option
		 *
		 * @since 2.4.0
		 */
		$value = apply_filters( "give_get_option_{$setting_name}", $value, $setting_name, $default );


		return $value;
	}

	/**
	 * Get plugin settings
	 *
	 * @since  2.4.0
	 * @access public
	 */
	public static function get_settings() {

		/**
		 * Filter the plugin setting
		 */
		return (array) apply_filters( 'give_get_settings', self::$settings['give_settings'] );
	}
}

Give_Cache_Setting::get_instance();
