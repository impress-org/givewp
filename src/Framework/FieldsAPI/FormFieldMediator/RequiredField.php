<?php

namespace Give\Framework\FieldsAPI\FieldMediator;

use Give\Framework\FieldsAPI\Field;

class RequiredField implements Contract
{
    public function __invoke(Field $field)
    {
        add_filter(
            'give_donation_form_required_fields',
            $this->getClosure()->bindTo($field),
            10,
            2
        );
    }

    public function getClosure()
    {
        return function ($requiredFields, $formID) {
            if ($this->isRequired()) {
                $requiredFields[$this->getName()] = $this->getRequiredError();
            }

            return $requiredFields;
        };
    }
}
