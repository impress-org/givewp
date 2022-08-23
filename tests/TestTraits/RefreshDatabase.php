<?php

namespace GiveTests\TestTraits;

use Give\Framework\Database\DB;

trait RefreshDatabase {
    /**
     * Truncate all Give database tables.
     *
     * @unreleased
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
