<?php

namespace Give\Framework\ListTableAPI\Concerns;

/**
 * @unreleased
 */
trait Items
{
    /**
     * @var array
     */
    private $items = [];

    /**
     * Set table items
     *
     * @unreleased
     */
    public function items(array $items)
    {
        $this->items = $items;
    }

    /**
     * @unreleased
     */
    public function getItems(): array
    {
        $items = [];

        foreach ($this->items as $row) {
            foreach ($row as $name => $value) {
                if ($column = $this->getColumnById($name)) {
                    $row[$name] = $column->applyFilters($value, $row);
                }
            }

            $items[] = $row;
        }

        return $items;
    }
}
