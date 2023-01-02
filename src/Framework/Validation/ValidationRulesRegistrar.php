<?php

declare(strict_types=1);

namespace Give\Framework\Validation;

use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Validation\Contracts\ValidationRule;

/**
 * @unreleased
 */
class ValidationRulesRegistrar
{
    /** @var array */
    protected $rules = [];

    /**
     * Register one or many validation rules.
     *
     * @unreleased
     */
    public function register(string ...$rules): self
    {
        foreach ($rules as $rule) {
            $this->registerClass($rule);
        }

        return $this;
    }

    /**
     * Register a validation rule.
     *
     * @unreleased
     */
    private function registerClass(string $class): self
    {
        if (!is_subclass_of($class, ValidationRule::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Validation rule must implement %s',
                    ValidationRule::class
                )
            );
        }

        if (isset($this->rules[$class::id()])) {
            throw new InvalidArgumentException(
                "A validation rule with the id {$class::id()} has already been registered."
            );
        }

        $this->rules[$class::id()] = $class;

        return $this;
    }

    /**
     * Get a validation rule.
     *
     * @since 2.12.0
     *
     * @return string|null
     */
    public function getRule(string $ruleId)
    {
        return $this->rules[$ruleId] ?? null;
    }
}
