<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Closure;
use Give\Framework\Database\DB;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Clauses\Where;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\Types\Operator;
use Give\Framework\QueryBuilder\WhereQueryBuilder;


/**
 * @since 2.19.0
 */
trait WhereClause
{

    /**
     * @var Where[]|RawSQL[]|string[]
     */
    protected $wheres = [];


    /**
     * @var bool
     */
    private $includeWhereKeyword = true;

    /**
     * @param  string|Closure|null  $column  The Closure will receive a Give\Framework\QueryBuilder\WhereQueryBuilder instance
     * @param  string|Closure|array|null  $value  The Closure will receive a Give\Framework\QueryBuilder\QueryBuilder instance
     * @param  string  $comparisonOperator
     * @param  string  $logicalOperator
     *
     * @return $this
     */
    private function setWhere($column, $value, $comparisonOperator, $logicalOperator)
    {
        // If the columns is a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parentheses.
        if ($column instanceof Closure && is_null($value)) {
            $builder = new WhereQueryBuilder();
            call_user_func($column, $builder);

            // Since this is a nested where statement, we have to remove the starting WHERE keyword
            // which is the first returned array element from the getWhereSQL method
            $wheres = $builder->getSQL();
            array_shift($wheres);

            $this->wheres[] = sprintf(
                "%s (%s)",
                empty($this->wheres) ? null : $logicalOperator,
                implode(' ', $wheres)
            );
        } // If the value is a Closure instance, we will assume the developer is performing an entire sub-select within the query
        elseif ($value instanceof Closure) {
            $builder = new QueryBuilder();
            call_user_func($value, $builder);

            $this->wheres[] = sprintf(
                "%s %s %s (%s)",
                empty($this->wheres) ? null : $logicalOperator,
                $column,
                $comparisonOperator,
                $builder->getSQL()
            );
        } // Standard WHERE clause
        else {
            $this->wheres[] = new Where(
                $column,
                $value,
                $comparisonOperator,
                empty($this->wheres) ? null : $logicalOperator
            );
        }

        return $this;
    }

    /**
     * @param  string|Closure  $column  The closure will receive a Give\Framework\QueryBuilder\WhereQueryBuilder instance
     * @param  string|Closure|array|null  $value  The closure will receive a Give\Framework\QueryBuilder\QueryBuilder instance
     * @param  string  $comparisonOperator
     *
     * @return $this
     */
    public function where($column, $value = null, $comparisonOperator = '=')
    {
        return $this->setWhere(
            $column,
            $value,
            $comparisonOperator,
            Operator::_AND
        );
    }

    /**
     * @param  string|Closure  $column
     * @param  string|Closure|array|null  $value
     * @param  string  $comparisonOperator
     *
     * @return $this
     */
    public function orWhere($column, $value = null, $comparisonOperator = '=')
    {
        return $this->setWhere(
            $column,
            $value,
            $comparisonOperator,
            Operator::_OR
        );
    }

    /**
     * @param  string  $column
     * @param  array|Closure  $value
     *
     * @return $this
     */
    public function whereIn($column, $value)
    {
        return $this->where(
            $column,
            $value,
            Operator::IN
        );
    }


    /**
     * @param  string  $column
     * @param  array|Closure  $value
     *
     * @return $this
     */
    public function orWhereIn($column, $value)
    {
        return $this->orWhere(
            $column,
            $value,
            Operator::IN
        );
    }

    /**
     * @param  string  $column
     * @param  array|Closure  $value
     *
     * @return $this
     */
    public function whereNotIn($column, $value)
    {
        return $this->where(
            $column,
            $value,
            Operator::NOTIN
        );
    }

