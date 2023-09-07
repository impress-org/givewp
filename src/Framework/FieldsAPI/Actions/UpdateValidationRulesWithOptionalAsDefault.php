<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI\Actions;

use Give\Vendors\StellarWP\Validation\Rules\ExcludeIf;
use Give\Vendors\StellarWP\Validation\Rules\ExcludeUnless;
use Give\Vendors\StellarWP\Validation\ValidationRuleSet;

class UpdateValidationRulesWithOptionalAsDefault
{
    /**
     * This adds the "optional" rule to fields that don't have a "required" rule.
     * This is to ensure that fields that are not required are not validated unless they have a value.
     * Additionally, this ensures that the "optional" rule is placed before the "exclude" rules to preserve the intended pipeline functionality.
     *
     * @unreleased
     */
    public function __invoke(ValidationRuleSet $rules): ValidationRuleSet
    {
        if (!$rules->hasRules()) {
            return $rules;
        }

        if (!$rules->hasRule('required')) {
            $rules->prependRule('optional');
        }

        $excludeRuleIds = [ExcludeIf::id(), ExcludeUnless::id()];

        foreach ($excludeRuleIds as $excludeRuleId) {
            if ($rules->hasRule($excludeRuleId) && $rules->hasRule('optional')) {
                $excludeRule = $rules->getRule($excludeRuleId);
                $rules->removeRuleWithId($excludeRuleId);
                $rules->prependRule($excludeRule);
            }
        }

        return $rules;
    }
}