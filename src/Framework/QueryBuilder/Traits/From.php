<?php

namespace Give\Framework\QueryBuilder\Traits;

use Give\Framework\QueryBuilder\From as FromTable;

trait From
{

    /**
     * @var FromTable
     */
    public $from;

    /**
     * @param  string  $table
     *
     * @return $this
     */
    public function from($table, $alias = null)
    {
        $this->from = new FromTable($table, $alias);

        return $this;
    }

    public function getFromSQL()
    {
        if ($this->from->alias) {
            return ["FROM {$this->from->table} AS {$this->from->alias}"];
        }

        return ["FROM {$this->from}"];
    }
}
