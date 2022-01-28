<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\QueryBuilder\Helpers\Table;

/**
 * @unreleased
 */
class From
{
    /**
     * @var string
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
        $this->table = Table::prefix($table);
        $this->alias = trim($alias);
    }
}
