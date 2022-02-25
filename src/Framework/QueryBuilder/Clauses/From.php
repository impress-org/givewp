<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.0
 */
class From
{
    /**
     * @var string|RawSQL
     */
    public $table;

    /**
     * @var string
     */
    public $alias;

    /**
     * @param  string|RawSQL  $table
     * @param  string|null  $alias
     */
    public function __construct($table, $alias = null)
    {
        $this->table = QueryBuilder::prefixTable($table);
        $this->alias = trim($alias);
    }
}
