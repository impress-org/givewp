<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\Clauses\Union;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.0
 */
trait UnionOperator
{
    /**
     * @var int
     */
    protected $unions = [];

    /**
     * @param  QueryBuilder  $union
     *
     * @return $this
     */
    public function union(...$union)
    {
        $this->unions = array_map(function (QueryBuilder $builder) {
            return new Union($builder);
        }, $union);

        return $this;
    }

    /**
     * @param  QueryBuilder  $union
     *
     * @return $this
     */
    public function unionAll(...$union)
    {
        $this->unions = array_map(function (QueryBuilder $builder) {
            return new Union($builder, true);
        }, $union);

        return $this;
    }

    /**
     * @return array|string[]
     */
    protected function getUnionSQL()
    {
        if (empty($this->unions)) {
            return [];
        }

        return array_map(function (Union $union) {
            return ( $union->all ? 'UNION ALL ' : 'UNION ' ) . $union->builder->getSQL();
        }, $this->unions);
    }
}
