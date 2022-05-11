<?php

namespace Give\Framework\ListTableAPI\Concerns;

/**
 * @unreleased
 */
trait Label
{
    /**
     * @var string
     */
    protected $label;

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
}
