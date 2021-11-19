<?php

namespace Give\Framework\FieldsAPI\Concerns;

use JsonSerializable;

/**
 * @since 2.12.0
 */
class ValidationRules implements JsonSerializable
{

    /** @var array */
    protected $rules;

    /**
     * ValidationRules constructor.
     *
     * @param array $rules
     */
    public function __construct($rules = [])
    {
        $this->rules = $rules;
    }

    /**
     * Set a rule.
     *
     * @since 2.12.0
     *
     * @param string $rule
     * @param mixed  $value
     *
     * @return $this
     */
    public function rule($rule, $value)
    {
        $this->rules[$rule] = $value;

        return $this;
    }

    /**
     * Get a rule.
     *
     * @since 2.12.0
     *
     * @param string $rule
     *
     * @return mixed
     */
    public function getRule($rule)
    {
        return array_key_exists($rule, $this->rules)
            ? $this->rules[$rule]
            : null;
    }

    /**
     * Forget a rule.
     *
     * @since 2.12.0
     *
     * @param string $rule
     *
     * @return $this
     */
    public function forgetRule($rule)
    {
        if (array_key_exists($rule, $this->rules)) {
            unset($this->rules[$rule]);
        }

        return $this;
    }

    /**
     * Get all the rules.
     *
     * @since 2.12.0
     *
     * @return array
     */
    public function all()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}}
     */
    public function jsonSerialize()
    {
        return (object)$this->all();
    }
}
