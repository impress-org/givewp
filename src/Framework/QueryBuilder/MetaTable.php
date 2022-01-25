<?php

namespace Give\Framework\QueryBuilder;

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
        $this->tableName       = $table;
        $this->keyColumnName   = $metaKeyColumnName;
        $this->valueColumnName = $metaValueColumnName;
    }
}
