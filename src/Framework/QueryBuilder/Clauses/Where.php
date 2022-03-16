<?php

namespace Give\Framework\QueryBuilder\Clauses;

use Give\Framework\QueryBuilder\Types\Operator;
use InvalidArgumentException;

/**
 * @since 2.19.0
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
        $this->column             = trim($column);
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
            '!=',
            '=',
            Operator::LIKE,
            Operator::NOTLIKE,
            Operator::IN,
            Operator::NOTIN,
            Operator::BETWEEN,
            Operator::NOTBETWEEN,
            Operator::ISNULL,
            Operator::NOTNULL
        ];

        if (!in_array($comparisonOperator, $operators, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported comparison operator %s. Please use one of the supported operators (%s)',
                    $comparisonOperator,
                    implode(',', $operators)
                )
            );
        }

        return $comparisonOperator;
    }

    /**
     * @param  string  $logicalOperator
     *
     * @return string
     */
    private function getLogicalOperator($logicalOperator)
    {
        $operators = [
            Operator::_AND,
            Operator::_OR
        ];

        $logicalOperator = strtoupper($logicalOperator);

        if (!in_array($logicalOperator, $operators, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported logical operator %s. Please use one of the supported operators (%s)',
                    $logicalOperator,
                    implode(',', $operators)
                )
            );
        }

        return $logicalOperator;
    }
}
