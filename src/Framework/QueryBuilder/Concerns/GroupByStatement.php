<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @unreleased
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
        $this->groupByColumns[] = DB::prepare( '%1s', $tableColumn);

        return $this;
    }

    protected function getGroupBySQL()
    {
        return ! empty($this->groupByColumns)
            ? ['GROUP BY ' . implode(',', $this->groupByColumns)]
            : [];
    }
}
