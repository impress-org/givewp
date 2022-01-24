<?php

namespace Give\Framework\QueryBuilder;

use Give\Framework\QueryBuilder\Types\JoinType;

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
     * @var string
     */
    public $foreignKey;

    /**
     * @var string
     */
    public $primaryKey;

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
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  string  $joinType  \Give\Framework\QueryBuilder\Types\JoinType
     * @param  string|null  $alias
     */
    public function __construct($table, $foreignKey, $primaryKey, $joinType = JoinType::LEFT, $alias = null)
    {
        $this->table      = $table;
        $this->foreignKey = $foreignKey;
        $this->primaryKey = $primaryKey;
        $this->joinType   = $this->getJoinType($joinType);
        $this->alias      = $alias;
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

        return JoinType::LEFT;
    }
}
