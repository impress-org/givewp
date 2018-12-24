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
	 * @var string
	 */
	static private $cache_key = 'giveAllOptions';

	/**
	 * Array of cached settings
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $settings = array(
		'give_settings'           => array(),
		'give_version'            => '',
		'give_completed_upgrades' => array(),
		'currencies'              => array(),
	);

	/**
	 * Array of cached setting db option names
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $db_option_ids = array(
		'give_settings',
		'give_version',
		'give_completed_upgrades',
	);

	/**
	 * Array of cached setting option names
	 *
	 * @since  2.4.0
	 * @access private
	 * @var array
	 */
	static private $all_option_ids;

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
		self::$all_option_ids = array_keys( self::$settings );

		$this->load_plugin_settings();

		add_action( 'added_option', array( $this, '__reload_plugin_settings' ) );
		add_action( 'updated_option', array( $this, '__reload_plugin_settings' ) );
		add_action( 'deleted_option', array( $this, '__reload_plugin_settings' ) );

		add_action( 'give_init', array( $this, '__setup_currencies_list' ), 11 );
	}

	/**
	 * Load plugin settings
	 *
	 * @since  2.4.0
	 * @access private
	 */
	private function load_plugin_settings() {
		global $wpdb;

		$cache = wp_cache_get( self::$cache_key, 'options' );

		// Load options from cache.
		if ( false !== $cache ) {
			self::$settings = $cache;

			return;
		}

		$db_option_ids = '\'' . implode( '\',\'', self::$db_option_ids ) . '\'';

		$tmp     = array();
		$sql     = "SELECT option_name, option_value FROM $wpdb->options WHERE option_name IN ({$db_option_ids}) ";
		$results = $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {

			/* @var  stdClass $result */
			foreach ( $results as $result ) {
				self::$settings[ $result->option_name ] = maybe_unserialize( $result->option_value );
			}

			wp_cache_set( self::$cache_key, $tmp, 'options' );
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
		if ( ! in_array( $option_name, self::$db_option_ids ) ) {
			return;
		}

		wp_cache_delete( self::$cache_key, 'options' );
		$this->load_plugin_settings();
	}

	/**
	 * Setup currencies list
	 *
	 * @since 2.4.0
	 */
	public function __setup_currencies_list() {
		$currencies = require_once GIVE_PLUGIN_DIR . 'includes/currency/currencies-list.php';

		/**
		 * Filter the supported currency list
		 *
		 * @since 2.4.0
		 */
		$currencies = apply_filters( 'give_register_currency', $currencies );

		self::$settings['currencies'] = $currencies;
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
		$value = $default;

		if ( in_array( $option_name, self::$all_option_ids ) ) {
			$value = ! empty( self::$settings[ $option_name ] )
				? self::$settings[ $option_name ]
				: $default;
		}

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
