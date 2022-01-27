<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Closure;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\Models\Join;

/**
 * @unreleased
 */
trait JoinClause
{

    /**
     * @var JoinQueryBuilder[]
     */
    protected $joins = [];

    /**
     * Method used to build complex JOIN queries, Check README.md for more info.
     * If you need to perform only simple JOINs with only one JOIN condition, then you don't need this method.
     *
     * @param  Closure  $callback  The closure will receive a Give\Framework\QueryBuilder\JoinQueryBuilder instance
     *
     * @return $this
     */
    public function join($callback)
    {
        $this->joins[] = $callback;

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $column1
     * @param  string  $column2
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function leftJoin($table, $column1, $column2, $alias = null)
    {
        $this->join(
            function (JoinQueryBuilder $builder) use ($table, $column1, $column2, $alias) {
                $builder
                    ->leftJoin($table, $alias)
                    ->on($column1, $column2);
            }
        );

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $column1
     * @param  string  $column2
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function innerJoin($table, $column1, $column2, $alias = null)
    {
        $this->join(
            function (JoinQueryBuilder $builder) use ($table, $column1, $column2, $alias) {
                $builder
                    ->innerJoin($table, $alias)
                    ->on($column1, $column2);
            }
        );

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $column1
     * @param  string  $column2
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function rightJoin($table, $column1, $column2, $alias = null)
    {
        $this->join(
            function (JoinQueryBuilder $builder) use ($table, $column1, $column2, $alias) {
                $builder
                    ->rightJoin($table, $alias)
                    ->on($column1, $column2);
            }
        );

        return $this;
    }


    /**
     * @return string[]
     */
    protected function getJoinSQL()
    {
        return array_map(function (Closure $callback) {
            $builder = new JoinQueryBuilder();

            call_user_func($callback, $builder);

            $joins = array_map(function ($join) {
                if ($join instanceof Join) {
                    if ($join->alias) {
                        return DB::prepare(
                            '%1s JOIN %2s %3s',
                            $join->joinType,
                            $join->table,
                            $join->alias
                        );
                    }

                    return DB::prepare(
                        '%1s JOIN %2s',
                        $join->joinType,
                        $join->table
                    );
                }

                // JoinCondition
                return DB::prepare(
                    $join->quote
                        ? ' %1s %2s = %s'
                        : ' %1s %2s = %3s',
                    $join->logicalOperator,
                    $join->column1,
                    $join->column2
                );
            }, $builder->getDefinedJoins());

            return implode(' ', $joins);
        }, $this->joins);
    }
}
