<?php

declare(strict_types=1);

namespace Give\Framework\FieldsAPI\Actions;

use Give\Framework\FieldsAPI\Form;
use Give\Framework\Validation\Validator;

class CreateValidatorFromForm
{
    /**
     * @unreleased
     */
    public function __invoke(Form $form, array $values): Validator
    {
        $labels = [];
        $rules = [];

        foreach ($form->getFields() as $field) {
            $rules[$field->getName()] = $field->getValidationRules();

            if (method_exists($field, 'getLabel')) {
                $labels[$field->getName()] = $field->getLabel();
            }
        }

        return new Validator($rules, $values, $labels);
    }
}
