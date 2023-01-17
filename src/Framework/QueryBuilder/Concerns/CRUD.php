<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Framework\Database\DB;

/**
 * @since 2.19.0
 */
trait CRUD
{
    /**
     * @see https://developer.wordpress.org/reference/classes/wpdb/insert/
     *
     * @since 2.19.0
     *
     * @param  array|string  $format
     *
     * @param  array  $data
     * @return false|int
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
     * @see https://developer.wordpress.org/reference/classes/wpdb/update/
     *
     * @since 2.19.0
     *
     * @param  null  $format
     *
     * @param  array  $data
     * @return false|int
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
     * @since 2.19.0
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
     * @since 2.19.0
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
     * @since 2.19.0
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
     * Get a single column's value from the first result of a query.
     *
     * @since 2.24.0
     *
     * @param string $column
     *
     * @return mixed
     */
    public function value(string $column)
    {
        $result = (array) $this->select($column)->get();
        return count($result) > 0 ? $result[$column] : null;
    }

    /**
     * @since 2.19.0
     *
     * @return string
     */
    private function getTable()
    {
        return $this->froms[0]->table;
    }

    /**
     * @since 2.19.0
     *
     * @return array[]
     */
    private function getWhere()
    {
        $wheres = [];

        foreach ($this->wheres as $where) {
            $wheres[$where->column] = $where->value;
        }

        return $wheres;
    }
}
