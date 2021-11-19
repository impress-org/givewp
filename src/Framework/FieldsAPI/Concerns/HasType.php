<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasType
{

    /**
     * Get the field’s type.
     *
     * @return string
     */
    public function getType()
    {
        return static::TYPE;
    }
}
