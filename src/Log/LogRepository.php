<?php

namespace Give\Log;

use InvalidArgumentException;
use Give\Framework\Database\DB;

/**
 * Class LogRepository
 * @package Give\Log\Repositories
 *
 * @since 2.9.7
 */
class LogRepository {

	const SORTABLE_COLUMNS = [ 'id', 'category', 'source', 'log_type', 'date' ];

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
				'log_type'     => $model->getType(),
				'migration_id' => $model->getMigrationId(),
				'data'         => $model->getData( $jsonEncode = true ),
				'category'     => $model->getCategory(),
				'source'       => $model->getSource(),
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
				'log_type'     => $model->getType(),
				'migration_id' => $model->getMigrationId(),
				'data'         => $model->getData( $jsonEncode = true ),
				'category'     => $model->getCategory(),
				'source'       => $model->getSource(),
			],
			[
				'id' => $model->getId(),
			]
		);
	}

	/**
	 * Get all logs
	 *
	 * @param  int|null  $limit Number of rows to return
	 * @param  int|null  $offset Limit offset
	 * @param  string|null  $sortBy  Column name
	 * @param  string|null  $sortDirection  ASC|DESC
	 *
	 * @return LogModel[]
	 */
	public function getLogs( $limit = null, $offset = null, $sortBy = null, $sortDirection = null ) {
		$logs = [];

		if ( $sortBy ) {
			$column    = ( in_array( $sortBy, self::SORTABLE_COLUMNS, true ) ) ? $sortBy : 'id';
			$direction = ( $sortDirection && strtoupper( $sortDirection ) === 'ASC' ) ? 'ASC' : 'DESC';

			$query = "SELECT * FROM {$this->log_table} ORDER BY {$column} {$direction}";
		} else {
			$query = "SELECT * FROM {$this->log_table} ORDER BY id DESC";
		}

		// Limit and offset
		if ( $limit ) {
			$query .= sprintf( ' LIMIT %d', $limit );
			if ( $offset > 1 ) {
				$query .= sprintf( ' OFFSET %d', $offset );
			}
		}

		$result = DB::get_results( $query );

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

	public function getTotalCount() {
		return DB::get_var( "SELECT count(id) FROM {$this->log_table}" );
	}

	/**
	 * Get log count by column name containing value
	 *
	 * @param string $columnName
	 * @param string $value
	 *
	 * @return string|null
	 */
	public function getLogCountBy( $columnName, $value ) {
		if ( ! in_array( $columnName, self::SORTABLE_COLUMNS, true ) ) {
			throw new InvalidArgumentException(
				sprintf( 'Invalid column %s', $columnName )
			);
		}
		return DB::get_var(
			DB::prepare( "SELECT count(id) FROM {$this->log_table} WHERE {$columnName}=%s", $value )
		);
	}

	/**
	 * Get sortable columns
	 *
	 * @return string[]
	 */
	public function getSortableColumns() {
		return self::SORTABLE_COLUMNS;
	}
}
