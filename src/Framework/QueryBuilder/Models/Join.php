<?php

namespace Give\Framework\QueryBuilder\Models;

use Give\Framework\QueryBuilder\Helpers\Table;
use Give\Framework\QueryBuilder\Types\JoinType;
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
     * @param  string|null  $alias
     */
    public function __construct($joinType, $table, $alias = null)
    {
        $this->table    = Table::prefix($table);
        $this->joinType = $this->getJoinType($joinType);
        $this->alias    = trim($alias);
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
}
