<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Clauses\Join;
use Give\Framework\QueryBuilder\Clauses\JoinCondition;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\Types\JoinType;
use Give\Framework\QueryBuilder\Types\Operator;

/**
 * @since 2.19.0
 */
class JoinQueryBuilder
{

    /**
     * @var Join[]|JoinCondition[]|RawSQL[]
     */
    private $joins = [];

    /**
     * @param  string|RawSQL  $table
     * @param  null|string  $alias
     *
     * @return $this
     */
    public function leftJoin($table, $alias = null)
    {
        return $this->join(
            JoinType::LEFT,
            $table,
            $alias
        );
    }

    /**
     * @param  string|RawSQL  $table
     * @param  null|string  $alias
     *
     * @return $this
     */
    public function rightJoin($table, $alias = null)
    {
        return $this->join(
            JoinType::RIGHT,
            $table,
            $alias
        );
    }

    /**
     * @param  string|RawSQL  $table
     * @param  null|string  $alias
     *
     * @return $this
     */
    public function innerJoin($table, $alias = null)
    {
        return $this->join(
            JoinType::INNER,
            $table,
            $alias
        );
    }

    /**
     * @param  string  $column1
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function on($column1, $column2, $quote = false)
    {
        return $this->joinCondition(
            Operator::ON,
            $column1,
            $column2,
            $quote
        );
    }

    /**
     * @param  string  $column1
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function andOn($column1, $column2, $quote = null)
    {
        return $this->joinCondition(
            Operator::_AND,
            $column1,
            $column2,
            $quote
        );
    }

    /**
     * @param  string  $column1
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    public function orOn($column1, $column2, $quote = null)
    {
        return $this->joinCondition(
            Operator::_OR,
            $column1,
            $column2,
            $quote
        );
    }

    /**
     * Add raw SQL JOIN clause
     *
     * @param  string  $sql
     * @param ...$args
     */
    public function joinRaw($sql, ...$args)
    {
        $this->joins[] = new RawSQL($sql, $args);
    }

    /**
     * Add Join
     *
     * @param  string  $joinType
     * @param  string|RawSQL  $table
     * @param  string  $alias
     *
     * @return $this
     */
    private function join($joinType, $table, $alias)
    {
        $this->joins[] = new Join(
            $joinType,
            $table,
            $alias
        );

        return $this;
    }

    /**
     * Add JoinCondition
     *
     * @param  string  $operator
     * @param  string  $column1
     * @param  string  $column2
     * @param  bool  $quote
     *
     * @return $this
     */
    private function joinCondition($operator, $column1, $column2, $quote)
    {
        $this->joins[] = new JoinCondition(
            $operator,
            $column1,
            $column2,
            $quote
        );

        return $this;
    }

    /**
     * @return Join[]|JoinCondition[]
     */
    public function getDefinedJoins()
    {
        return $this->joins;
    }
}
