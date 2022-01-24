<?php

namespace Give\Framework\QueryBuilder;

/**
 * @unreleased
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
        $this->column = $column;
        $this->alias  = $alias;
    }
}
