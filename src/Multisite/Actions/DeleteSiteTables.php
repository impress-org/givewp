<?php

namespace Give\Multisite\Actions;

use Give\Framework\Database\DB;

class DeleteSiteTables
{
    public function __invoke()
    {
        $prefix = DB::prefix('give_%');
        $giveTables = DB::get_col("SHOW TABLES LIKE '{$prefix}'");

        DB::query('DROP TABLE ' . implode(',', $giveTables));
    }
}
