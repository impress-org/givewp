<?php

namespace Give\Framework\ListTableAPI\Concerns;

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
    protected $filterable = false;

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
    public function applyFilters($value, array $row)
    {
        if ($this->hasFilter()) {
            return call_user_func($this->columnFilter, $value, $row);
        }

        return $value;
    }
}
