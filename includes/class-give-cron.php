<?php
/**
 * Cron
 *
 * @package     Give
 * @subpackage  Classes/Cron
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 1.3.2
 */
class Give_Cron {
	/**
	 * Get things going
	 *
	 * @since 1.3.2
	 * @see   Give_Cron::weekly_events()
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'wp', array( $this, 'schedule_Events' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 1.3.2
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'easy-digital-downloads' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since  1.3.2
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
		$this->daily_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since  1.3.2
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
	 * @access private
	 * @since  1.3.2
	 * @return void
	 */
	private function daily_events() {
		if ( ! wp_next_scheduled( 'give_daily_scheduled_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'give_daily_scheduled_events' );
		}
	}

}

$give_cron = new Give_Cron;
