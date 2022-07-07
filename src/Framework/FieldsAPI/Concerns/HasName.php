<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasName
{

    /** @var string */
    protected $name;

    /**
     * Get the field’s name.
     */
    public function getName(): string
    {
        return $this->name;
    }
}
