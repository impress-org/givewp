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
        $wpCommentTables = DB::get_col("SHOW TABLES LIKE '%comment%'");
        $wpPostTables = DB::get_col("SHOW TABLES LIKE '%post%'");

        foreach (array_merge($giveTables, $wpCommentTables, $wpPostTables) as $table) {
            DB::query("TRUNCATE TABLE $table");
        }
    }
}
