<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
 */
trait IsVisible
{
    /**
     * @var bool
     */
    private $visible = true;

    /**
     * Set is column visible on frontend
     *
     * @unreleased
     */
    public function visible(bool $visible): self
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @unreleased
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }
}
