<?php

namespace Give\Framework\Database;

use Give\Framework\Database\Exceptions\DatabaseQueryException;
use WP_Error;

class DB {
	/**
	 * Runs the dbDelta function and returns a WP_Error with any errors that occurred during the process
	 *
	 * @see dbDelta() for parameter and return details
	 *
	 * @since 2.9.2
	 *
	 * @param $delta
	 *
	 * @return array
	 * @throws DatabaseQueryException
	 */
	public static function delta( $delta ) {
		return self::runQueryWithErrorChecking(
			function () use ( $delta ) {
				return dbDelta( $delta );
			}
		);
	}

	/**
	 * Runs the $wpdb::delete method with SQL error checking
	 *
	 * @see wpdb::delete() for parameter and return details
	 *
	 * @since 2.9.2
	 *
	 * @param string $tableName
	 * @param array  $data
	 * @param array  $formats
	 *
	 * @return int|false
	 * @throws DatabaseQueryException
	 */
	public static function delete( $tableName, $data, $formats ) {
		return self::runQueryWithErrorChecking(
			function () use ( $tableName, $data, $formats ) {
				global $wpdb;

				return $wpdb->delete( $tableName, $data, $formats = null );
			}
		);
	}

	/**
	 * Runs a query callable and checks to see if any unique SQL errors occurred when it was run
	 *
	 * @since 2.9.2
	 *
	 * @param Callable $queryCaller
	 *
	 * @return mixed
	 * @throws DatabaseQueryException
	 */
	private static function runQueryWithErrorChecking( $queryCaller ) {
		global $wpdb, $EZSQL_ERROR;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$errorCount    = is_array( $EZSQL_ERROR ) ? count( $EZSQL_ERROR ) : 0;
		$hasShowErrors = $wpdb->hide_errors();

		$output = $queryCaller();

		if ( $hasShowErrors ) {
			$wpdb->show_errors();
		}

		$errors = self::getQueryErrors( $errorCount );

		if ( $errors->has_errors() ) {
			throw DatabaseQueryException::create( $errors->get_error_messages() );
		}

		return $output;
	}

	/**
	 * Retrieves the SQL errors stored by WordPress
	 *
	 * @since 2.9.2
	 *
	 * @param int $initialCount
	 *
	 * @return WP_Error
	 */
	private static function getQueryErrors( $initialCount = 0 ) {
		global $EZSQL_ERROR;

		$wpError = new WP_Error();

		if ( is_array( $EZSQL_ERROR ) ) {
			for ( $index = $initialCount, $indexMax = count( $EZSQL_ERROR ); $index < $indexMax; $index ++ ) {
				$error = $EZSQL_ERROR[ $index ];

				if ( empty( $error['error_str'] ) || empty( $error['query'] ) || 0 === strpos( $error['query'], 'DESCRIBE ' ) ) {
					continue;
				}

				$wpError->add( 'db_delta_error', $error['error_str'] );
			}
		}

		return $wpError;
	}
}
