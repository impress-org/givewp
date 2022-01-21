<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\Select as SelectClause;

trait Select
{

    /**
     * @var SelectClause[]
     */
    public $selects = [];

    /**
     * @param  array  $columns
     *
     * @return $this
     */
    public function select(...$columns)
    {
        $selects = array_map(function ($select) {
            if (is_array($select)) {
                list($column, $alias) = $select;

                return new SelectClause($column, $alias);
            }

            return new SelectClause($select);
        }, $columns);

        $this->selects = array_merge($this->selects, $selects);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSelectSQL()
    {
        return [
            'SELECT ' . implode(
                ', ',
                array_map(function ( SelectClause $select) {

                    if ( $select->alias ) {
                        return "{$select->column} AS {$select->alias}";
                    }
                    return $select->column;
                }, $this->selects)
            )
        ];
    }
}
