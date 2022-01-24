<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Types\Operator;

/**
 * @unreleased
 */
class Having
{
    /**
     * @var string
     */
    public $column;

    /**
     * @var string
     */
    public $comparisonOperator;

    /**
     * @var string|int
     */
    public $value;

    /**
     * @var string
     */
    public $logicalOperator;

    /**
     * @var string|null
     */
    public $mathFunction;

    /**
     * @param  string  $column
     * @param  string  $comparisonOperator
     * @param  string|int  $value
     * @param  string|null  $logicalOperator
     * @param  string  $mathFunction
     */
    public function __construct($column, $comparisonOperator, $value, $logicalOperator, $mathFunction = null)
    {
        $this->column             = $column;
        $this->comparisonOperator = $comparisonOperator;
        $this->value              = $value;
        $this->logicalOperator    = $logicalOperator ? $this->getLogicalOperator($logicalOperator) : '';
        $this->mathFunction       = $mathFunction;
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
