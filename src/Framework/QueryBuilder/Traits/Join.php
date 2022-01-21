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
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string|null  $alias
     *
     * @return $this
     */
    public function leftJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join($table, $foreignKey, $primaryKey, 'LEFT', $alias);

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
        $this->join($table, $foreignKey, $primaryKey, 'INNER', $alias);

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
    public function outerJoin($table, $foreignKey, $primaryKey, $alias = null)
    {
        $this->join($table, $foreignKey, $primaryKey, 'OUTER', $alias);

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
        $this->join($table, $foreignKey, $primaryKey, 'RIGHT', $alias);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getJoinSQL()
    {
        return array_map(function (JoinTable $joinTable) {

            $from = $this->from->alias ? : $this->from->table;

            if ($joinTable->alias) {
                return "{$joinTable->joinType} JOIN {$joinTable->table} {$joinTable->alias} ON {$from}.{$joinTable->foreignKey} = {$joinTable->alias}.{$joinTable->primaryKey}";
            }

            return "{$joinTable->joinType} JOIN {$joinTable->table} ON {$from}.{$joinTable->foreignKey} = {$joinTable->table}.{$joinTable->primaryKey}";
        }, $this->joins);
    }
}
