<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\Join as JoinTable;

trait Meta
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

            $this->join($table, $foreignKey, $primaryKey, 'LEFT', $tableAlias);
            $this->select([$tableAlias . '.meta_value', $columnAlias ? : $column]);
            $this->where($tableAlias . '.meta_key', $column);
        }

        return $this;
    }
}
