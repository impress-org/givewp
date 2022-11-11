<?php

namespace Give\Framework\FieldsAPI\Concerns;

use Give\Framework\Validation\Rules\Max;

/**
 * @unreleased update to new validation system
 * @since 2.14.0
 */
trait HasMaxLength
{
    /**
     * Set the value’s maximum length.
     *
     * @unreleased update to use the new validation system
     * @since 2.14.0
     */
    public function maxLength(int $maxLength): self
    {
        if ( $this->validationRules->hasRule('max') ) {
            $this->validationRules->removeRuleWithId('max');
        }

        $this->validationRules->rules("max:$maxLength");

        return $this;
    }

    /**
     * Get the value’s maximum length.
     *
     * @unreleased update to use the new validation system
     * @since 2.14.0
     *
     * @return int|null
     */
    public function getMaxLength()
    {
        $rule = $this->validationRules->getRule('max');

        return $rule instanceof Max ? $rule->getSize() : null;
    }
}
