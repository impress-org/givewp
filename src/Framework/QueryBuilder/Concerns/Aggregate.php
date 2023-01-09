<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\QueryBuilder\Clauses\RawSQL;

/**
 * @since 2.19.0
 */
trait Aggregate
{
    /**
     * Returns the number of rows returned by a query
     *
     * @since 2.19.0
     * @param  null|string  $column
     *
     * @return int
     */
    public function count($column = null)
    {
        $column = ( ! $column || $column === '*') ? '1' : trim($column);

        if (empty($this->selects)) {
            $this->selects[] = new RawSQL('SELECT COUNT(%1s) AS count', $column);
        } else {
            $this->selects[] = new RawSQL('COUNT(%1s) AS count', $column);
        }

        return +$this->get()->count;
    }

    /**
     * Returns the total sum in a set of values
     *
     * @since 2.19.0
     * @param  string  $column
     *
     * @return int|float
     */
    public function sum($column)
    {
        $this->selects[] = new RawSQL('SELECT SUM(%1s) AS sum', $column);

        return +$this->get()->sum;
    }


    /**
     * Get the average value in a set of values
     *
     * @since 2.19.0
     * @param  string  $column
     *
     * @return int|float
     */
    public function avg($column)
    {
        $this->selects[] = new RawSQL('SELECT AVG(%1s) AS avg', $column);

        return +$this->get()->avg;
    }

    /**
     * Returns the minimum value in a set of values
     *
     * @since 2.19.0
     * @param  string  $column
     *
     * @return int|float
     */
    public function min($column)
    {
        $this->selects[] = new RawSQL('SELECT MIN(%1s) AS min', $column);

        return +$this->get()->min;
    }

    /**
     * Returns the maximum value in a set of values
     *
     * @since 2.19.0
     * @param  string  $column
     *
     * @return int|float
     */
    public function max($column)
    {
        $this->selects[] = new RawSQL('SELECT MAX(%1s) AS max', $column);

        return +$this->get()->max;
    }
}
