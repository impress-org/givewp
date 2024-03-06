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
 * @since 3.5.0
 *
 * @template M
 */
class EagerLoader
{
    /**
     * @since 3.5.0
     * @var ReflectionClass<M>
     */
    protected $reflection;

    /**
     * @since 3.5.0
     * @var ModelQueryBuilder
     */
    protected $modelQuery;

    /**
     * @since 3.5.0
     * @var ModelQueryBuilder
     */
    protected $eagerLoadedQuery;

    /**
     * @since 3.5.0
     * @var string
     */
    protected $relationshipKey;

    /**
     * @since 3.5.0
     * @var string
     */
    protected $foreignKey;

    /**
     * @var mixed
     */
    protected $foreignAttribute;

    /**
     * @since 3.5.0
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
     * @since 3.5.0
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
     * @since 3.5.0
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
     * @since 3.5.0
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
     * @since 3.5.0
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
