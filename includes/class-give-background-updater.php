<?php
/**
 * Background Updater
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing to handle DB
 * updates in the background.
 *
 * @class    Give_Background_Updater
 * @version  2.0.0
 * @package  Give/Classes
 * @category Class
 * @author   WordImpress
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Async_Request' ) ) {
	include_once( GIVE_PLUGIN_DIR . 'includes/libraries/wp-async-request.php' );
}

if ( ! class_exists( 'WP_Background_Process' ) ) {
	include_once( GIVE_PLUGIN_DIR . 'includes/libraries/wp-background-process.php' );
}

/**
 * Give_Background_Updater Class.
 */
class Give_Background_Updater extends WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'give_db_updater';

	/**
	 * Dispatch updater.
	 *
	 * Updater will still run via cron job if this fails for any reason.
	 */
	public function dispatch() {
		/* @var WP_Background_Process $dispatched */
		parent::dispatch();
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();

			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param array $update Update info
	 *
	 * @return mixed
	 */
	protected function task( $update ) {
		if ( empty( $update ) ) {
			return false;
		}

		/* @var  Give_Updates $give_updates */
		$give_updates  = Give_Updates::get_instance();
		$resume_update = get_option(
			'give_doing_upgrade',

			// Default update.
			array(
				'update_info' => $update,
				'step'        => 1,
				'update'      => 1,
				'heading'     => sprintf( 'Update %s of {update_count}', 1 ),
				'percentage'  => $give_updates->percentage,
			)
		);

		// Continuously skip update if previous update does not complete yet.
		if(
			$resume_update['update_info']['id'] !== $update['id'] &&
			! give_has_upgrade_completed( $resume_update['update_info']['id'] )
		) {
			return $update;
		}

		// Set params.
		$resume_update['update_info'] = $update;
		$give_updates->step           = absint( $resume_update['step'] );
		$give_updates->update         = absint( $resume_update['update'] );
		$is_parent_update_completed   = $give_updates->is_parent_updates_completed( $update );

		// Skip update if dependency update does not complete yet.
		if ( empty( $is_parent_update_completed ) ) {
			// @todo: set error when you have only one update with invalid dependency
			if ( ! is_null( $is_parent_update_completed ) ) {
				return $update;
			}

			return false;
		}

		// Disable cache.
		Give_Cache::disable();

		// Run update.
		if ( is_array( $update['callback'] ) ) {
			$update['callback'][0]->$update['callback'][1]();
		} else {
			$update['callback']();
		}

		// Set update info.
		$doing_upgrade_args = array(
			'update_info' => $update,
			'step'        => ++ $give_updates->step,
			'update'      => $give_updates->update,
			'heading'     => sprintf( 'Update %s of {update_count}', $give_updates->update ),
			'percentage'  => $give_updates->percentage,
		);

		// Cache upgrade.
		update_option( 'give_doing_upgrade', $doing_upgrade_args );

		// Enable cache.
		Give_Cache::enable();

		// Check if current update completed or not.
		if ( give_has_upgrade_completed( $update['id'] ) ) {
			return false;
		}

		return $update;
	}

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		parent::complete();

		delete_option( 'give_doing_upgrade' );
	}
}
