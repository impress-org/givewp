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
     * Sort table items by column
     *
     * @unreleased
     */
    public function sortItems(string $column, string $direction = 'ASC')
    {
        $sortColumn = array_column($this->items, $column);
        $sortDirection = ('DESC' === strtoupper($direction)) ? SORT_DESC : SORT_ASC;

        array_multisort($sortColumn, $sortDirection, $this->items);
    }

    /**
     * @unreleased
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
