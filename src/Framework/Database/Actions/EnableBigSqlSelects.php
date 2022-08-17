<?php

declare(strict_types=1);

namespace Give\Framework\Database\Actions;

class EnableBigSqlSelects
{
    /**
     * @since 2.22.0
     *
     * Enables mysql big selects for the session using a session system variable.
     *
     * This is necessary for hosts that have an arbitrary MAX_JOIN_SIZE limit, which prevents more complex queries from
     * running properly. Setting SQL_BIG_SELECTS ignores this limit. This is also done by WooCommerce, supporting the
     * idea that this is a viable option. There also doesn't seem to be a way for hosts to prevent this.
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
