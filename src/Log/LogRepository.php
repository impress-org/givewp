<?php

namespace Give\Log;

use InvalidArgumentException;
use Give\Framework\Database\DB;
use WP_REST_Request;
use DateTime;

/**
 * Class LogRepository
 * @package Give\Log\Repositories
 *
 * @since 2.10.0
 */
class LogRepository {

	const SORTABLE_COLUMNS = [ 'id', 'category', 'source', 'log_type', 'date' ];

	const LOGS_PER_PAGE = 10;

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
				'date'     => $model->getDate(),
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
				'date'     => $model->getDate(),
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
		$logs   = [];
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
					$log->id,
					$log->date
				);
			}
		}

		return $logs;
	}

	/**
	 * Get all logs for request
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return LogModel[]
	 */
	public function getLogsForRequest( WP_REST_Request $request ) {
		$logs = [];

		$type          = $request->get_param( 'type' );
		$category      = $request->get_param( 'category' );
		$source        = $request->get_param( 'source' );
		$page          = $request->get_param( 'page' );
		$sortBy        = $request->get_param( 'sort' );
		$startDate     = $request->get_param( 'start' );
		$endDate       = $request->get_param( 'end' );
		$sortDirection = $request->get_param( 'direction' );

		$offset = ( $page - 1 ) * self::LOGS_PER_PAGE;

		$query = "SELECT * FROM {$this->log_table} WHERE 1=1";

		if ( $type ) {
			$query .= sprintf( ' AND log_type = "%s"', esc_sql( $type ) );
		}

		if ( $category ) {
			$query .= sprintf( ' AND category = "%s"', esc_sql( $category ) );
		}

		if ( $source ) {
			$query .= sprintf( ' AND source = "%s"', esc_sql( $source ) );
		}

		if ( $startDate ) {
			$startDate = new DateTime( $startDate );
			$query    .= sprintf( " AND date(date) >= '%s'", $startDate->format( 'Y-m-d' ) );
		}

		if ( $endDate ) {
			$endDate = new DateTime( $endDate );
			$query  .= sprintf( " AND date(date) <= '%s'", $endDate->format( 'Y-m-d' ) );
		}

		if ( $sortBy ) {
			$column    = ( in_array( $sortBy, self::SORTABLE_COLUMNS, true ) ) ? $sortBy : 'id';
			$direction = ( $sortDirection && strtoupper( $sortDirection ) === 'ASC' ) ? 'ASC' : 'DESC';

			$query .= " ORDER BY `{$column}` {$direction}";
		} else {
			$query .= ' ORDER BY id DESC';
		}

		// Limit
		$query .= sprintf( ' LIMIT %d', self::LOGS_PER_PAGE );

		// Offset
		if ( $offset > 1 ) {
			$query .= sprintf( ' OFFSET %d', $offset );
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
					$data['context'],
					$log->id,
					$log->date
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
				$log->id,
				$log->date
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
			DB::prepare( "SELECT * FROM {$this->log_table} WHERE log_type = %s", $type )
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
					$log->id,
					$log->date
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
					$log->id,
					$log->date
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
				$categories[] = $category->category;
			}
		}

		return $categories;
	}

	/**
	 * Get logs sources
	 *
	 * @return array
	 */
	public function getSources() {
		$sources = [];
		$result  = DB::get_results( "SELECT DISTINCT source FROM {$this->log_table}" );

		if ( $result ) {
			foreach ( $result as $source ) {
				$sources[] = $source->source;
			}
		}

		return $sources;
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
	 * Get log count for request
	 *
	 * @param  WP_REST_Request  $request
	 *
	 * @return int|null
	 */
	public function getLogCountForRequest( WP_REST_Request $request ) {
		$type      = $request->get_param( 'type' );
		$category  = $request->get_param( 'category' );
		$source    = $request->get_param( 'source' );
		$startDate = $request->get_param( 'start' );
		$endDate   = $request->get_param( 'end' );

		$query = "SELECT count(id) FROM {$this->log_table} WHERE 1=1";

		if ( $type ) {
			$query .= sprintf( ' AND log_type = "%s"', esc_sql( $type ) );
		}

		if ( $category ) {
			$query .= sprintf( ' AND category = "%s"', esc_sql( $category ) );
		}

		if ( $source ) {
			$query .= sprintf( ' AND source = "%s"', esc_sql( $source ) );
		}

		if ( $startDate ) {
			$startDate = new DateTime( $startDate );
			$query    .= sprintf( " AND date(date) >= '%s'", $startDate->format( 'Y-m-d' ) );
		}

		if ( $endDate ) {
			$endDate = new DateTime( $endDate );
			$query  .= sprintf( " AND date(date) <= '%s'", $endDate->format( 'Y-m-d' ) );
		}

		return DB::get_var( $query );
	}

	/**
	 * Get sortable columns
	 *
	 * @return string[]
	 */
	public function getSortableColumns() {
		return self::SORTABLE_COLUMNS;
	}

	/**
	 * Get logs per page limit
	 *
	 * @return int
	 */
	public function getLogsPerPageLimit() {
		return self::LOGS_PER_PAGE;
	}

	/**
	 * Flush logs
	 */
	public function flushLogs() {
		DB::query( "DELETE FROM {$this->log_table}" );
	}
}
