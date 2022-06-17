<?php

namespace Give\Form\LegacyConsumer\Actions;

use ArrayObject;
use Give\Framework\FieldsAPI\Conditions\BasicCondition;
use Give\Framework\FieldsAPI\Conditions\Condition;
use Give\Framework\FieldsAPI\Field;
use Give\ValueObjects\Money;

/**
 * @since 2.21.0
 */
class DetermineVisibilityForRequest
{
    /** @var bool */
    const IS_VISIBLE = true;

    /** @var Field */
    private $field;

    /** @var ArrayObject */
    protected $postData;

    /**
     * @since 2.21.0
     * @param Field $field
     * @param array $postData
     */
    public function __construct(Field $field, array $postData)
    {
        $this->field = $field;
        $this->postData = new ArrayObject($postData);
    }

    /**
     * @since 2.21.0
     */
    public function __invoke()
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
    protected function fieldHasVisibilityConditions()
    {
        return method_exists($this->field, 'hasVisibilityConditions')
            && $this->field->hasVisibilityConditions();
    }

    /**
     * @since 2.21.0
     * @param bool $visibility
     * @param Condition $condition
     * @return bool
     */
    protected function reduceVisibility($visibility, Condition $condition)
    {
        $result = $this->compareConditionWithOperator($condition);

        return 'and' === $condition->boolean
            ? $visibility && $result
            : $visibility || $result;
    }

    /**
     * @since 2.21.0
     * @param Condition $condition
     * @return bool
     */
    protected function compareConditionWithOperator(Condition $condition)
    {
        if (is_a($condition, BasicCondition::class)) {
            return $this->compareBasicConditionWithOperator($condition);
        }

        // @TODO Implement nested conditions.
        return self::IS_VISIBLE;
    }

    /**
     * @since 2.21.0
     * @param BasicCondition $condition
     * @return bool
     */
    protected function compareBasicConditionWithOperator(BasicCondition $condition)
    {
        $conditionValue = $condition->value;
        $comparisonValue = $this->postData[$condition->field];

        if ('give-amount' === $condition->field) {
            $conditionValue = $this->normalizeMinorAmount($conditionValue);
            $comparisonValue = $this->normalizeMinorAmount($comparisonValue);
        }

        switch ($condition->operator) {
            case '=':
                return $comparisonValue === $conditionValue;
            case '!=':
                return $comparisonValue !== $conditionValue;
            case '>':
                return $comparisonValue > $conditionValue;
            case '>=':
                return $comparisonValue >= $conditionValue;
            case '<':
                return $comparisonValue < $conditionValue;
            case '<=':
                return $comparisonValue <= $conditionValue;
            default:
                return false;
        }
    }

    /**
     * @since 2.21.0
     * @param $amount
     * @return int
     */
    protected function normalizeMinorAmount($amount)
    {
        $currency = give_get_currency($this->postData['give-form-id']);
        $settings = give_get_currencies('all')[$currency]['setting'];

        $amount = str_replace(
            [$settings['thousands_separator'], $settings['decimal_separator']],
            ['', '.'],
            $amount
        );

        return Money::of($amount, $currency)->getMinorAmount();
    }
}
