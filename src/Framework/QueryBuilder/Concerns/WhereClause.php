<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Closure;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\Types\Operator;
use Give\Framework\QueryBuilder\Where;

/**
 * @unreleased
 */
trait WhereClause
{

    /**
     * @var Where|string[]
     */
    protected $wheres = [];

    /**
     * @param  string|Closure  $column
     * @param  string|Closure|array|null  $value
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
            $builder = new QueryBuilder();
            call_user_func($column, $builder);

            // Since this is a nested where statement, we have to remove the starting WHERE keyword
            // which is the first returned array element from the getWhereSQL method
            $wheres = $builder->getWhereSQL();
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
     * @param  string|Closure  $column
     * @param  string|Closure|array|null  $value
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
            Operator::AND
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
            Operator::OR
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
     * @param  string|int  $value1
     * @param  string|int  $value2
     *
     * @return $this
     */
    public function whereBetween($column, $value1, $value2)
    {
        return $this->where(
            $column,
            [$value1, $value2],
            Operator::BETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $value1
     * @param  string|int  $value2
     *
     * @return $this
     */
    public function whereNotBetween($column, $value1, $value2)
    {
        return $this->where(
            $column,
            [$value1, $value2],
            Operator::NOTBETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $value1
     * @param  string|int  $value2
     *
     * @return $this
     */
    public function orWhereBetween($column, $value1, $value2)
    {
        return $this->orWhere(
            $column,
            [$value1, $value2],
            Operator::BETWEEN
        );
    }

    /**
     * @param  string  $column
     * @param  string|int  $value1
     * @param  string|int  $value2
     *
     * @return $this
     */
    public function orWhereNotBetween($column, $value1, $value2)
    {
        return $this->orWhere(
            $column,
            [$value1, $value2],
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
     * @return string[]
     */
    public function getWhereSQL()
    {
        $wheres = array_map(function ($where) {
            if ($where instanceof Where) {
                return $this->buildWhereSQL($where);
            }

            // If the variable $where is not an instance of the Where class
            // it means the SQL is already generated by the Query Builder, so we just return that
            return $where;
        }, $this->wheres);

        return array_merge(['WHERE'], $wheres);
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
                return sprintf(
                    "%s %s %s ('%s')",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    implode("','", esc_sql($where->value))
                );

            // Handle BETWEEN conditions
            case Operator::BETWEEN:
            case Operator::NOTBETWEEN:
                list($min, $max) = esc_sql($where->value);

                return sprintf(
                    "%s %s %s '%s' AND '%s'",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    $min,
                    $max
                );

            // Handle LIKE conditions
            case Operator::LIKE:
            case Operator::NOTLIKE:
                return sprintf(
                    "%s %s %s '%%%s%%'",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    esc_sql($where->value)
                );


            // Standard WHERE clause
            default:
                return sprintf(
                    "%s %s %s '%s'",
                    $where->logicalOperator,
                    $where->column,
                    $where->comparisonOperator,
                    esc_sql($where->value)
                );
        }
    }
}
