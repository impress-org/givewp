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
	 * Get all batches.
	 *
	 * @since  2.0
	 * @access public
	 * @return stdClass
	 */
	public function get_all_batch() {
		return parent::get_batch();
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
		if (
			$resume_update['update_info']['id'] !== $update['id'] &&
			! give_has_upgrade_completed( $resume_update['update_info']['id'] )
		) {
			$batch = Give_Updates::$background_updater->get_all_batch();
			$batch_data_count = count( $batch->data );

			if ( ! empty( $batch ) &&  1 === $batch_data_count ) {
				if ( ! empty( $update['depend'] ) ) {

					$give_updates   = Give_Updates::get_instance();
					$all_updates    = $give_updates->get_updates( 'database', 'all' );
					$all_update_ids = wp_list_pluck( $all_updates, 'id' );

					foreach ( $update['depend'] as $depend ) {
						if ( give_has_upgrade_completed( $depend ) ) {
							continue;
						}

						if ( in_array( $depend, $all_update_ids ) ) {
							array_unshift( $batch->data, $all_updates[ array_search( $depend, $all_update_ids ) ] );
						}
					}

					if( $batch_data_count !== count( $batch->data ) ) {
						update_option( $batch->key, $batch->data );
						$this->dispatch();

						wp_die();
					}
				}
			}


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
			'update_info'      => $update,
			'step'             => ++ $give_updates->step,
			'update'           => $give_updates->update,
			'heading'          => sprintf( 'Update %s of %s', $give_updates->update, get_option( 'give_db_update_count' ) ),
			'percentage'       => $give_updates->percentage,
			'total_percentage' => $give_updates->get_db_update_processing_percentage(),
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

		delete_option( 'give_db_update_count' );
		delete_option( 'give_doing_upgrade' );
		add_option( 'give_show_db_upgrade_complete_notice', 1, '', 'no' );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default.
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || '-1' === $memory_limit ) {
			// Unlimited, set to 32GB.
			$memory_limit = '32000M';
		}

		return intval( $memory_limit ) * 1024 * 1024;
	}
}
