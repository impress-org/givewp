<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\Where;
use Give\Framework\QueryBuilder\Types\LogicalOperator;

/**
 * @unreleased
 */
trait WhereTrait
{

    /**
     * @var string
     */
    protected $wheres = [];

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
        $this->wheres[] = new Where($column, $value, $comparisonOperator, $logicalOperator);

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
        return $this->setWhere($column, $value, $comparisonOperator, LogicalOperator::AND);
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
        return $this->setWhere($column, $value, $comparisonOperator, LogicalOperator::OR);
    }

    public function getWhereSQL()
    {
        $wheres = array_map(function (Where $where) {
            return "{$where->logicalOperator} {$where->column} {$where->comparisonOperator} '{$where->value}'";
        }, $this->wheres);

        return array_merge(['WHERE 1'], $wheres);
    }
}
