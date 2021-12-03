<?php

namespace Give\Log\Migrations;

use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * Class DeleteOldLogTables
 * @package Give\Log\Migrations
 */
class DeleteOldLogTables extends Migration
{
    /**
     * @return string
     */
    public static function id()
    {
        return 'delete_old_log_tables';
    }

    /**
     * @return string
     */
    public static function title()
    {
        return esc_html__('Delete give_logs and give_logmeta tables', 'give');
    }

    /**
     * @return int
     */
    public static function timestamp()
    {
        return strtotime('2021-01-28 14:00');
    }

    public function run()
    {
        global $wpdb;

        $logs_table = "{$wpdb->prefix}give_logs";
        $logmeta_table = "{$wpdb->prefix}give_logmeta";

        DB::query("DROP TABLE IF EXISTS {$logs_table}, {$logmeta_table};");
    }
}
