<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\Where as WhereClause;

trait Where
{

    /**
     * @var string
     */
    public $wheres = [];

    /**
     * @param  string  $column
     * @param  string  $value
     * @param  string  $comparisonOperator
     * @param  string  $logicalOperator
     *
     * @return $this
     */
    private function setWhere($column, $value, $comparisonOperator, $logicalOperator)
    {
        $this->wheres[] = new WhereClause($column, $value, $comparisonOperator, $logicalOperator);

        return $this;
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string  $value
     *
     * @return $this
     */
    public function where($column, $value, $comparisonOperator = '=')
    {
        return $this->setWhere($column, $value, $comparisonOperator, 'AND');
    }

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string  $value
     *
     * @return $this
     */
    public function orWhere($column, $value, $comparisonOperator = '=')
    {
        return $this->setWhere($column, $value, $comparisonOperator, 'OR');
    }

    public function getWhereSQL()
    {
        $wheres = array_map(function (WhereClause $whereClause) {
            return "{$whereClause->logicalOperator} {$whereClause->column} {$whereClause->comparisonOperator} '{$whereClause->value}'";
        }, $this->wheres);

        return array_merge(['WHERE 1'], $wheres);
    }
}
