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
    public $column;

    /**
     * @var string
     */
    public $direction;

    /**
     * @param  string  $tableColumn
     * @param  string  $direction
     *
     * @return $this
     */
    public function orderBy($tableColumn, $direction)
    {
        $this->column    = $tableColumn;
        $this->direction = $this->getSortDirection($direction);

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
        return $this->column && $this->direction
            ? ["ORDER BY {$this->column} {$this->direction}"]
            : [];
    }
}
