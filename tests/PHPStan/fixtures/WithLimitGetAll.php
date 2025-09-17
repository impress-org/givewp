<?php

use Give\Framework\Database\DB;

function phpstan_rule_fixture_with_limit_get_all() {
    // Should pass (explicit limit)
    $rows = DB::table('give_donationmeta')
        ->where('meta_key', 'x')
        ->limit(50)
        ->getAll();

    return $rows;
}


