<?php

namespace Give\Framework\QueryBuilder\Models;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
class RawSQL
{
    /**
     * @var string
     */
    public $sql;

    /**
     * @param  string  $sql
     * @param  null  $args
     */
    public function __construct($sql, $args = null)
    {
        $this->sql = $args ? DB::prepare($sql, $args) : $sql;
    }
}
