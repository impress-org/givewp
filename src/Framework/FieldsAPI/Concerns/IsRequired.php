<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait IsRequired
{
    /**
     * Marks the field as required or not.
     *
     * @since 2.24.0 switch to new validation system
     */
    public function required($value = true): self
    {
        if ( $value ) {
            $this->validationRules->rules('required');
        } else {
            $this->validationRules->removeRuleWithId('required');
        }

        return $this;
    }

    /**
     * Returns whether the field is required or not.
     *
     * @since 2.24.0 switch to new validation system
     */
    public function isRequired(): bool
    {
        return $this->hasRule('required');
    }

    /**
     * @deprecated Use the Validator to generate errors
     */
    public function getRequiredError(): array
    {
        return [
            'error_id' => $this->name,
            'error_message' => __('Please enter a value for ' . $this->name, 'give'),
        ];
    }
}
