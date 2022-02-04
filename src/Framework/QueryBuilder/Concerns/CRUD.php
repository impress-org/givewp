<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @unreleased
 */
trait CRUD
{
    /**
     * @unreleased
     *
     * @param  array  $data
     * @param  array|string  $format
     *
     * @return false|int
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/insert/
     *
     */
    public function insert($data, $format = null)
    {
        return DB::insert(
            $this->getTable(),
            $data,
            $format
        );
    }

    /**
     * @unreleased
     *
     * @param  array  $data
     * @param  null  $format
     *
     * @return false|int
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/update/
     *
     */
    public function update($data, $format = null)
    {
        return DB::update(
            $this->getTable(),
            $data,
            $this->getWhere(),
            $format,
            null
        );
    }

    /**
     * @unreleased
     *
     * @return false|int
     *
     * @see https://developer.wordpress.org/reference/classes/wpdb/delete/
     */
    public function delete()
    {
        return DB::delete(
            $this->getTable(),
            $this->getWhere(),
            null
        );
    }

    /**
     * Get results
     *
     * @unreleased
     *
     * @param  string ARRAY_A|ARRAY_N|OBJECT|OBJECT_K $output
     *
     * @return array|object|null
     */
    public function getAll($output = OBJECT)
    {
        return DB::get_results($this->getSQL(), $output);
    }

    /**
     * Get row
     *
     * @unreleased
     *
     * @param  string ARRAY_A|ARRAY_N|OBJECT|OBJECT_K $output
     *
     * @return array|object|null
     */
    public function get($output = OBJECT)
    {
        return DB::get_row($this->getSQL(), $output);
    }

    /**
     * @unreleased
     *
     * @return string
     */
    private function getTable()
    {
        return $this->froms[ 0 ]->table;
    }

    /**
     * @unreleased
     *
     * @return array[]
     */
    private function getWhere()
    {
        $wheres = [];

        foreach ($this->wheres as $where) {
            $wheres[ $where->column ] = $where->value;
        }

        return $wheres;
    }
}
