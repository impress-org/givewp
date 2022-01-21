<?php

namespace Give\Framework\QueryBuilder;

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
     * @param  string  $joinType
     * @param  string|null  $alias
     */
    public function __construct($table, $foreignKey, $primaryKey, $joinType = 'LEFT', $alias = null)
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

        if (in_array($type, ['INNER', 'LEFT', 'RIGHT', 'OUTER'])) {
            return $type;
        }

        return 'LEFT';
    }
}
