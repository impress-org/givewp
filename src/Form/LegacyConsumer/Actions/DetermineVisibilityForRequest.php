<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\Framework\FieldsAPI\Field;
use Give\Vendors\StellarWP\FieldConditions\Contracts\Condition;
use Give\Vendors\StellarWP\FieldConditions\FieldCondition;

/**
 * @since 2.27.3 change postData to an array
 * @since 2.21.0
 */
class DetermineVisibilityForRequest
{
    /** @var bool */
    const IS_VISIBLE = true;

    /** @var Field */
    private $field;

    /** @var array */
    protected $postData;

    /**
     * @since 2.27.3 add parameter and return types
     * @since 2.21.0
     */
    public function __construct(Field $field, array $postData)
    {
        $this->field = $field;
        $this->postData = $postData;
    }

    /**
     * @since 2.21.0
     */
    public function __invoke(): bool
    {
        if (!$this->fieldHasVisibilityConditions()) {
            return self::IS_VISIBLE;
        }

        $conditions = $this->field->getVisibilityConditions();
        return array_reduce($conditions, [$this, 'reduceVisibility'], self::IS_VISIBLE);
    }

    /**
     * @since 2.21.0
     * @return bool
     */
    protected function fieldHasVisibilityConditions(): bool
    {
        return method_exists($this->field, 'hasVisibilityConditions')
            && $this->field->hasVisibilityConditions();
    }

    /**
     * @since 2.27.3 update to use FieldConditions
     * @since 2.21.0
     */
    protected function reduceVisibility(bool $visibility, Condition $condition): bool
    {
        $result = $this->compareConditionWithOperator($condition);

        return 'and' === $condition->getLogicalOperator()
            ? $visibility && $result
            : $visibility || $result;
    }

    /**
     * @since 2.27.3 update to use FieldConditions
     * @since 2.21.0
     */
    protected function compareConditionWithOperator(Condition $condition): bool
    {
        if (is_a($condition, FieldCondition::class)) {
            return $condition->passes($this->postData);
        }

        return self::IS_VISIBLE;
    }
}
