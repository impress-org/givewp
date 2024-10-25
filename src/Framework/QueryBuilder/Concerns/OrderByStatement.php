<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\OrderBy;
use Give\Framework\QueryBuilder\Clauses\RawSQL;

/**
 * @since 2.19.0
 */
trait OrderByStatement
{
    /**
     * @var OrderBy[]
     */
    protected $orderBys = [];

    /**
     * @param  string  $column
     * @param  string  $direction  ASC|DESC
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBys[] = new OrderBy($column, $direction);

        return $this;
    }

    /**
     * Add raw SQL Order By statement
     *
     * @unreleased
     *
     * @param $sql
     * @param ...$args
     *
     * @return $this
     */
    public function orderByRaw($sql, ...$args)
    {
        $this->orderBys[] = new RawSQL($sql, $args);

        return $this;
    }

    /**
     * @return array|string[]
     */
    protected function getOrderBySQL()
    {
        if (empty($this->orderBys)) {
            return [];
        }

        $orderBys = implode(
            ', ',
            array_map(function ($order) {
                if ($order instanceof RawSQL) {
                    return DB::prepare('%s', $order->sql);
                }
                return DB::prepare('%1s %2s', $order->column, $order->direction);
            }, $this->orderBys)
        );


        return ['ORDER BY ' . $orderBys];
    }
}
