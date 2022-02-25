<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\QueryBuilder\Types\Operator;
use InvalidArgumentException;

/**
 * @since 2.19.0
 */
class JoinCondition
{
    /**
     * @var string
     */
    public $logicalOperator;

    /**
     * @var string
     */
    public $column1;

    /**
     * @var mixed
     */
    public $column2;

    /**
     * @var bool
     */
    public $quote;


    /**
     * @param  string  $logicalOperator
     * @param  string  $column1
     * @param  string  $column2
     * @param  bool  $quote
     */
    public function __construct($logicalOperator, $column1, $column2, $quote = false)
    {
        $this->logicalOperator = $this->getLogicalOperator($logicalOperator);
        $this->column1         = trim($column1);
        $this->column2         = trim($column2);
        $this->quote           = $quote;
    }

    /**
     * @param  string  $operator
     *
     * @return string
     */
    private function getLogicalOperator($operator)
    {
        $operator = strtoupper($operator);

        $supportedOperators = [
            Operator::ON,
            Operator::_AND,
            Operator::_OR
        ];

        if ( ! in_array($operator, $supportedOperators, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported logical operator %s. Please provide one of the supported operators (%s)',
                    $operator,
                    implode(',', $supportedOperators)
                )
            );
        }

        return $operator;
    }
}
