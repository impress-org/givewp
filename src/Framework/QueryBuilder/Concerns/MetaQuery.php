<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\QueryBuilder\Types\JoinType;

/**
 * @unreleased
 */
trait MetaQuery
{
    /**
     * Select meta columns
     *
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     * @param  array  $columns
     *
     * @return $this
     */
    public function attachMeta($table, $foreignKey, $primaryKey, ...$columns)
    {
        foreach ($columns as $i => $entry) {
            if (is_array($entry)) {
                list ($column, $columnAlias) = $entry;
            } else {
                $column      = $entry;
                $columnAlias = null;
            }

            // Set dynamic alias
            $tableAlias = sprintf('%s_%s_%d', $table, 'attach_meta', $i);

            $this->join(
                $table,
                JoinType::LEFT,
                function (QueryBuilder $builder) use ($foreignKey, $primaryKey, $tableAlias, $column) {
                    $builder
                        ->joinOn($foreignKey, '=', "{$tableAlias}.{$primaryKey}")
                        ->joinAnd("{$tableAlias}.meta_key", '=', $column, true);
                },
                $tableAlias
            );

            $this->select([$tableAlias . '.meta_value', $columnAlias ? : $column]);
        }

        return $this;
    }
}
