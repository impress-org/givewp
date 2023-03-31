<?php

namespace Give\Form\LegacyConsumer\Actions;

use Give\Framework\FieldsAPI\Field;
use Give\ValueObjects\Money;
use Give\Vendors\StellarWP\FieldConditions\Contracts\Condition;
use Give\Vendors\StellarWP\FieldConditions\FieldCondition;

/**
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
     * @unreleased add parameter and return types
     * @since 2.21.0
     */
    public function __construct(Field $field, array $postData)
    {
        $this->field = $field;
        $this->postData = $postData;

        // Check to see if this is necessary
        // We may also need to normalize the comparison value
//        if ( isset($this->postData['give-amount']) ) {
//            $this->postData['give-amount'] = $this->normalizeMinorAmount($this->postData['give-amount']);
//        }
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
     * @unreleased update to use FieldConditions
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
     * @unreleased update to use FieldConditions
     * @since 2.21.0
     */
    protected function compareConditionWithOperator(Condition $condition): bool
    {
        if (is_a($condition, FieldCondition::class)) {
            return $condition->passes($this->postData);
        }

        return self::IS_VISIBLE;
    }

    /**
     * @unreleased add parameter and return types
     * @since 2.21.0
     */
    protected function normalizeMinorAmount(string $amount): int
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
