<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\Select;

/**
 * @unreleased
 */
trait SelectStatement
{
    /**
     * @var Select[]
     */
    protected $selects = [];

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

                return new Select($column, $alias);
            }

            return new Select($select);
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
                array_map(function (Select $select) {
                    if ($select->alias) {
                        return sprintf(
                            '%s AS %s',
                            $select->column,
                            $select->alias
                        );
                    }

                    return $select->column;
                }, $this->selects)
            )
        ];
    }
}
