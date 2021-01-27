<?php

namespace Give\Log;

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
	 * @param  string  $type
	 * @param  string  $message
	 * @param  string  $category
	 * @param  string  $source
	 *
	 * @return int inserted log id
	 */
	public function insertLog( $type, $message, $category, $source ) {
		global $wpdb;

		$wpdb->insert(
			$wpdb->give_logs,
			[
				'type'     => $type,
				'message'  => $message,
				'category' => $category,
				'source'   => $source,
				'date'     => current_time( 'mysql' ),
			]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Insert log metadata
	 *
	 * @param  int  $logId
	 * @param  string  $key
	 * @param  mixed  $value
	 *
	 * @return int
	 */
	public function insertLogMeta( $logId, $key, $value ) {
		global $wpdb;

		// Prepare value
		if ( is_array( $value ) || is_object( $value ) ) {
			$value = print_r( $value, true );
		}

		$wpdb->insert(
			$wpdb->give_logs_meta,
			[
				'log_id'    => $logId,
				'log_key'   => $key,
				'log_value' => $value,
			]
		);

		return $wpdb->insert_id;
	}

	/**
	 * Get all logs
	 *
	 * @return array
	 */
	public function getLogs() {
		global $wpdb;

		$result = $wpdb->get_results( "SELECT * FROM { $wpdb->give_logs } ORDER BY id DESC" );

		if ( $result ) {
			return $result;
		}

		return [];
	}

	/**
	 * Get log by ID
	 *
	 * @param  int  $logId
	 *
	 * @return object|null
	 */
	public function getLog( $logId ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_logs } WHERE id = %d", $logId )
		);

		if ( $result ) {
			return $result;
		}

		return null;
	}

	/**
	 * Get logs by type
	 *
	 * @param  string  $type
	 *
	 * @return array
	 */
	public function getLogsByType( $type ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_logs } WHERE type = %s", $type )
		);

		if ( $result ) {
			return $result;
		}

		return [];
	}

	/**
	 * Get logs by category
	 *
	 * @param  string  $category
	 *
	 * @return array
	 */
	public function getLogsByCategory( $category ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_logs } WHERE category = %s", $category )
		);

		if ( $result ) {
			return $result;
		}

		return [];
	}

	/**
	 * Get log meta data
	 *
	 * @param $logId
	 *
	 * @return array
	 */
	public function getLogMeta( $logId ) {
		global $wpdb;

		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_logs_meta } WHERE log_id = %d", $logId )
		);

		if ( $result ) {
			return $result;
		}

		return [];
	}

	/**
	 * Get log meta data by key
	 *
	 * @param  int  $logId
	 * @param  string  $key
	 *
	 * @return object|null
	 */
	public function getLogMetaByKey( $logId, $key ) {
		global $wpdb;

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_logs_meta } WHERE log_id = %d AND meta_key = %s", $logId, $key )
		);

		if ( $result ) {
			return $result;
		}

		return null;
	}


	/**
	 * Get logs categories
	 *
	 * @return array
	 */
	public function getCategories() {
		global $wpdb;

		$categories = [];
		$result     = $wpdb->get_results( "SELECT DISTINCT category FROM { $wpdb->give_logs }" );

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

		$wpdb->query( "DELETE FROM { $wpdb->give_logs }, { $wpdb->give_logs_meta }" );
	}

	/**
	 * Delete log by ID
	 *
	 * @param  int  $logId
	 */
	public function deleteLog( $logId ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM { $wpdb->give_logs } WHERE id = %d", $logId )
		);

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM { $wpdb->give_logs_meta } WHERE log_id = %d", $logId )
		);
	}
}
