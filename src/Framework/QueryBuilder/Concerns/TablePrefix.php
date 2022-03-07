<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\Clauses\RawSQL;

/**
 * @since 2.19.0
 */
trait TablePrefix
{
    /**
     * @param  string  $table
     *
     * @return string
     */
    public static function prefixTable($table)
    {
        global $wpdb;

        //  Shared tables in  multisite environment
        $sharedTables = [
            'users'    => $wpdb->users,
            'usermeta' => $wpdb->usermeta,
        ];

        if ($table instanceof RawSQL) {
            return $table->sql;
        }

        if (array_key_exists($table, $sharedTables)) {
            return $sharedTables[ $table ];
        }

        return $wpdb->prefix . $table;
    }
}
