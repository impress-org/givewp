<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
 */
trait Label
{
    /**
     * @var string
     */
    private $label;

    /**
     * Set column position
     *
     * @unreleased
     */
    public function label(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
