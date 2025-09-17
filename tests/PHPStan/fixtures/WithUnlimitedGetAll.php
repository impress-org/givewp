<?php

use Give\Framework\Database\DB;

function phpstan_rule_fixture_with_unlimited_get_all() {
    // Should pass (explicit unlimited)
    $rows = DB::table('give_donationmeta')
        ->where('meta_key', 'x')
        ->limit(0)
        ->getAll();

    return $rows;
}


