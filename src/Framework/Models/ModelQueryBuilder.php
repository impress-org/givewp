<?php

namespace Give\Framework\Models;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Subscriptions\Models\Subscription;

/**
 * @unreleased
 */
class ModelQueryBuilder extends QueryBuilder
{
    /**
     * @var string
     */
    protected $model;

    /**
     * Get row
     *
     * @unreleased
     *
     * @return object|Model|Donation|Donor|Subscription|null
     */
    public function get($output = OBJECT)
    {
        $row = DB::get_row($this->getSQL(), OBJECT);

        if (!$row){
            return null;
        }

        if (isset($this->model)) {
            return $this->getRowAsModel($row);
        }

        return $row;
    }

    /**
     * Get results
     *
     * @unreleased
     *
     * @return array|Donation[]|Donor[]|Model[]|Subscription[]|object|null
     */
    public function getAll($output = OBJECT)
    {
        $results = DB::get_results($this->getSQL(), OBJECT);

        if (!$results) {
            return null;
        }

        if (isset($this->model)) {
            return $this->getAllAsModel($results);
        }

        return $results;
    }


    /**
     * Get row as model
     *
     * @unreleased
     *
     * @param  object|null  $row
     *
     * @return Model|Donation|Subscription|Donor|null
     */
    protected function getRowAsModel($row)
    {
        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return $model::fromQueryBuilderObject($row);
    }


    /**
     * Get results as models
     *
     * @unreleased
     *
     * @param  object[]  $results
     *
     * @return Model[]|Donation[]|Subscription[]|Donor[]|null
     */
    protected function getAllAsModel($results)
    {
        /** @var ModelCrud $model */
        $model = $this->model;

        if (!method_exists($model, 'fromQueryBuilderObject')) {
            throw new InvalidArgumentException("fromQueryBuilderObject missing from $model");
        }

        return array_map(static function ($object) use ($model) {
            return $model::fromQueryBuilderObject($object);
        }, $results);
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
}
