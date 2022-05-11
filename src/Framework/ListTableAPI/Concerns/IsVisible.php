<?php

namespace Give\Framework\ListTableAPI\Concerns;

/**
 * @unreleased
 */
trait IsVisible
{
    /**
     * @var bool
     */
    protected $visible = true;

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
}
