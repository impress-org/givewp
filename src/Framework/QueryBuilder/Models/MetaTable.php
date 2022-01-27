<?php

namespace Give\Framework\QueryBuilder\Models;

use Give\Framework\QueryBuilder\Helpers\Table;

/**
 * @unreleased
 */
class MetaTable
{
    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $keyColumnName;

    /**
     * @var string
     */
    public $valueColumnName;

    /**
     * @param  string  $table
     * @param  string  $metaKeyColumnName
     * @param  string  $metaValueColumnName
     */
    public function __construct($table, $metaKeyColumnName, $metaValueColumnName)
    {
        $this->tableName       = Table::prefix($table);
        $this->keyColumnName   = trim($metaKeyColumnName);
        $this->valueColumnName = trim($metaValueColumnName);
    }
}
