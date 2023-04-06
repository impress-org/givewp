<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasLabel
{
    /** @var string */
    protected $label;

    /**
     * @since 2.24.0 add types
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @since 2.24.0 add types
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label;
    }
}
