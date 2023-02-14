<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\Having;

use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Types\Math;
use Give\Framework\QueryBuilder\Types\Operator;

/**
 * @since 2.19.0
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
    private $includeHavingKeyword = true;

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
            empty($this->havings) ? null : Operator::_AND,
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
            empty($this->havings) ? null : Operator::_OR,
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
     *
     * @return $this
     */
    public function havingRaw($sql, ...$args)
    {
        $this->havings[] = new RawSQL($sql, $args);

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

        $havings = [];

        foreach ($this->havings as $i => $having) {
            if ($having instanceof RawSQL) {
                if ($i === 0) {
                    // If the first element is an instance of RawSQL
                    // then we don't need the starting HAVING keyword because we assume that the dev will include that in RawSQL
                    $this->includeHavingKeyword = false;
                }
                $havings[] = $having->sql;
                continue;
            }

            $havings[] = $this->buildHavingSQL($having);
        }

        if ($this->includeHavingKeyword) {
            return array_merge(['HAVING'], $havings);
        }

        return $havings;
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
            "%1s %2s %3s %s",
            $having->logicalOperator,
            $having->column,
            $having->comparisonOperator,
            $having->value
        );
    }
}
