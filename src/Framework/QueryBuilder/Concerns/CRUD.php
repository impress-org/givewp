<?php

namespace Give\Framework\QueryBuilder\Concerns;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\Models\Model;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
trait CRUD
{
    /**
     * @var string
     */
    private $model;

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
     * @param  string  $output  ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
     * @return array|Donation[]|Donor[]|Model[]|Subscription[]|object|null
     */
    public function getAll($output = OBJECT)
    {
        if (isset($this->model)) {
            return $this->getAllAsModel();
        }

        return DB::get_results($this->getSQL(), $output);
    }

    /**
     * Get row
     *
     * @unreleased
     *
     * @param  string  $output  ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
     * @return object|Model|Donation|Donor|Subscription|null
     */
    public function get($output = OBJECT)
    {
        if (isset($this->model)) {
            return $this->getRowAsModel();
        }

        return DB::get_row($this->getSQL(), $output);
    }

    /**
     * @unreleased
     *
     * @return string
     */
    private function getTable()
    {
        return $this->froms[0]->table;
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
            $wheres[$where->column] = $where->value;
        }

        return $wheres;
    }

    /**
     * Set the model to be used for returning formatted query
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new InvalidArgumentException("$model must be an instance of " . Model::class);
        }

        $this->model = $model;

        return $this;
    }


    /**
     * Get row as model
     *
     * @unreleased
     *
     * @return Model|Donation|Subscription|Donor|null
     */
    protected function getRowAsModel()
    {
        $row = DB::get_row($this->getSQL(), OBJECT);

        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return $row ? $model::fromQueryBuilderObject($row) : null;
    }

    /**
     * Get results as models
     *
     * @unreleased
     *
     * @return Model[]|Donation[]|Subscription[]|Donor[]|null
     */
    protected function getAllAsModel()
    {
        $results = DB::get_results($this->getSQL(), OBJECT);

        /** @var ModelCrud $model */
        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return $results ? array_map(static function ($object) use ($model) {
            return $model::fromQueryBuilderObject($object);
        }, $results) : null;
    }
}
