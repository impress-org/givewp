<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\Join;
use Give\Framework\QueryBuilder\Types\JoinType;

/**
 * @unreleased
 */
trait JoinClause
{

    /**
     * @var Join[]
     */
    public $joins = [];

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string  $joinType \Give\Framework\QueryBuilder\Types\JoinType
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function join($table, $foreignKey, $primaryKey, $joinType, $alias = null)
    {
        $this->joins[] = new Join($table, $foreignKey, $primaryKey, $joinType, $alias);

        return $this;
    }


    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function leftJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join($table, $foreignKey, $primaryKey, JoinType::LEFT, $alias);

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function innerJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join($table, $foreignKey, $primaryKey, JoinType::INNER, $alias);

        return $this;
    }

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function rightJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join($table, $foreignKey, $primaryKey, JoinType::RIGHT, $alias);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getJoinSQL()
    {
        return array_map(function (Join $joinTable) {
            if ($joinTable->alias) {
                return "{$joinTable->joinType} JOIN {$joinTable->table} {$joinTable->alias} ON {$joinTable->foreignKey} = {$joinTable->alias}.{$joinTable->primaryKey}";
            }

            return "{$joinTable->joinType} JOIN {$joinTable->table} ON {$joinTable->foreignKey} = {$joinTable->table}.{$joinTable->primaryKey}";
        }, $this->joins);
    }
}
