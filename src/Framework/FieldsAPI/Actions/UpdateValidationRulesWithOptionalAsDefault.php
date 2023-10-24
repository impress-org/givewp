<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI\Actions;

use Give\Vendors\StellarWP\Validation\Rules\ExcludeIf;
use Give\Vendors\StellarWP\Validation\Rules\ExcludeUnless;
use Give\Vendors\StellarWP\Validation\Rules\Optional;
use Give\Vendors\StellarWP\Validation\ValidationRuleSet;

class UpdateValidationRulesWithOptionalAsDefault
{
    /**
     * This adds the "optional" rule to fields that don't have a "required" rule.
     * This is to ensure that fields that are not required are not validated unless they have a value.
     * Additionally, this ensures that the "optional" rule is placed before the "exclude" rules to preserve the intended pipeline functionality.
     *
     * @since 3.0.0
     */
    public function __invoke(ValidationRuleSet $rules): ValidationRuleSet
    {
        if (!$rules->hasRules() || $rules->hasRule('optional')) {
            return $rules;
        }

        if (!$rules->hasRule('required')) {
            $rules->prependRule('optional');
        }

        $excludeRuleIds = [ExcludeIf::id(), ExcludeUnless::id()];

        foreach ($excludeRuleIds as $excludeRuleId) {
            // If the exclude rule is present, remove it and prepend it to the rules array so that optional comes after.
            if ($rules->hasRule($excludeRuleId) && $rules->getRules()[0] instanceof Optional) {
                $excludeRule = $rules->getRule($excludeRuleId);

                $rules->removeRuleWithId($excludeRuleId);
                $rules->prependRule($excludeRule);
            }
        }

        return $rules;
    }
}