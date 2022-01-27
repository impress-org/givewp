<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
trait OrderByStatement
{

    /**
     * @var string
     */
    protected $orderByColumn;

    /**
     * @var string
     */
    protected $orderByDirection;

    /**
     * @param  string  $tableColumn
     * @param  string  $direction
     *
     * @return $this
     */
    public function orderBy($tableColumn, $direction)
    {
        $this->orderByColumn    = $tableColumn;
        $this->orderByDirection = $this->getSortDirection($direction);

        return $this;
    }

    /**
     * @param  string  $direction
     *
     * @return string
     */
    protected function getSortDirection($direction)
    {
        $direction = strtoupper($direction);

        if (in_array($direction, ['ASC', 'DESC'])) {
            return $direction;
        }

        return 'ASC';
    }

    protected function getOrderBySQL()
    {
        return $this->orderByColumn && $this->orderByDirection
            ? [
                DB::prepare('ORDER BY %1s %2s', $this->orderByColumn, $this->orderByDirection)
            ]
            : [];
    }
}
