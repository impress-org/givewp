<?php
namespace Give\Helpers;

/**
 * Class Table
 * @package Give\Helpers
 *
 * @since 2.9.0
 */
class Table {
	/**
	 * Get table name.
	 *
	 * @since 2.9.0
	 *
	 * @param $tableName
	 *
	 * @return string
	 */
	public static function getName( $tableName ) {
		global $wpdb;

		return "{$wpdb->prefix}{$tableName}";
	}

	/**
	 * Check if the given table exists
	 *
	 * @param $tableName
	 *
	 * @return bool          If the table name exists.
	 * @since  2.9.0
	 * @access public
	 */
	public static function tableExists( $tableName ) {
		global $wpdb;

		return (bool) $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", self::getName( $tableName ) ) );
	}

	/**
	 * Checks whether column exists in a table or not.
	 *
	 * @param $tableName
	 * @param $columnName
	 *
	 * @return bool
	 * @since 2.9.0
	 */
	public static function doesColumnExist( $tableName, $columnName ) {
		global $wpdb;

		return (bool) $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
						WHERE TABLE_SCHEMA = %s
						AND TABLE_NAME = %s
						AND COLUMN_NAME = %s ',
				DB_NAME,
				self::getName( $tableName ),
				$columnName
			)
		);
	}
}

