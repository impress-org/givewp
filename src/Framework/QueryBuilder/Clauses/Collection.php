<?php

namespace Give\Framework\QueryBuilder\Clauses;

/**
 * @unreleased
 */
class Collection
{
    protected $columns = [];
    protected $data = [];

    /*
     * Set data
     *
     * @unreleased
     */
    public function set($data): Collection
    {
        // Set column names
        if (empty($this->columns)) {
            $this->columns = array_keys($data);
        }

        $this->data[] = array_values($data);

        return $this;
    }

    /*
     * @unreleased
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /*
     * @unreleased
     */
    public function getData(): array
    {
        return $this->data;
    }
}
