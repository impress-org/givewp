<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\Join as JoinTable;

trait Join
{

    /**
     * @var JoinTable[]
     */
    protected $joins = [];

    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function join($table, $foreignKey, $primaryKey, $joinType = 'LEFT', $alias = null)
    {
        $this->joins[] = new JoinTable($table, $foreignKey, $primaryKey, $joinType, $alias);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getJoinSQL()
    {
        return array_map(function (JoinTable $joinTable) {
            if ($joinTable->alias) {
                return "{$joinTable->joinType} JOIN {$joinTable->table} {$joinTable->alias} ON {$this->from->alias}.{$joinTable->foreignKey} = {$joinTable->alias}.{$joinTable->primaryKey}";
            }

            return "{$joinTable->joinType} JOIN {$joinTable->table} ON {$this->from->table}.{$joinTable->foreignKey} = {$joinTable->table}.{$joinTable->primaryKey}";
        }, $this->joins);
    }
}
