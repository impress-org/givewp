<?php

declare(strict_types=1);

namespace Give\Framework\Validation\Concerns;

use Give\Framework\Validation\ValidationRulesArray;

trait HasValidationRules
{
    /**
     * @var ValidationRulesArray
     */
    protected $validationRules;

    public function __construct()
    {
        $this->validationRules = give(ValidationRulesArray::class);
    }

    public function rules(...$rules): self
    {
        $this->validationRules->rules(...$rules);

        return $this;
    }

    public function forgetRuleWithId(string $ruleId): self
    {
        $this->validationRules->removeRuleWithId($ruleId);

        return $this;
    }

    public function getValidationRules(): ValidationRulesArray
    {
        return $this->validationRules;
    }
}
