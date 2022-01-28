<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\Having;

use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Types\Math;
use Give\Framework\QueryBuilder\Types\Operator;

/**
 * @unreleased
 */
trait HavingClause
{
    /**
     * @var Having[]|RawSQL[]
     */
    protected $havings = [];

    /**
     * @var bool
     */
    private $havingUseRawSql = false;

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string  $value
     * @param  null|string  $mathFunction  \Give\Framework\QueryBuilder\Types\Math
     *
     * @return $this
     *
     */
    public function having($column, $comparisonOperator, $value, $mathFunction = null)
    {
        $this->havings[] = new Having(
            $column,
            $comparisonOperator,
            $value,
            empty($this->havings) ? null : Operator::AND,
            $mathFunction
        );

        return $this;
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string  $value
     * @param  null|string  $mathFunction  \Give\Framework\QueryBuilder\Types\Math
     *
     * @return $this
     */
    public function orHaving($column, $comparisonOperator, $value, $mathFunction = null)
    {
        $this->havings[] = new Having(
            $column,
            $comparisonOperator,
            $value,
            empty($this->havings) ? null : Operator::OR,
            $mathFunction
        );

        return $this;
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function havingCount($column, $comparisonOperator, $value)
    {
        return $this->having(
            $column,
            $comparisonOperator,
            $value,
            Math::COUNT
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function orHavingCount($column, $comparisonOperator, $value)
    {
        return $this->orHaving(
            $column,
            $comparisonOperator,
            $value,
            Math::COUNT
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function havingMin($column, $comparisonOperator, $value)
    {
        return $this->having(
            $column,
            $comparisonOperator,
            $value,
            Math::MIN
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function orHavingMin($column, $comparisonOperator, $value)
    {
        return $this->orHaving(
            $column,
            $comparisonOperator,
            $value,
            Math::MIN
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function havingMax($column, $comparisonOperator, $value)
    {
        return $this->having(
            $column,
            $comparisonOperator,
            $value,
            Math::MAX
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function orHavingMax($column, $comparisonOperator, $value)
    {
        return $this->orHaving(
            $column,
            $comparisonOperator,
            $value,
            Math::MAX
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function havingAvg($column, $comparisonOperator, $value)
    {
        return $this->having(
            $column,
            $comparisonOperator,
            $value,
            Math::AVG
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function orHavingAvg($column, $comparisonOperator, $value)
    {
        return $this->orHaving(
            $column,
            $comparisonOperator,
            $value,
            Math::AVG
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function havingSum($column, $comparisonOperator, $value)
    {
        return $this->having(
            $column,
            $comparisonOperator,
            $value,
            Math::SUM
        );
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     *
     * @return $this
     */
    public function orHavingSum($column, $comparisonOperator, $value)
    {
        return $this->orHaving(
            $column,
            $comparisonOperator,
            $value,
            Math::SUM
        );
    }

    /**
     * Add raw SQL HAVING clause
     *
     * @param  string  $sql
     * @param ...$args
     * @return $this
     */
    public function havingRaw($sql, ...$args)
    {
        $this->havingUseRawSql = true;
        $this->havings[]       = new RawSQL($sql, $args);

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getHavingSQL()
    {
        if (empty($this->havings)) {
            return [];
        }

        $havings = array_map(function ($having) {
            if ($having instanceof RawSQL) {
                return $having->sql;
            }

            return $this->buildHavingSQL($having);
        }, $this->havings);

        if ($this->havingUseRawSql) {
            return $havings;
        }

        return array_merge(['HAVING'], $havings);
    }

    /**
     * @param  Having  $having
     *
     * @return string
     */
    private function buildHavingSQL(Having $having)
    {
        if ($having->mathFunction) {
            return DB::prepare(
                "%1s %2s(%3s) %4s %s",
                $having->logicalOperator,
                $having->mathFunction,
                $having->column,
                $having->comparisonOperator,
                $having->value
            );
        }

        return DB::prepare(
            "%1s %s %3s %s",
            $having->logicalOperator,
            $having->column,
            $having->comparisonOperator,
            $having->value
        );
    }
}
