<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Closure;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Models\Join;
use Give\Framework\QueryBuilder\Models\JoinCondition;
use Give\Framework\QueryBuilder\Types\JoinType;
use Give\Framework\QueryBuilder\Types\Operator;

/**
 * @unreleased
 */
trait JoinClause
{

    /**
     * @var Join[]
     */
    protected $joins = [];

    /**
     * Helper method used to build complex JOIN queries, Check README.md for more info.
     * If you need to perform only simple JOINs with one "simple" JOIN condition, then you don't need this method.
     *
     * @param  string  $table
     * @param  string  $joinType  \Give\Framework\QueryBuilder\Types\JoinType
     * @param  array|Closure  $condition
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function join($table, $joinType, $condition, $alias = null)
    {
        $this->joins[] = new Join(
            $table,
            $joinType,
            $condition,
            $alias
        );

        return $this;
    }

    /**
     * @param  string  $table
     * @param $foreignKey
     * @param $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function leftJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join(
            $table,
            JoinType::LEFT,
            [$foreignKey, $primaryKey],
            $alias
        );

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     *
     * @return $this
     */
    public function innerJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join(
            $table,
            JoinType::INNER,
            [$foreignKey, $primaryKey],
            $alias
        );

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function rightJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join(
            $table,
            JoinType::RIGHT,
            [$foreignKey, $primaryKey],
            $alias
        );

        return $this;
    }

    /**
     * Add JoinCondition using Query Builder
     *
     * @internal this is a special helper method for building complex JOIN queries. Check README.md for more info.
     *
     * @param  string  $column1
     * @param  string  $comparisonOperator
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function joinOn($column1, $comparisonOperator, $column2, $quote = false)
    {
        $this->joins[] = new JoinCondition(
            Operator::ON,
            $column1,
            $comparisonOperator,
            $column2,
            $quote
        );

        return $this;
    }

    /**
     * Add JoinCondition using Query Builder
     *
     * @internal this is a special helper method for building complex JOIN queries. Check README.md for more info.
     *
     * @param  string  $column1
     * @param  string  $comparisonOperator
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function joinAnd($column1, $comparisonOperator, $column2, $quote = false)
    {
        $this->joins[] = new JoinCondition(
            Operator::AND,
            $column1,
            $comparisonOperator,
            $column2,
            $quote
        );

        return $this;
    }

    /**
     * Add JoinCondition using Query Builder
     *
     * @internal this is a special helper method for building complex JOIN queries. Check README.md for more info.
     *
     * @param  string  $column1
     * @param  string  $comparisonOperator
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function joinOr($column1, $comparisonOperator, $column2, $quote = false)
    {
        $this->joins[] = new JoinCondition(
            Operator::OR,
            $column1,
            $comparisonOperator,
            $column2,
            $quote
        );

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getJoinSQL()
    {
        return array_map(function (Join $joinTable) {
            $conditions = array_map(function (JoinCondition $condition) {
                return DB::prepare(
                    $condition->quote
                        ? ' %1s %2s %3s %s'
                        : ' %1s %2s %3s %4s',
                    $condition->logicalOperator,
                    $condition->column1,
                    $condition->comparisonOperator,
                    $condition->column2
                );
            }, $joinTable->conditions);

            // Join table is using an alias
            if ($joinTable->alias) {
                return DB::prepare(
                        '%1s JOIN %2s %3s',
                        $joinTable->joinType,
                        $joinTable->table,
                        $joinTable->alias
                    ) . implode(' ', $conditions);
            }

            return DB::prepare(
                    '%1s JOIN %2s',
                    $joinTable->joinType,
                    $joinTable->table
                ) . implode(' ', $conditions);
        }, $this->joins);
    }

    /**
     * @internal This method is only used internally by the QueryBuilder, and it is not meant to be used when building queries
     *
     * @return Join[]
     */
    public function getDefinedJoins()
    {
        return $this->joins;
    }
}
