<?php

declare(strict_types=1);

namespace Give\Framework\Database\Actions;

class EnableBigSqlSelects
{
    /**
     * @unreleased
     *
     * Enables mysql big selects for the session using a session system variable.
     *
     * @see https://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_sql_big_selects
     * @see https://dev.mysql.com/doc/refman/5.7/en/system-variable-privileges.html
     *
     */
    public function __invoke()
    {
        static $bigSelects = false;

        if (!$bigSelects) {
            global $wpdb;

            $wpdb->query('SET SESSION SQL_BIG_SELECTS=1;');

            $bigSelects = true;
        }
    }
}
