<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Concerns;

use Give\Framework\Validation\Contracts\ValidationRule;
use Give\Framework\Validation\ValidationRuleSet;

/**
 * Apply this trait to a class to enable it to have validation rules. These rules may be passed to the front-end
 * or used with the Validator to validate data.
 *
 * @unreleased
 */
trait HasValidationRules
{
    /**
     * @var ValidationRuleSet
     */
    protected $validationRules;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->validationRules = give(ValidationRuleSet::class);
    }

    /**
     * @unreleased
     */
    public function rules(...$rules): self
    {
        $this->validationRules->rules(...$rules);

        return $this;
    }

    /**
     * @unreleased
     */
    public function hasRule(string $ruleId): bool
    {
        return $this->validationRules->hasRule($ruleId);
    }

    /**
     * @unreleased
     */
    public function getRule(string $ruleId): ValidationRule
    {
        return $this->validationRules->getRule($ruleId);
    }

    /**
     * @unreleased
     */
    public function forgetRule(string $ruleId): self
    {
        $this->validationRules->removeRuleWithId($ruleId);

        return $this;
    }

    /**
     * @unreleased
     */
    public function getValidationRules(): ValidationRuleSet
    {
        return $this->validationRules;
    }
}
