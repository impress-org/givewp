<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Models\RawSQL;
use Give\Framework\QueryBuilder\Models\Select;

/**
 * @unreleased
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
    private $selectUseRawSql = false;

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
        $this->selectUseRawSql = true;
        $this->selects         = array_merge($this->selects, [new RawSQL($sql, $args)]);

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

        $selects = implode(
            ', ',
            array_map(function ($select) {
                if ($select instanceof RawSQL) {
                    return $select->sql;
                }

                if ($select->alias) {
                    return DB::prepare(
                        '%1s AS %2s',
                        $select->column,
                        $select->alias
                    );
                }

                return DB::prepare('%1s', $select->column);
            }, $this->selects)
        );

        if ($this->selectUseRawSql) {
            return [$selects];
        }

        return ['SELECT ' . $selects];
    }
}
