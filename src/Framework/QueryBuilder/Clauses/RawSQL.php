<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\Database\DB;

/**
 * @since 2.19.0
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
