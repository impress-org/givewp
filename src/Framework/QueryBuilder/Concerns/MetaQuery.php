<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\JoinQueryBuilder;
use Give\Framework\QueryBuilder\Clauses\MetaTable;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.0
 */
trait MetaQuery
{

    /**
     * @var MetaTable[]
     */
    private $metaTablesConfigs = [];

    /**
     * @var string
     */
    private $defaultMetaKeyColumn = 'meta_key';

    /**
     * @var string
     */
    private $defaultMetaValueColumn = 'meta_value';

    /**
     * @param string|RawSQL $table
     * @param string $metaKeyColumn
     * @param string $metaValueColumn
     *
     * @return $this
     */
    public function configureMetaTable($table, $metaKeyColumn, $metaValueColumn)
    {
        $this->metaTablesConfigs[] = new MetaTable(
            $table,
            $metaKeyColumn,
            $metaValueColumn
        );

        return $this;
    }

    /**
     * @param string|RawSQL $table
     *
     * @return MetaTable
     */
    protected function getMetaTable($table)
    {
        $tableName = QueryBuilder::prefixTable($table);

        foreach ($this->metaTablesConfigs as $metaTable) {
            if ($metaTable->tableName === $tableName) {
                return $metaTable;
            }
        }

        return new MetaTable(
            $table,
            $this->defaultMetaKeyColumn,
            $this->defaultMetaValueColumn
        );
    }

    /**
     * Select meta columns
     *
     * @param string|RawSQL $table
     * @param string $foreignKey
     * @param string $primaryKey
     * @param array $columns
     *
     * @return $this
     */
    public function attachMeta($table, $foreignKey, $primaryKey, ...$columns)
    {
        $groupConcat = false;
        $metaTable = $this->getMetaTable($table);

        // Check if we have meta columns that dev wants to group concat
        foreach ($columns as $definition) {
            if (is_array($definition)) {
                list (, , $concat) = array_pad($definition, 3, false);
                if ($concat) {
                    $groupConcat = true;
                    // Include foreign key so that dev doesn't have to
                    // he will be confused why he needs this if we don't do it for him, and we also want to prevent errors if sql_mode is only_full_group_by
                    $this->groupBy($foreignKey);
                    break;
                }
            }
        }

        foreach ($columns as $i => $definition) {
            if (is_array($definition)) {
                list ($column, $columnAlias, $concat) = array_pad($definition, 3, false);
            } else {
                $column = $definition;
                $columnAlias = $concat = false;
            }

            // Set dynamic alias
            $tableAlias = sprintf('%s_%s_%d', ($table instanceof RawSQL) ? $table->sql : $table, 'attach_meta', $i);

            if ($concat) {
                $this->selectRaw(
                    "CONCAT('[',GROUP_CONCAT(DISTINCT CONCAT('\"',%1s,'\"')),']') AS %2s",
                    $tableAlias . '.' . $metaTable->valueColumnName,
                    $columnAlias ?: $column
                );
            } else {
                $this->select(["{$tableAlias}.{$metaTable->valueColumnName}", $columnAlias ?: $column]);
            }

            $this->join(
                function (JoinQueryBuilder $builder) use ($table, $foreignKey, $primaryKey, $tableAlias, $column, $metaTable) {
                    $builder
                        ->leftJoin($table, $tableAlias)
                        ->on($foreignKey, "{$tableAlias}.{$primaryKey}")
                        ->andOn("{$tableAlias}.{$metaTable->keyColumnName}", $column, true);
                }
            );

            // If this is non-aggregate column - we have to include it in the GROUP BY statement
            // otherwise the query will fail on servers that have sql_mode flag set to only_full_group_by
            if ($groupConcat && !$concat) {
                $this->groupBy($columnAlias ?: $column);
            }
        }

        return $this;
    }
}
