<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI\Actions;

use Give\Framework\FieldsAPI\Field;
use Give\Vendors\StellarWP\Validation\Validator;

class CreateValidatorFromFormFields
{
    /**
     * @since 3.0.0
     *
     * @param  Field[]  $formFields
     * @param  array  $values
     *
     * @return Validator
     */
    public function __invoke(array $formFields, array $values): Validator
    {
        $labels = [];
        $rules = [];

        foreach ($formFields as $field) {
            $rules[$field->getName()] = (new UpdateValidationRulesWithOptionalAsDefault())(
                $field->getValidationRules()
            );

            if (method_exists($field, 'getLabel')) {
                $labels[$field->getName()] = $field->getLabel();
            }
        }

        return new Validator($rules, $values, $labels);
    }
}
