<?php

namespace Give\Framework\ListTable\Concerns;

/**
 * @since 2.24.0
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
     * @since 2.24.0
     */
    public function filterable(bool $filterable): self
    {
        $this->filterable = $filterable;
        return $this;
    }

    /**
     * Add column filter
     *
     * @since 2.24.0
     */
    public function filter(callable $filter): self
    {
        $this->columnFilter = $filter;
        return $this;
    }

    /**
     * @since 2.24.0
     */
    public function hasFilter(): bool
    {
        return is_callable($this->columnFilter);
    }

    /**
     * @since 2.24.0
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
     * @since 2.24.0
     */
    public function isFilterable(): bool
    {
        return $this->filterable;
    }
}
