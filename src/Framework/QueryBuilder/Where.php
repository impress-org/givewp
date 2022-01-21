<?php

namespace Give\Framework\QueryBuilder;

/**
 * @unreleased
 */
class Where
{
    /**
     * @var string
     */
    public $column;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $comparisonOperator;

    /**
     * @var string
     */
    public $logicalOperator;

    /**
     * @param  string  $column
     * @param  string  $value
     * @param  string  $comparisonOperator
     * @param  string  $logicalOperator
     */
    public function __construct($column, $value, $comparisonOperator, $logicalOperator)
    {
        $this->column             = $column;
        $this->value              = $value;
        $this->comparisonOperator = $this->getComparisonOperator($comparisonOperator);
        $this->logicalOperator    = $this->getLogicalOperator($logicalOperator);
    }

    /**
     * @param  string  $comparisonOperator
     *
     * @return string
     */
    private function getComparisonOperator($comparisonOperator)
    {
        if (in_array($comparisonOperator, ['<', '<=', '=', '>', '>=', '<>'])) {
            return $comparisonOperator;
        }

        return '=';
    }

    /**
     * @param  string  $logicalOperator
     *
     * @return string
     */
    private function getLogicalOperator($logicalOperator)
    {
        $logicalOperator = strtoupper($logicalOperator);

        if (in_array($logicalOperator, ['AND', 'OR', 'BETWEEN', 'EXISTS', 'IN', 'LIKE', 'NOT'])) {
            return $logicalOperator;
        }

        return 'AND';
    }
}
