<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasType
{

    /**
     * Get the field’s type.
     */
    public function getType(): string
    {
        return static::TYPE;
    }
}
