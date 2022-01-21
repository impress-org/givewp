<?php

namespace Give\Framework\QueryBuilder\Traits;

trait Meta
{
    /**
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $primaryKey
     *
     * @return $this
     */
    public function joinMeta($table, $foreignKey, $primaryKey, $columns = [])
    {
        $this->joins[] = [$this->alias($table), $foreignKey, $primaryKey, 'LEFT'];

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
