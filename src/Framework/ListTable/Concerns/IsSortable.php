<?php

namespace Give\Framework\ListTable\Concerns;
/**
 * @unreleased
 */
trait IsSortable
{
    /**
     * @var bool
     */
    private $sortable = false;

    /**
     * Set is column sortable
     *
     * @unreleased
     */
    public function sortable(bool $sortable): self
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * @unreleased
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }
}
