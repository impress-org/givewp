<?php

namespace Give\Multisite\Actions;

use Give\Framework\Database\DB;

class DeleteSiteTables
{
    /**
     * @unreleased
     */
    public function __invoke()
    {
        $prefix = DB::prefix('give_%');
        $giveTables = DB::get_col("SHOW TABLES LIKE '{$prefix}'");

        DB::query('DROP TABLE ' . implode(',', $giveTables));

        wp_clear_scheduled_hook('give_daily_scheduled_events');
        wp_clear_scheduled_hook('give_weekly_scheduled_events');
        wp_clear_scheduled_hook('give_daily_cron');
        wp_clear_scheduled_hook('give_weekly_cron');
    }
}
