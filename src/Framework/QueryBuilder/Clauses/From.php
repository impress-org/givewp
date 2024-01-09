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
     * @param string|RawSQL $table
     * @param string|null   $alias
     */
    public function __construct($table, $alias = null)
    {
        $this->table = QueryBuilder::prefixTable($table);

        if ( ! is_null($alias)) {
            $this->alias = trim($alias);
        }
    }
}
