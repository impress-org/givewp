<?php

namespace Give\Framework\FieldsAPI\FieldMediator;

use Give\Framework\FieldsAPI\Field;

interface Contract
{
    public function __invoke(Field $field);
}
