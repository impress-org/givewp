<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Types\Operator;

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
     * @var mixed
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
     * @var string|null
     */
    public $type;

    /**
     * @param  string  $column
     * @param  string  $value
     * @param  string  $comparisonOperator
     * @param  string|null  $logicalOperator
     */
    public function __construct($column, $value, $comparisonOperator, $logicalOperator)
    {
        $this->column             = $column;
        $this->value              = $value;
        $this->comparisonOperator = $this->getComparisonOperator($comparisonOperator);
        $this->logicalOperator    = $logicalOperator ? $this->getLogicalOperator($logicalOperator) : '';
    }

    /**
     * @param  string  $comparisonOperator
     *
     * @return string
     */
    private function getComparisonOperator($comparisonOperator)
    {
        $operators = [
            '<',
            '<=',
            '>',
            '>=',
            '<>',
            Operator::LIKE,
            Operator::IN,
            Operator::NOTIN,
            Operator::BETWEEN,
            Operator::NOTBETWEEN,
        ];

        if (in_array($comparisonOperator, $operators)) {
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

        if (array_key_exists($logicalOperator, Operator::getTypes())) {
            return $logicalOperator;
        }

        return Operator::AND;
    }
}
