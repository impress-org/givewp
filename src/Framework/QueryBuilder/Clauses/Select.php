<?php

namespace Give\Framework\QueryBuilder\Clauses;

/**
 * @since 2.19.0
 */
class Select
{
    /**
     * @var string
     */
    public $column;

    /**
     * @var string
     */
    public $alias;

    /**
     * @param  string  $column
     * @param  string|null  $alias
     */
    public function __construct($column, $alias = null)
    {
        $this->column = trim($column);
        $this->alias  = trim($alias);
    }
}
