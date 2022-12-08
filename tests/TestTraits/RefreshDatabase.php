<?php

namespace Give\Tests\TestTraits;

use Give\Framework\Database\DB;

trait RefreshDatabase {
    /**
     * Truncate all Give database tables.
     *
     * @since 2.22.1
     *
     * @return void
     */
    public function refreshDatabase()
    {
	    $giveTables = DB::get_col("SHOW TABLES LIKE '%give%'");
        $wpTables = DB::get_col("SHOW TABLES LIKE '%post%'");

        foreach (array_merge($giveTables, $wpTables) as $table) {
            DB::query("TRUNCATE TABLE $table");
        }
    }
}
