<?php

namespace Give\Framework\ListTable\Concerns;

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
        $data = [];

        foreach ($items as $row) {
            foreach ($row as $name => $value) {
                if ($column = $this->getColumnByName($name)) {
                    $row[$name] = $column->applyFilter($value, $row);
                }
            }

            $data[] = $row;
        }

        $this->items = $data;
    }

    /**
     * @unreleased
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
