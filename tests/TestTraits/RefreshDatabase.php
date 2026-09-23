<?php

namespace Give\Tests\TestTraits;

use Give\Framework\Database\DB;

trait RefreshDatabase {
    /**
     * Truncate all Give database tables.
     *
     * @since 4.17.0 Match tables by the current prefix so parallel workers do not truncate each other's tables.
     * @since 2.22.1
     *
     * @return void
     */
    public function refreshDatabase()
    {
        $prefix = DB::prefix('');
        $giveTables = DB::get_col("SHOW TABLES LIKE '{$prefix}give%'");
        $wpCommentTables = DB::get_col("SHOW TABLES LIKE '{$prefix}comment%'");
        $wpPostTables = DB::get_col("SHOW TABLES LIKE '{$prefix}post%'");

        foreach (array_merge($giveTables, $wpCommentTables, $wpPostTables) as $table) {
            DB::query("TRUNCATE TABLE $table");
        }
    }
}
