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
 * @author   GiveWP
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
		if ( give_test_ajax_works() ) {
			parent::dispatch();
		} elseif ( wp_doing_ajax() ) {
			$this->maybe_handle();
		}
	}


	/**
	 * Get all batches.
	 *
	 * @since  2.0
	 * @access public
	 * @return stdClass
	 */
	public function get_all_batch() {
		return $this->get_batch();
	}

	/**
	 * Is queue empty
	 *
	 * @since 2.0.3
	 *
	 * @return bool
	 */
	public function has_queue() {
		return ( ! $this->is_queue_empty() );
	}


	/**
	 * Lock process
	 *
	 * Lock the process so that multiple instances can't run simultaneously.
	 * Override if applicable, but the duration should be greater than that
	 * defined in the time_exceeded() method.
	 *
	 * @since 2.0.3
	 */
	protected function lock_process() {
		// Check if admin want to pause upgrade.
		if ( get_option( 'give_pause_upgrade' ) ) {
			self::flush_cache();

			delete_option( 'give_paused_batches' );

			Give_Updates::get_instance()->__pause_db_update( true );

			delete_option( 'give_pause_upgrade' );

			/**
			 * Fire action when pause db updates
			 *
			 * @since 2.0.1
			 */
			do_action( 'give_pause_db_upgrade', Give_Updates::get_instance() );

			wp_die();
		}

		$this->start_time = time(); // Set start time of current process.

		$lock_duration = ( property_exists( $this, 'queue_lock_time' ) ) ? $this->queue_lock_time : 60; // 1 minute
		$lock_duration = apply_filters( $this->identifier . '_queue_lock_time', $lock_duration );

		set_transient( $this->identifier . '_process_lock', microtime(), $lock_duration );
	}

	/**
	 * Handle cron healthcheck
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() || $this->is_paused_process() ) {
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
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) && ! $this->is_paused_process() ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is queue empty
	 *
	 * @since 2.4.5
	 *
	 * @return bool
	 */
	protected function is_queue_empty() {
		global $wpdb;

		$table  = $wpdb->options;
		$column = 'option_name';

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT COUNT(*)
			FROM {$table}
			WHERE {$column} LIKE %s
		",
				$key
			)
		);

		return ! ( $count > 0 );
	}

	/**
	 * Get batch
	 *
	 * @since 2.4.5
	 *
	 * @return stdClass Return the first batch from the queue
	 */
	protected function get_batch() {
		global $wpdb;

		$table        = $wpdb->options;
		$column       = 'option_name';
		$key_column   = 'option_id';
		$value_column = 'option_value';

		$key = $wpdb->esc_like( $this->identifier . '_batch_' ) . '%';

		$query = $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT *
			FROM {$table}
			WHERE {$column} LIKE %s
			ORDER BY {$key_column} ASC
			LIMIT 1
		",
				$key
			)
		);

		$batch       = new stdClass();
		$batch->key  = $query->$column;
		$batch->data = maybe_unserialize( $query->$value_column );

		return $batch;
	}

	/**
	 * Save queue
	 *
	 * @since 2.4.5
	 *
	 * @return $this
	 */
	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_option( $key, $this->data );
		}

		return $this;
	}

	/**
	 * Update queue
	 *
	 * @since 2.4.5
	 *
	 * @param string $key Key.
	 * @param array  $data Data.
	 *
	 * @return $this
	 */
	public function update( $key, $data ) {
		if ( ! empty( $data ) ) {
			update_option( $key, $data );
		}

		return $this;
	}

	/**
	 * Delete queue
	 *
	 * @since 2.4.5
	 *
	 * @param string $key Key.
	 *
	 * @return $this
	 */
	public function delete( $key ) {
		delete_option( $key );

		return $this;
	}

	/**
	 * Is process running
	 *
	 * @since 2.4.5
	 *
	 * Check whether the current process is already running
	 * in a background process.
	 */
	public function is_process_running() {
		if ( get_transient( $this->identifier . '_process_lock' ) ) {
			// Process already running.
			return true;
		}

		return false;
	}

	/**
	 * Unlock process
	 *
	 * Unlock the process so that other instances can spawn.
	 *
	 * @since 2.4.5
	 *
	 * @return $this
	 */
	protected function unlock_process() {
		delete_transient( $this->identifier . '_process_lock' );

		return $this;
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
		// Pause upgrade immediately if admin pausing upgrades.
		if ( $this->is_paused_process() ) {
			wp_die();
		}

		if ( empty( $update ) ) {
			return false;
		}

		// Delete cache.
		self::flush_cache();

		/* @var  Give_Updates $give_updates */
		$give_updates  = Give_Updates::get_instance();
		$resume_update = get_option(
			'give_doing_upgrade',
			// Default update.
			array(
				'update_info'      => $update,
				'step'             => 1,
				'update'           => 1,
				'heading'          => sprintf( 'Update %s of {update_count}', 1 ),
				'percentage'       => $give_updates->percentage,
				'total_percentage' => 0,
			)
		);

		// Continuously skip update if previous update does not complete yet.
		if (
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

		// Pause upgrade immediately if found following:
		// 1. Running update number greater then total update count
		// 2. Processing percentage greater then 100%
		if ( (
			101 < $resume_update['total_percentage'] ) ||
			( $give_updates->get_total_db_update_count() < $resume_update['update'] ) ||
			! in_array( $resume_update['update_info']['id'], $give_updates->get_update_ids() )
		) {
			if ( ! $this->is_paused_process() ) {
				$give_updates->__pause_db_update( true );
			}

			update_option( 'give_upgrade_error', 1, false );

			$log_data  = 'Update Task' . "\n";
			$log_data .= "Total update count: {$give_updates->get_total_db_update_count()}\n";
			$log_data .= 'Update IDs: ' . print_r( $give_updates->get_update_ids(), true );
			$log_data .= 'Update: ' . print_r( $resume_update, true );

			Give()->logs->add( 'Update Error', $log_data, 0, 'update' );

			wp_die();
		}

		// Disable cache.
		Give_Cache::disable();

		try {
			// Run update.
			if ( is_array( $update['callback'] ) ) {
				$object      = $update['callback'][0];
				$method_name = $update['callback'][1];

				$object->$method_name();

			} else {
				$update['callback']();
			}
		} catch ( Exception $e ) {

			if ( ! $this->is_paused_process() ) {
				$give_updates->__pause_db_update( true );
			}

			$log_data  = 'Update Task' . "\n";
			$log_data .= print_r( $resume_update, true ) . "\n\n";
			$log_data .= "Error\n {$e->getMessage()}";

			Give()->logs->add( 'Update Error', $log_data, 0, 'update' );
			update_option( 'give_upgrade_error', 1, false );

			wp_die();
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
		update_option( 'give_doing_upgrade', $doing_upgrade_args, false );

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
	public function complete() {
		if ( $this->is_paused_process() ) {
			return false;
		}

		parent::complete();

		delete_option( 'give_pause_upgrade' );
		delete_option( 'give_upgrade_error' );
		delete_option( 'give_db_update_count' );
		delete_option( 'give_doing_upgrade' );
		add_option( 'give_show_db_upgrade_complete_notice', 1, '', false );

		// Flush cache.
		Give_Cache::flush_cache( true );

		if ( $cache_keys = Give_Cache::get_options_like( '' ) ) {
			Give_Cache::delete( $cache_keys );
		}
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
			$memory_limit = '32G';
		}

		return give_let_to_num( $memory_limit );
	}

	/**
	 * Maybe process queue
	 *
	 * Checks whether data exists within the queue and that
	 * the process is not already running.
	 */
	public function maybe_handle() {
		// Don't lock up other requests while processing
		session_write_close();

		if ( $this->is_process_running() || $this->is_paused_process() ) {
			// Background process already running.
			wp_die();
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			wp_die();
		}

		check_ajax_referer( $this->identifier, 'nonce' );

		$this->handle();

		wp_die();
	}

	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {
		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {

			// Dispatch only if ajax works.
			if ( give_test_ajax_works() ) {
				$this->dispatch();
			}
		} else {
			$this->complete();
		}

		wp_die();
	}


	/**
	 * Check if backgound upgrade paused or not.
	 *
	 * @since 2.0
	 * @access public
	 * @return bool
	 */
	public function is_paused_process() {
		// Delete cache.
		wp_cache_delete( 'give_paused_batches', 'options' );

		$paused_batches = Give_Cache_Setting::get_option( 'give_paused_batches' );

		return ! empty( $paused_batches );
	}


	/**
	 * Get identifier
	 *
	 * @since  2.0
	 * @access public
	 * @return mixed|string
	 */
	public function get_identifier() {
		return $this->identifier;
	}

	/**
	 * Get cron identifier
	 *
	 * @since  2.0
	 * @access public
	 * @return mixed|string
	 */
	public function get_cron_identifier() {
		return $this->cron_hook_identifier;
	}


	/**
	 * Flush background update related cache to prevent task to go to stalled state.
	 *
	 * @since 2.0.3
	 */
	public static function flush_cache() {

		$options = array(
			'give_completed_upgrades',
			'give_doing_upgrade',
			'give_paused_batches',
			'give_upgrade_error',
			'give_db_update_count',
			'give_pause_upgrade',
			'give_show_db_upgrade_complete_notice',
		);

		foreach ( $options as $option ) {
			wp_cache_delete( $option, 'options' );
		}
	}
}
