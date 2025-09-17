<?php

use Give\Framework\Database\DB;

function phpstan_rule_fixture_no_limit_get_all() {
    // Should be flagged by the rule (no limit()/paginate())
    /** @var \Give\Framework\QueryBuilder\QueryBuilder $q */
    $q = DB::table('give_donationmeta')
        ->where('meta_key', 'x');

    $rows = $q->getAll();

    return $rows;
}


