<?php

namespace Give\Framework\QueryBuilder\Models;

use Closure;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\Types\JoinType;
use Give\Framework\QueryBuilder\Types\Operator;
use InvalidArgumentException;

/**
 * @unreleased
 */
class Join
{
    /**
     * @var string
     */
    public $table;

    /**
     * @var JoinCondition[]
     */
    public $conditions = [];

    /**
     * @var string
     */
    public $joinType;

    /**
     * @var string|null
     */
    public $alias;

    /**
     * @param  string  $table
     * @param  string  $joinType  \Give\Framework\QueryBuilder\Types\JoinType
     * @param  array|Closure  $condition
     * @param  string|null  $alias
     */
    public function __construct($table, $joinType, $condition, $alias = null)
    {
        $this->table      = trim($table);
        $this->joinType   = $this->getJoinType($joinType);
        $this->conditions = $this->getJoinConditions($condition);
        $this->alias      = trim($alias);
    }

    /**
     * @param  string  $type
     *
     * @return string
     */
    private function getJoinType($type)
    {
        $type = strtoupper($type);

        if (array_key_exists($type, JoinType::getTypes())) {
            return $type;
        }

        throw new InvalidArgumentException(
            sprintf(
                'Join type %s is not supported. Please provide one of the supported join types (%s)',
                $type,
                implode(',', JoinType::getTypes())
            )
        );
    }

    /**
     * @param  array|Closure  $condition
     *
     * @return JoinCondition[]
     */
    private function getJoinConditions($condition)
    {
        if ($condition instanceof Closure) {
            $builder = new QueryBuilder();

            call_user_func($condition, $builder);

            return array_map(function (JoinCondition $join) {
                return new JoinCondition(
                    $join->logicalOperator,
                    $join->column1,
                    $join->comparisonOperator,
                    $join->column2,
                    $join->quote
                );
            }, $builder->getDefinedJoins());
        }

        if (is_array($condition)) {
            list($foreignKey, $primaryKey) = $condition;

            return [
                new JoinCondition(
                    Operator::ON,
                    $foreignKey,
                    '=',
                    $primaryKey
                )
            ];
        }

        throw new InvalidArgumentException(
            'Invalid argument type provided for condition parameter. Condition parameter must be a Closure instance or an Array ([foreignKey, primaryKey])'
        );
    }
}
