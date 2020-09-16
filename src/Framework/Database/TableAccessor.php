<?php
namespace Give\Framework\Database;

/**
 * Class Table
 * @package Give\Database\Tables
 *
 * @since 2.9.0
 */
trait TableAccessor {
	/**
	 * Retrieve a row by the primary key
	 *
	 * @param string $tableName
	 * @param string $primaryKey
	 * @param int  $primaryKeyValue  Primary key value.
	 *
	 * @return object
	 * @since  2.9.0
	 * @access public
	 */
	public static function get( $tableName, $primaryKey, $primaryKeyValue ) {
		global $wpdb;

		return $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT * FROM {$tableName}
					WHERE {$primaryKey} = %s LIMIT 1;
				",
				$primaryKeyValue
			)
		);
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @param string $tableName
	 * @param string $columnName
	 * @param int  $columnValue  Row ID.
	 *
	 * @return object
	 * @since  2.9.0
	 * @access public
	 *
	 */
	public static function getBy( $tableName, $columnName, $columnValue ) {
		global $wpdb;

		$columnName = esc_sql( $columnName );

		return $wpdb->get_row(
			$wpdb->prepare(
				"
			SELECT * FROM {$tableName}
			WHERE {$columnName} = %s
			LIMIT 1;",
				$columnValue
			)
		);
	}

	/**
	 * Retrieve all rows by a specific column / value
	 * Note: currently support string comparison
	 *
	 * @param $tableName
	 * @param  array  $columnArgs  Array contains column key and expected value.
	 *
	 * @return array
	 * @since  2.9.0
	 * @access public
	 *
	 */
	public static function getResultsBy( $tableName, $columnArgs ) {
		global $wpdb;

		$columnArgs = wp_parse_args(
			$columnArgs,
			[ 'relation' => 'AND' ]
		);

		$relation = $columnArgs['relation'];
		unset( $columnArgs['relation'] );

		$where = [];
		foreach ( $columnArgs as $name => $value ) {
			$value = esc_sql( $value );
			$name  = esc_sql( $name );

			$where[] = "{$name}='{$value}'";
		}
		$where = implode( " {$relation} ", $where );

		return $wpdb->get_results(
			"
			SELECT * FROM {$tableName}
			WHERE {$where};"
		);
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @param $tableName
	 * @param $primaryKey
	 * @param  int  $primaryKeyValue  Row ID.
	 *
	 * @param $columnName
	 *
	 * @return string      Column value.
	 * @since  2.9.0
	 * @access public
	 *
	 */
	public static function getColumn( $tableName, $primaryKey, $primaryKeyValue, $columnName ) {
		global $wpdb;

		$columnName = esc_sql( $columnName );

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT {$columnName}
			FROM {$tableName}
			WHERE {$primaryKey} = %s
			LIMIT 1;",
				$primaryKeyValue
			)
		);
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @param string $tableName
	 * @param string $columnName  Column name
	 * @param string  $columnWhere .
	 * @param string  $columnValue  Column value.
	 *
	 * @return string
	 * @since  2.9.0
	 * @access public
	 *
	 */
	public static function getColumnBy( $tableName, $columnName, $columnWhere, $columnValue ) {
		global $wpdb;

		$columnWhere = esc_sql( $columnWhere );
		$columnName  = esc_sql( $columnName );

		return $wpdb->get_var(
			$wpdb->prepare(
				"
			SELECT {$columnName}
			FROM {$tableName}
			WHERE {$columnWhere} = %s
			LIMIT 1;",
				$columnValue
			)
		);
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @param string $tableName
	 * @param string $primaryKey
	 * @param int $primaryKeyValue
	 *
	 * @return int|bool
	 * @since  2.9.0
	 * @access public
	 *
	 */
	public static function delete( $tableName, $primaryKey, $primaryKeyValue ) {
		global $wpdb;

		return $wpdb->query(
			$wpdb->prepare(
				"
			DELETE FROM {$tableName}
			WHERE {$primaryKey} = %d",
				$primaryKeyValue
			)
		);
	}
}
