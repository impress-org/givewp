<?php

namespace Give\Framework\Models;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use ReflectionClass;

/**
 * Eager load model relationships for more performant queries.
 *
 * As opposed to "lazy" loading, which queries the database for a relationship when it is accessed,
 * "eager" loading queries the database for the relationship of all queried models, with a single query.
 * This prevents a "N+1" problem, where a query is executed for each model, but using query optimization.
 *
 * @unreleased
 *
 * @template M
 */
class EagerLoader
{
    /**
     * @unreleased
     * @var ReflectionClass<M>
     */
    protected $reflection;

    /**
     * @unreleased
     * @var ModelQueryBuilder
     */
    protected $modelQuery;

    /**
     * @unreleased
     * @var ModelQueryBuilder
     */
    protected $eagerLoadedQuery;

    /**
     * @unreleased
     * @var string
     */
    protected $relationshipKey;

    /**
     * @unreleased
     * @var string
     */
    protected $foreignKey;

    /**
     * @var mixed
     */
    protected $foreignAttribute;

    /**
     * @unreleased
     *
     * @param class-string<M> $modelClass
     * @param class-string<M> $eagerLoadedModelClass
     * @param string $relationshipKey
     * @param string $foreignKey
     * @param string|null $foreignAttribute
     */
    public function __construct(string $modelClass, string $eagerLoadedModelClass, string $relationshipKey, string $foreignKey, string $foreignAttribute = null)
    {
        if (!is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException("$modelClass must be an instance of " . Model::class);
        }

        if (!is_subclass_of($eagerLoadedModelClass, Model::class)) {
            throw new InvalidArgumentException("$eagerLoadedModelClass must be an instance of " . Model::class);
        }

        $this->reflection = new ReflectionClass($modelClass);
        $this->modelQuery = $modelClass::query();
        $this->relationshipKey = $relationshipKey;
        $this->eagerLoadedQuery = $eagerLoadedModelClass::query();
        $this->foreignKey = $foreignKey;
        $this->foreignAttribute = $foreignAttribute ?? $foreignKey;
    }

    /**
     * @unreleased
     */
    public function __call($name, $arguments)
    {
        $this->modelQuery->$name(...$arguments);
        return $this;
    }

    /**
     * This method wraps the `get()` method of the underlying ModelQueryBuilder.
     * It uses the results to query the related models and pre-set the cachedRelations property.
     *
     * @unreleased
     *
     * @return M|null
     */
    public function get()
    {
        $model = $this->modelQuery->get();

        $eagerLoadedModels = $this->eagerLoadedQuery
            ->where($this->foreignKey, $model->id)
            ->getAll();

        $this->setEagerLoadedModels($model, $eagerLoadedModels);

        return $model;
    }

    /**
     * This method wraps the `getAll()` method of the underlying ModelQueryBuilder.
     * It uses the results to query the related models and pre-set the cachedRelations property.
     *
     * @unreleased
     *
     * @return M[]|null
     */
    public function getAll()
    {
        $models = $this->modelQuery->getAll();

        $eagerLoadedModels = $this->eagerLoadedQuery
            ->whereIn($this->foreignKey, array_column($models, 'id'))
            ->getAll();

        foreach($models as $model) {
            $this->setEagerLoadedModels($model, array_filter($eagerLoadedModels, function($eagerLoadedModel) use ($model) {
                return $eagerLoadedModel->{$this->foreignAttribute} === $model->id;
            }));
        }

        return $models;
    }

    /**
     * The cachedRelations property is protected and cannot be accessed directly.
     * This method uses reflection to set the cachedRelations property on the model.
     *
     * @unreleased
     *
     * @param Model $model
     * @param array $eagerLoadedModels
     */
    protected function setEagerLoadedModels(Model $model, array $eagerLoadedModels): void
    {
        $property = $this->reflection
            ->getParentClass()
            ->getProperty('cachedRelations');
        $property->setAccessible(true);

        $cachedRelations = $property->getValue($model);
        $cachedRelations[$this->relationshipKey] = $eagerLoadedModels;

        $property->setValue($model, $cachedRelations);
    }
}
