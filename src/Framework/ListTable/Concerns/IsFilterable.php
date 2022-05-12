<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @unreleased
 */
trait IsFilterable
{
    /**
     * @var callable|null
     */
    private $columnFilter;

    /**
     * @var bool
     */
    private $filterable = false;

    /**
     * Set is column filterable from frontend
     *
     * @unreleased
     */
    public function filterable(bool $filterable): self
    {
        $this->filterable = $filterable;
        return $this;
    }

    /**
     * Add column filter
     *
     * @unreleased
     */
    public function filter(callable $filter): self
    {
        $this->columnFilter = $filter;
        return $this;
    }

    /**
     * @unreleased
     */
    public function hasFilter(): bool
    {
        return is_callable($this->columnFilter);
    }

    /**
     * @unreleased
     * @return mixed
     */
    public function applyFilter($value, array $row)
    {
        if ($this->hasFilter()) {
            return call_user_func($this->columnFilter, $value, $row);
        }

        return $value;
    }

    /**
     * @unreleased
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }
}
