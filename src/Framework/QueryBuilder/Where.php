<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Types\Operator;
use InvalidArgumentException;

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

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported comparison operator %s. Please use one of the supported operators (%s)',
                $comparisonOperator,
                implode(',', $operators)
            )
        );
    }

    /**
     * @param  string  $logicalOperator
     *
     * @return string
     */
    private function getLogicalOperator($logicalOperator)
    {
        $operators = [
            Operator::AND,
            Operator::OR
        ];

        $logicalOperator = strtoupper($logicalOperator);

        if (array_key_exists($logicalOperator, $operators)) {
            return $logicalOperator;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Unsupported logical operator %s. Please use one of the supported operators (%s)',
                $logicalOperator,
                implode(',', $operators)
            )
        );
    }
}
