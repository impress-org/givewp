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
	 * @var string
	 */
	private $log_table;

	/**
	 * LogRepository constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->log_table = "{$wpdb->prefix}give_log";
	}

	/**
	 * Insert Log into database
	 *
	 * @param  LogModel  $model
	 *
	 * @return int inserted log id
	 */
	public function insertLog( LogModel $model ) {
		DB::insert(
			$this->log_table,
			[
				'log_type' => $model->getType(),
				'data'     => $model->getData( $jsonEncode = true ),
				'category' => $model->getCategory(),
				'source'   => $model->getSource(),
			],
			null
		);

		return DB::last_insert_id();
	}

	/**
	 * Update log
	 *
	 * @param  LogModel  $model
	 *
	 * @return false|int
	 */
	public function updateLog( LogModel $model ) {
		return DB::update(
			$this->log_table,
			[
				'log_type' => $model->getType(),
				'data'     => $model->getData( $jsonEncode = true ),
				'category' => $model->getCategory(),
				'source'   => $model->getSource(),
			],
			[
				'id' => $model->getId(),
			]
		);
	}

	/**
	 * Get all logs
	 *
	 * @return LogModel[]
	 */
	public function getLogs() {
		$logs = [];

		$result = DB::get_results( "SELECT * FROM {$this->log_table} ORDER BY id DESC" );

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
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
		$log = DB::get_row(
			DB::prepare( "SELECT * FROM {$this->log_table} WHERE id = %d", $logId )
		);

		if ( $log ) {
			$data = json_decode( $log->data, true );

			return LogFactory::make(
				$log->log_type,
				$data['message'],
				$log->category,
				$log->source,
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
		$logs = [];

		$result = DB::get_results(
			DB::prepare( "SELECT * FROM {$this->log_table} WHERE type = %s", $type )
		);

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
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
		$logs = [];

		$result = DB::get_results(
			DB::prepare( "SELECT * FROM {$this->log_table} WHERE category = %s", $category )
		);

		if ( $result ) {
			foreach ( $result as $log ) {
				$data = json_decode( $log->data, true );

				$logs[] = LogFactory::make(
					$log->log_type,
					$data['message'],
					$log->category,
					$log->source,
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
		$categories = [];
		$result     = DB::get_results( "SELECT DISTINCT category FROM {$this->log_table}" );

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
		DB::query( "DELETE FROM {$this->log_table}" );
	}

	/**
	 * Delete log by ID
	 *
	 * @param  int  $logId
	 */
	public function deleteLog( $logId ) {
		DB::query(
			DB::prepare( "DELETE FROM {$this->log_table} WHERE id = %d", $logId )
		);
	}
}
