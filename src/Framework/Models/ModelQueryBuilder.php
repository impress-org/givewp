<?php

namespace Give\Framework\Models;

use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Contracts\ModelCrud;
use Give\Framework\QueryBuilder\Clauses\RawSQL;
use Give\Framework\QueryBuilder\QueryBuilder;

/**
 * @since 2.19.6
 *
 * @template M
 */
class ModelQueryBuilder extends QueryBuilder
{
    /**
     * @var class-string<M>
     */
    protected $model;

    /**
     * @param class-string<M> $modelClass
     */
    public function __construct($modelClass)
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("$modelClass must be an instance of " . Model::class);
        }

        $this->model = $modelClass;
    }

    /**
     * Returns the number of rows returned by a query
     *
     * @since 2.24.0
     *
     * @param  null|string  $column
     */
    public function count($column = null): int
    {
        $column = ( ! $column || $column === '*') ? '1' : trim($column);

        if ('1' === $column) {
            $this->selects = [];
        }
        $this->selects[] = new RawSQL('SELECT COUNT(%1s) AS count', $column);

        return +parent::get()->count;
    }

    /**
     * Get row
     *
     * @since 2.19.6
     *
     * @return M|null
     */
    public function get($output = OBJECT)
    {
        $row = DB::get_row($this->getSQL(), OBJECT);

        if (!$row) {
            return null;
        }

        return $this->getRowAsModel($row);
    }

    /**
     * Get results
     *
     * @since 2.19.6
     *
     * @return M[]|null
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
     * @since 2.19.6
     *
     * @param object|null $row
     *
     * @return M|null
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
     * @since 2.19.6
     *
     * @param object[] $results
     *
     * @return M[]|null
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
}
