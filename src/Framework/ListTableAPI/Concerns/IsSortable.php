<?php

namespace Give\Framework\ListTableAPI\Concerns;
/**
 * @unreleased
 */
trait IsSortable
{
    /**
     * @var bool
     */
    protected $sortable = false;

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
}
