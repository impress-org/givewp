<?php

namespace Give\Framework\QueryBuilder\Traits;

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
            list ($column, $columnAlias) = $entry;

            $tableAlias      = sprintf('%s_%d', $table, $i);
            $this->joins[]   = [$this->alias($table) . ' ' . $tableAlias, $foreignKey, $primaryKey, 'LEFT'];
            $this->selects[] = [$tableAlias . '.meta_value', $columnAlias];
            $this->wheres[]  = [$tableAlias . '.meta_key', '=', $column, 'AND'];
        }

        return $this;
    }
}
