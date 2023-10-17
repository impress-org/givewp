<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Vendors\StellarWP\FieldConditions\Contracts\Condition;
use Give\Vendors\StellarWP\FieldConditions\FieldCondition;
use Give\Vendors\StellarWP\FieldConditions\SimpleConditionSet;
use Give\Vendors\StellarWP\Validation\Concerns\HasValidationRules;
use Give\Vendors\StellarWP\Validation\Rules\ExcludeUnless;

/**
 * @since 2.13.0
 *
 * @mixin HasValidationRules
 */
trait HasVisibilityConditions
{
    /**
     * The node is visible by default. These are the conditions for it to remain visible.
     *
     * @since 2.27.3 update to use SimpleConditionSet
     * @since 2.13.0
     *
     * @var SimpleConditionSet
     */
    protected $visibilityConditions;

    /**
     * @since 2.27.3
     */
    public function __construct()
    {
        $this->visibilityConditions = new SimpleConditionSet();
    }

    /**
     * Get the visibility conditions.
     *
     * @since 2.27.3 update to use SimpleConditionSet
     * @since 2.13.0
     *
     * @return Condition[]
     */
    public function getVisibilityConditions(): array
    {
        return $this->visibilityConditions->getConditions();
    }

    /**
     * @since 2.27.3 replace with native condition set method
     * @since 2.16.0
     */
    public function hasVisibilityConditions(): bool
    {
        return $this->visibilityConditions->hasConditions();
    }

    /**
     * @since 2.27.3
     */
    public function passesVisibilityConditions(array $values): bool
    {
        return $this->visibilityConditions->passes($values);
    }

    /**
     * @since 2.27.3
     */
    public function failsVisibilityConditions(array $values): bool
    {
        return $this->visibilityConditions->fails($values);
    }

    /**
     * Set a condition for showing the node.
     *
     * @since 3.0.0 Fixed a bug with encoded HTML entities in the operator, ie > (greater than), < (less than)
     * @since 2.27.3 update to use SimpleConditionSet
     * @since 2.13.0
     */
    public function showIf(string $field, string $operator, $value, string $boolean = 'and'): self
    {
        // Fixes a bug with encoded HTML entities in the operator, ie > (greater than), < (less than)
        $operator = html_entity_decode($operator);

        if ($boolean === 'and') {
            $this->visibilityConditions->and($field, $operator, $value);
        } else {
            $this->visibilityConditions->or($field, $operator, $value);
        }

        $this->updateValidationRules();

        return $this;
    }

    /**
     * Add an "or" visibility condition, useful when chained for readability.
     *
     * @since 2.27.3
     */
    public function orShowIf(string $field, string $operator, $value): self
    {
        $this->visibilityConditions->or($field, $operator, $value);

        $this->updateValidationRules();

        return $this;
    }

    /**
     * Add an "and" visibility condition, useful when chained for readability.
     *
     * @since 2.27.3
     */
    public function andShowIf(string $field, string $operator, $value): self
    {
        $this->visibilityConditions->and($field, $operator, $value);

        $this->updateValidationRules();

        return $this;
    }

    /**
     * Set multiple conditions for showing the node.
     *
     * @since 2.27.3 update to use FieldCondition
     * @since 2.13.0
     *
     * @param FieldCondition|array ...$conditions
     */
    public function showWhen(...$conditions): self
    {
        foreach ($conditions as $condition) {
            $this->visibilityConditions->append($this->normalizeCondition($condition));
        }

        $this->updateValidationRules();

        return $this;
    }

    /**
     * Updates the validation rules to include the visibility conditions. This prevents the node from being validated
     * when the conditions are not met.
     *
     * This also only adds the validation rule if the node has validation rules.
     *
     * @since 2.27.3
     */
    protected function updateValidationRules()
    {
        if (method_exists($this, 'replaceOrPrependRule')) {
            $this->replaceOrPrependRule(ExcludeUnless::id(), new ExcludeUnless($this->visibilityConditions));
        }
    }

    /**
     * Normalize the condition if in array format.
     *
     * @since 2.27.3 update to use FieldCondition
     * @since 2.13.0
     *
     * @param FieldCondition|array $condition
     *
     * @throws InvalidArgumentException
     */
    protected function normalizeCondition($condition): FieldCondition
    {
        if ($condition instanceof FieldCondition) {
            return $condition;
        }

        if (is_array($condition)) {
            return new FieldCondition(...$condition);
        }

        throw new InvalidArgumentException('Parameter $condition must be a FieldCondition or an array.');
    }
}
