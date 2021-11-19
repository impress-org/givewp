<?php

namespace Give\Helpers;

/**
 * Class Table
 * @package Give\Helpers
 *
 * @since 2.9.0
 */
class Table
{
    /**
     * Get table name.
     *
     * @since 2.9.0
     *
     * @param $tableName
     *
     * @return string
     */
    public static function prefixTableName($tableName)
    {
        global $wpdb;

        return "{$wpdb->prefix}{$tableName}";
    }

    /**
     * Check if the given table exists
     *
     * @since  2.9.0
     * @access public
     *
     * @param $tableName
     *
     * @return bool          If the table name exists.
     */
    public static function tableExists($tableName)
    {
        global $wpdb;

        return (bool)$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE '%s'", $tableName));
    }

    /**
     * Checks whether column exists in a table or not.
     *
     * @since 2.9.0
     *
     * @param $columnName
     * @param $tableName
     *
     * @return bool
     */
    public static function doesColumnExist($tableName, $columnName)
    {
        global $wpdb;

        return (bool)$wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS
						WHERE TABLE_SCHEMA = %s
						AND TABLE_NAME = %s
						AND COLUMN_NAME = %s ',
                DB_NAME,
                $tableName,
                $columnName
            )
        );
    }
}

