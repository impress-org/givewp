<?php

namespace Give\Framework\FieldsAPI\Concerns;

trait HasLabel
{
    /** @var string */
    protected $label;

    /**
     * @unreleased add types
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @unreleased add types
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
