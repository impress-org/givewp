<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Clauses\Select;

/**
 * @since 2.19.0
 */
trait SelectStatement
{
    /**
     * @var Select[]|RawSQL[]
     */
    protected $selects = [];

    /**
     * @var bool
     */
    protected $distinct = false;

    /**
     * @var bool
     */
    private $includeSelectKeyword = true;

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
     * Add raw SQL SELECT statement
     *
     * @param  string  $sql
     * @param ...$args
     */
    public function selectRaw($sql, ...$args)
    {
        $this->selects[] = new RawSQL($sql, $args);

        return $this;
    }


    /**
     * Select distinct
     *
     * @return $this
     */
    public function distinct()
    {
        $this->distinct = true;

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getSelectSQL()
    {
        // Select all by default
        if (empty($this->selects)) {
            $this->select('*');
        }

        $selects = [];

        foreach ($this->selects as $i => $select) {
            if ($select instanceof RawSQL) {
                if ($i === 0) {
                    // If the first element is an instance of RawSQL
                    // then we don't need the starting SELECT keyword because we assume that the dev will include that in RawSQL
                    $this->includeSelectKeyword = false;
                }
                $selects[] = $select->sql;
                continue;
            }

            if ($select->alias) {
                $selects[] = DB::prepare(
                    '%1s AS %2s',
                    $select->column,
                    $select->alias
                );

                continue;
            }

            $selects[] = DB::prepare('%1s', $select->column);
        }

        $selectStatements = implode(', ', $selects);

        if ($this->includeSelectKeyword) {
            $keyword = 'SELECT ';

            if ($this->distinct) {
                $keyword .= 'DISTINCT ';
            }

            return [$keyword . $selectStatements];
        }

        return [$selectStatements];
    }
}
