<?php

namespace Give\Framework\Models;

use ReflectionClass;

/**
 * @unreleased
 */
class EagerLoader
{
    /**
     * @unreleased
     * @var string
     */
    protected $modelClass;

    /**
     * @unreleased
     * @var string
     */
    protected $relationshipKey;

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
    protected $foreignKey;

    /**
     * @var mixed
     */
    protected $foreignAttribute;

    public function __construct($modelClass, $relationshipKey, $eagerLoadedModelClass, $foreignKey, $foreignAttribute = null)
    {
        $this->modelClass = $modelClass;
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
     * @unreleased
     *
     * This method wraps the `get()` method of the underlying ModelQueryBuilder.
     * It uses the results to query the related models and pre-set the cachedRelations property.
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
     * @unreleased
     *
     * This method wraps the `getAll()` method of the underlying ModelQueryBuilder.
     * It uses the results to query the related models and pre-set the cachedRelations property.
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
     * @unreleased
     *
     * The cachedRelations property is protected and cannot be accessed directly.
     * This method uses reflection to set the cachedRelations property on the model.
     */
    protected function setEagerLoadedModels($model, $eagerLoadedModels): void
    {
        $modelClassReflection = new ReflectionClass($this->modelClass);
        $cachedRelationsReflection = $modelClassReflection
            ->getParentClass()
            ->getProperty('cachedRelations');
        $cachedRelationsReflection->setAccessible(true);

        $cachedRelations = $cachedRelationsReflection->getValue($model);
        $cachedRelations[$this->relationshipKey] = $eagerLoadedModels;

        $cachedRelationsReflection->setValue($model, $cachedRelations);
    }
}