    /**
     * @param  string  $column
     * @param  array|Closure  $value
     *
     * @return $this
     */
    public function orWhereNotIn($column, $value)
    {
        return $this->orWhere(
            $column,
            $value,
            Operator::NOTIN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $min
     * @param  string|int  $max
     *
     * @return $this
     */
    public function whereBetween($column, $min, $max)
    {
        return $this->where(
            $column,
            [$min, $max],
            Operator::BETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $min
     * @param  string|int  $max
     *
     * @return $this
     */
    public function whereNotBetween($column, $min, $max)
    {
        return $this->where(
            $column,
            [$min, $max],
            Operator::NOTBETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $min
     * @param  string|int  $max
     *
     * @return $this
     */
    public function orWhereBetween($column, $min, $max)
    {
        return $this->orWhere(
            $column,
            [$min, $max],
            Operator::BETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $min
     * @param  string|int  $max
     *
     * @return $this
     */
    public function orWhereNotBetween($column, $min, $max)
    {
        return $this->orWhere(
            $column,
            [$min, $max],
            Operator::NOTBETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string  $value
     *
     * @return $this
     */
    public function whereLike($column, $value)
    {
        return $this->where(
            $column,
            $value,
            Operator::LIKE
        );
    }

    /**
     * @param  string  $column
     * @param  string  $value
     *
     * @return $this
     */
    public function whereNotLike($column, $value)
    {
        return $this->where(
            $column,
            $value,
            Operator::NOTLIKE
        );
    }

    /**
     * @param  string  $column
     * @param  string  $value
     *
     * @return $this
     */
    public function orWhereLike($column, $value)
    {
        return $this->orWhere(
            $column,
            $value,
            Operator::LIKE
        );
    }

    /**
     * @param  string  $column
     * @param  string  $value
     *
     * @return $this
     */
    public function orWhereNotLike($column, $value)
    {
        return $this->orWhere(
            $column,
            $value,
            Operator::NOTLIKE
        );
    }

    /**
     * @param  string  $column
     *
     * @return $this
     */
    public function whereIsNull($column)
    {
        return $this->where(
            $column,
            null,
            Operator::ISNULL
        );
    }

    /**
     * @param  string  $column
     *
     * @return $this
     */
    public function orWhereIsNull($column)
    {
        return $this->orWhere(
            $column,
            null,
            Operator::ISNULL
        );
    }

    /**
     * @param  string  $column
     *
     * @return $this
     */
    public function whereIsNotNull($column)
    {
        return $this->where(
            $column,
            null,
            Operator::NOTNULL
        );
    }

    /**
     * @param  string  $column
     *
     * @return $this
     */
    public function orWhereIsNotNull($column)
    {
        return $this->orWhere(
            $column,
            null,
            Operator::NOTNULL
        );
    }


    /**
     * @param  Closure  $callback  The closure will receive a Give\Framework\QueryBuilder\QueryBuilder instance
     *
     * @return QueryBuilder|WhereQueryBuilder
     */
    public function whereExists($callback)
    {
        return $this->where(
            null,
            $callback,
            Operator::EXISTS
        );
    }

    /**
     * @param  Closure  $callback  The closure will receive a Give\Framework\QueryBuilder\QueryBuilder instance
     *
     * @return QueryBuilder|WhereQueryBuilder
     */
    public function whereNotExists($callback)
    {
        return $this->where(
            null,
            $callback,
            Operator::NOTEXISTS
        );
    }

    /**
     * Add raw SQL WHERE clause
     *
     * @param $sql
     * @param ...$args
     *
     * @return $this
     */
    public function whereRaw($sql, ...$args)
    {
        $this->wheres[] = new RawSQL($sql, $args);

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getWhereSQL()
    {
        // Bailout
        if (empty($this->wheres)) {
            return [];
        }

        $wheres = [];

        foreach ($this->wheres as $i => $where) {
            if ($where instanceof RawSQL) {
                if ($i === 0) {
                    // If the first element is an instance of RawSQL
                    // then we don't need the starting WHERE keyword because we assume that the dev will include that in RawSQL
                    $this->includeWhereKeyword = false;
                }
                $wheres[] = $where->sql;
                continue;
            }

            if ($where instanceof Where) {
                $wheres[] = $this->buildWhereSQL($where);
                continue;
            }

            // If the variable $where is not an instance of the Where class
            // it means the SQL is already generated by the Query Builder, so we just return that
            $wheres[] = $where;
        }


        if ($this->includeWhereKeyword) {
            return array_merge(['WHERE'], $wheres);
        }

        return $wheres;
    }

    /**
     * @param  Where  $where
     *
     * @return string
     */
    private function buildWhereSQL(Where $where)
    {
        switch ($where->comparisonOperator) {
            // Handle membership conditions
            case Operator::IN:
            case Operator::NOTIN:

                return DB::prepare(
                        "%1s %2s %3s",
                        $where->logicalOperator,
                        $where->column,
                        $where->comparisonOperator
                    ) . ' (' . implode(
                           ',',
                           array_map(function ($where) {
                               return DB::prepare('%s', $where);
                           }, $where->value)
                       ) . ')';

            // Handle BETWEEN conditions
            case Operator::BETWEEN:
            case Operator::NOTBETWEEN:
                list($min, $max) = $where->value;

                return DB::prepare(
                    "%1s %2s %3s %s AND %s",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    $min,
                    $max
                );

            // Handle LIKE conditions
            case Operator::LIKE:
            case Operator::NOTLIKE:
                return DB::prepare(
                    "%1s %2s %3s %s",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    strpos($where->value, '%') !== false
                        ? $where->value
                        : '%' . $where->value . '%'
                );

            // Handle NULL conditions
            case Operator::ISNULL:
            case Operator::NOTNULL:
                return DB::prepare(
                    "%1s %2s %3s",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator
                );

            // Standard WHERE clause
            default:
                return DB::prepare(
                    "%1s %2s %3s %s",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    $where->value
                );
        }
    }
}
