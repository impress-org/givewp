<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @unreleased
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
            return sprintf('UNION %s', $builder->getSQL());
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
            return sprintf('UNION ALL %s', $builder->getSQL());
        }, $union);

        return $this;
    }

    protected function getUnionSQL()
    {
        return ! empty($this->unions)
            ? [implode(' ', $this->unions)]
            : [];
    }
}
