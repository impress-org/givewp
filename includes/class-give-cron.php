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
	 * @since  1.8.13
	 * @access public
	 * @return static
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
	 * @since  1.3.2
	 * @access public
	 *
	 * @param  array $schedules An array of non-default cron schedules.
	 *
	 * @return array            An array of non-default cron schedules.
	 */
	public function __add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'give' ),
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @since  1.3.2
	 * @access public
	 *
	 * @return void
	 */
	public function __schedule_events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @since  1.3.2
	 * @access private
	 *
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'give_weekly_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'give_weekly_scheduled_events' );
		}
	}

	/**
	 * Schedule daily events
	 *
	 * @since  1.3.2
	 * @access private
	 *
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'give_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'give_daily_scheduled_events' );
		}
	}

	/**
	 * get cron job action name
	 *
	 * @since  1.8.13
	 * @access public
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public static function get_cron_action( $type = 'weekly' ) {
		switch ( $type ) {
			case 'daily':
				$cron_action = 'give_daily_scheduled_events';
				break;

			default:
				$cron_action = 'give_weekly_scheduled_events';
				break;
		}

		return $cron_action;
	}

	/**
	 * Add action to cron action
	 *
	 * @since  1.8.13
	 * @access private
	 *
	 * @param        $action
	 * @param string $type
	 */
	private static function add_event( $action, $type = 'weekly' ) {
		$cron_event = self::get_cron_action( $type );
		add_action( $cron_event, $action );
	}

	/**
	 * Add weekly event
	 *
	 * @since  1.8.13
	 * @access public
	 *
	 * @param $action
	 */
	public static function add_weekly_event( $action ) {
		self::add_event( $action, 'weekly' );
	}

	/**
	 * Add daily event
	 *
	 * @since  1.8.13
	 * @access public
	 *
	 * @param $action
	 */
	public static function add_daily_event( $action ) {
		self::add_event( $action, 'daily' );
	}
}

// Initiate class.
Give_Cron::get_instance();
