<?php

namespace Give\Framework\QueryBuilder\Concerns;

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
    public function getSortDirection($direction)
    {
        $direction = strtoupper($direction);

        if (in_array($direction, ['ASC', 'DESC'])) {
            return $direction;
        }

        return 'ASC';
    }

    public function getOrderBySQL()
    {
        return $this->orderByColumn && $this->orderByDirection
            ? ["ORDER BY {$this->orderByColumn} {$this->orderByDirection}"]
            : [];
    }
}
