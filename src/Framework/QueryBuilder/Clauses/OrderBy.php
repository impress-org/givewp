<?php

namespace Give\Framework\QueryBuilder\Clauses;

use InvalidArgumentException;

/**
 * @since 2.19.0
 */
class OrderBy
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
     * @param $column
     * @param $direction
     */
    public function __construct($column, $direction)
    {
        $this->column    = trim($column);
        $this->direction = $this->getSortDirection($direction);
    }

    /**
     * @param  string  $direction
     *
     * @return string
     */
    private function getSortDirection($direction)
    {
        $direction  = strtoupper($direction);
        $directions = ['ASC', 'DESC'];

        if ( ! in_array($direction, $directions, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported sort direction %s. Please use one of the (%s)',
                    $direction,
                    implode(',', $directions)
                )
            );
        }

        return $direction;
    }
}
