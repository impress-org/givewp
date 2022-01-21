<?php

namespace Give\Framework\QueryBuilder;

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
     * @param  string  $table
     * @param  string|null  $alias
     */
    public function __construct($table, $alias = null )
    {
        $this->table = $table;
        $this->alias  = $alias;
    }
}
