<?php

namespace Give\Log\Helpers;

use Give\Framework\Database\DB;

/**
 * Class LogsLegacyTable
 * @package Give\Log\Helpers
 *
 * @since 2.10.2
 */
class LegacyLogsTable
{
    /**
     * Check if legacy logs table exists
     *
     * @return bool
     */
    public function exist()
    {
        global $wpdb;

        return (bool)DB::get_var(
            DB::prepare("SHOW TABLES LIKE '%s'", "{$wpdb->prefix}give_logs")
        );
    }
}
