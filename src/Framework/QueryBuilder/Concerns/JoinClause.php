<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Closure;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\Clauses\Join;
use Give\Framework\QueryBuilder\Clauses\RawSQL;

/**
 * @since 2.19.0
 */
trait JoinClause
{

    /**
     * @var Closure[]|RawSQL[]
     */
    protected $joins = [];

    /**
     * Method used to build advanced JOIN queries, Check README.md for more info.
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
     * @param  string|RawSQL  $table
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
     * @param  string|RawSQL  $table
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
     * @param  string|RawSQL  $table
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
     * Add raw SQL JOIN clause
     *
     * @param  string  $sql
     * @param ...$args
     *
     * @return $this;
     */
    public function joinRaw($sql, ...$args)
    {
        $this->joins[] = new RawSQL($sql, $args);

        return $this;
    }


    /**
     * @return string[]
     */
    protected function getJoinSQL()
    {
        return array_map(function ($callback) {
            if ($callback instanceof RawSQL) {
                return $callback->sql;
            }

            $builder = new JoinQueryBuilder();

            call_user_func($callback, $builder);

            $joins = array_map(function ($join) {
                if ($join instanceof RawSQL) {
                    return $join->sql;
                }

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
