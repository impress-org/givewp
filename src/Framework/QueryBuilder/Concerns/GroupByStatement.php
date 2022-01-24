<?php

namespace Give\Framework\QueryBuilder\Concerns;

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
        $this->groupByColumns[] = trim($tableColumn);

        return $this;
    }

    public function getGroupBySQL()
    {
        return ! empty($this->groupByColumns)
            ? ['GROUP BY ' . implode(',', $this->groupByColumns)]
            : [];
    }
}
