<?php

namespace Give\Helpers;

use WP_Error;

class DB {
	/**
	 * Runs the dbDelta function and returns a WP_Error with any errors that occurred during the process
	 *
	 * @since 2.9.2
	 *
	 * @param $delta
	 *
	 * @return WP_Error
	 */
	public static function delta( $delta ) {
		global $wpdb, $EZSQL_ERROR;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$errorCount    = is_array( $EZSQL_ERROR ) ? count( $EZSQL_ERROR ) : 0;
		$hasShowErrors = $wpdb->hide_errors();

		dbDelta( $delta );

		if ( $hasShowErrors ) {
			$wpdb->show_errors();
		}

		$wpError = new WP_Error();

		if ( is_array( $EZSQL_ERROR ) ) {
			for ( $index = $errorCount, $indexMax = count( $EZSQL_ERROR ); $index < $indexMax; $index ++ ) {
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
