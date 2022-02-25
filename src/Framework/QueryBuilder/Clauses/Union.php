<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.0
 */
class Union
{
    /**
     * @var QueryBuilder
     */
    public $builder;

    /**
     * @var bool
     */
    public $all = false;

    /**
     * @param  QueryBuilder  $builder
     * @param  bool  $all
     */
    public function __construct($builder, $all = false)
    {
        $this->builder = $builder;
        $this->all     = $all;
    }
}
