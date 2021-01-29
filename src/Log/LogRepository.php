<?php

namespace Give\Log;

use Give\Framework\Database\DB;

/**
 * Class LogRepository
 * @package Give\Log\Repositories
 *
 * @since 2.9.7
 */
class LogRepository {
	/**
	 * Insert Log into database
	 *
	 * @param  LogModel  $model
	 *
	 * @return int inserted log id
	 */
	public function insertLog( LogModel $model ) {
		global $wpdb;

		DB::insert(
			$wpdb->give_log,
			[
				'log_type'     => $model->getType(),
				'migration_id' => $model->getMigrationId(),
				'data'         => $model->getData( $jsonEncode = true ),
				'category'     => $model->getCategory(),
				'source'       => $model->getSource(),
			],
			null
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get all logs
	 *
	 * @return LogModel[]
	 */
	public function getLogs() {
		global $wpdb;

		$logs = [];

		$result = DB::get_results( "SELECT * FROM {$wpdb->give_log} ORDER BY id DESC" );

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
					$log->migration_id,
					$data['context'],
					$log->id
				);
			}
		}

		return $logs;
	}

	/**
	 * Get log by ID
	 *
	 * @param  int  $logId
	 *
	 * @return LogModel|null
	 */
	public function getLog( $logId ) {
		global $wpdb;

		$log = DB::get_row(
			DB::prepare( "SELECT * FROM {$wpdb->give_log} WHERE id = %d", $logId )
		);

		if ( $log ) {
			$data = json_decode( $log->data, true );

			return LogFactory::make(
				$log->log_type,
				$data['message'],
				$log->category,
				$log->source,
				$log->migration_id,
				$data['context'],
				$log->id
			);
		}

		return null;
	}

	/**
	 * Get logs by type
	 *
	 * @param  string  $type
	 *
	 * @return LogModel[]
	 */
	public function getLogsByType( $type ) {
		global $wpdb;

		$logs = [];

		$result = DB::get_results(
			DB::prepare( "SELECT * FROM {$wpdb->give_log} WHERE type = %s", $type )
		);

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
					$log->migration_id,
					$data['context'],
					$log->id
				);
			}
		}

		return $logs;
	}

	/**
	 * Get logs by category
	 *
	 * @param  string  $category
	 *
	 * @return LogModel[]
	 */
	public function getLogsByCategory( $category ) {
		global $wpdb;

		$logs = [];

		$result = DB::get_results(
			DB::prepare( "SELECT * FROM {$wpdb->give_log} WHERE category = %s", $category )
		);

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
					$log->migration_id,
					$data['context'],
					$log->id
				);
			}
		}

		return $logs;
	}

	/**
	 * Get logs categories
	 *
	 * @return array
	 */
	public function getCategories() {
		global $wpdb;

		$categories = [];
		$result     = DB::get_results( "SELECT DISTINCT category FROM {$wpdb->give_log}" );

		if ( $result ) {
			foreach ( $result as $category ) {
				$category[] = $category->category;
			}
		}

		return $categories;
	}


	/**
	 * Delete all logs
	 */
	public function deleteLogs() {
		global $wpdb;

		DB::query( "DELETE FROM {$wpdb->give_log}" );
	}

	/**
	 * Delete log by ID
	 *
	 * @param  int  $logId
	 */
	public function deleteLog( $logId ) {
		global $wpdb;

		DB::query(
			DB::prepare( "DELETE FROM {$wpdb->give_log} WHERE id = %d", $logId )
		);
	}
}
