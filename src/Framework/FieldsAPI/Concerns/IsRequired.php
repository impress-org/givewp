<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait IsRequired
{

    /**
     * {@inheritdoc}
     */
    public function required($value = true)
    {
        $this->validationRules->rule('required', $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        $required = $this->validationRules->getRule('required');

        return is_null($required) ? false : $required;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredError()
    {
        return [
            'error_id' => $this->name,
            'error_message' => __('Please enter a value for ' . $this->name, 'give'),
        ];
    }
}
