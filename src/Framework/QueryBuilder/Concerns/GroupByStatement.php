<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @since 2.19.0
 */
trait GroupByStatement
{

    /**
     * @var string
     */
    protected $groupByColumns = [];

    /**
     * @return $this
     */
    public function groupBy($tableColumn)
    {
        if (!in_array($tableColumn, $this->groupByColumns, true)) {
            $this->groupByColumns[] = DB::prepare('%1s', $tableColumn);
        }

        return $this;
    }

    protected function getGroupBySQL()
    {
        return !empty($this->groupByColumns)
            ? ['GROUP BY ' . implode(',', $this->groupByColumns)]
            : [];
    }
}
