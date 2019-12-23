<?php
/**
 * Cron
 *
 * @package     Give
 * @subpackage  Classes/Give_Cron
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.3.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_Cron Class
 *
 * This class handles scheduled events.
 *
 * @since 1.3.2
 */
class Give_Cron {

	/**
	 * Instance.
	 *
	 * @since  1.8.13
	 * @access private
	 * @var
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.13
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @return static
	 * @since  1.8.13
	 * @access public
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
	 * @since 1.8.13
	 */
	private function setup() {
		add_filter( 'cron_schedules', array( self::$instance, '__add_schedules' ) );
		add_action( 'wp', array( self::$instance, '__schedule_events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @param array $schedules An array of non-default cron schedules.
	 *
	 * @return array            An array of non-default cron schedules.
	 * @since  1.3.2
	 * @access public
	 */
	public function __add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800, // 7 * 24 * 3600
			'display'  => __( 'Once Weekly', 'give' ),
		);

		// Adds once weekly to the existing schedules.
		$schedules['monthly'] = array(
			'interval' => 2592000, // 30 * 24 * 3600
			'display'  => __( 'Once Monthly', 'give' ),
		);

		// Adds every third day to the existing schedules.
		$schedules['thricely'] = array(
			'interval' => 259200, // 3 * 24 * 3600
			'display'  => __( 'Every Third Day', 'give' ),
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access public
	 */
	public function __schedule_events() {
		$this->monthly_events();
		$this->weekly_events();
		$this->daily_events();
		$this->thricely_events();
	}

	/**
	 * Schedule monthly events
	 *
	 * @return void
	 * @since  2.5.0
	 * @access private
	 */
	private function monthly_events() {
		if ( ! wp_next_scheduled( 'give_monthly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'monthly', 'give_monthly_scheduled_events' );
		}
	}

	/**
	 * Schedule weekly events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access private
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'give_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'give_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @return void
	 * @since  1.3.2
	 * @access private
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'give_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'give_daily_scheduled_events' );
		}
	}

	/**
	 * Schedule thricely events
	 *
	 * @return void
	 * @since  2.5.11
	 * @access private
	 */
	private function thricely_events() {
		if ( ! wp_next_scheduled( 'give_thricely_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'thricely', 'give_thricely_scheduled_events' );
		}
	}

	/**
	 * get cron job action name
	 *
	 * @param string $type
	 *
	 * @return string
	 * @since  1.8.13
	 * @access public
	 */
	public static function get_cron_action( $type = 'weekly' ) {
		$cron_action = '';

		switch ( $type ) {
			case 'daily':
				$cron_action = 'give_daily_scheduled_events';
				break;

			case 'thricely':
				$cron_action = 'give_thricely_scheduled_events';
				break;

			case 'monthly':
				$cron_action = 'give_monthly_scheduled_events';
				break;

			case 'weekly':
				$cron_action = 'give_weekly_scheduled_events';
				break;
		}

		return $cron_action;
	}

	/**
	 * Add action to cron action
	 *
	 * @param string $callback
	 * @param string $type
	 *
	 * @since  1.8.13
	 * @access private
	 */
	private static function add_event( $callback, $type = 'weekly' ) {
		$cron_event = self::get_cron_action( $type );
		add_action( $cron_event, $callback );
	}

	/**
	 * Add weekly event
	 *
	 * @param string $callback
	 *
	 * @since  1.8.13
	 * @access public
	 */
	public static function add_weekly_event( $callback ) {
		self::add_event( $callback );
	}

	/**
	 * Add daily event
	 *
	 * @param $callback
	 *
	 * @since  1.8.13
	 * @access public
	 */
	public static function add_daily_event( $callback ) {
		self::add_event( $callback, 'daily' );
	}

	/**
	 * Add thricely event
	 *
	 * @param $callback
	 *
	 * @since  2.5.11
	 * @access public
	 */
	public static function add_thricely_event( $callback ) {
		self::add_event( $callback, 'thricely' );
	}

	/**
	 * Add monthly event
	 *
	 * @param $callback
	 *
	 * @since  2.5.0
	 * @access public
	 */
	public static function add_monthly_event( $callback ) {
		self::add_event( $callback, 'monthly' );
	}
}

// Initiate class.
Give_Cron::get_instance();
