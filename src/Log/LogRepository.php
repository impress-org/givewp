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
	 * @param  string $message
	 * @param  string  $category
	 * @param  string  $source
	 * @param  string  $migration_id
	 * @param  array  $context
	 *
	 * @return int inserted log id
	 */
	public function insertLog( $type, $message, $category, $source, $migration_id = null, $context = [] ) {
		global $wpdb;

		$data = [
			'message' => $message,
			'context' => $context,
		];

		$wpdb->insert(
			$wpdb->give_log,
			[
				'log_type'     => $type,
				'migration_id' => $migration_id,
				'data'         => json_encode( $data ),
				'category'     => $category,
				'source'       => $source,
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

		$result = $wpdb->get_results( "SELECT * FROM { $wpdb->give_log } ORDER BY id DESC" );

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
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_log } WHERE id = %d", $logId )
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
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_log } WHERE type = %s", $type )
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
			$wpdb->prepare( "SELECT * FROM { $wpdb->give_log } WHERE category = %s", $category )
		);

		if ( $result ) {
			return $result;
		}

		return [];
	}

	/**
	 * Get logs categories
	 *
	 * @return array
	 */
	public function getCategories() {
		global $wpdb;

		$categories = [];
		$result     = $wpdb->get_results( "SELECT DISTINCT category FROM { $wpdb->give_log }" );

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

		$wpdb->query( "DELETE FROM { $wpdb->give_log }" );
	}

	/**
	 * Delete log by ID
	 *
	 * @param  int  $logId
	 */
	public function deleteLog( $logId ) {
		global $wpdb;

		$wpdb->query(
			$wpdb->prepare( "DELETE FROM { $wpdb->give_log } WHERE id = %d", $logId )
		);
	}
}
