<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
abstract class ListTable implements Arrayable
{
    use Columns;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @unreleased
     *
     * @throws ColumnIdCollisionException
     */
    public function __construct()
    {
        $this->addColumns(...$this->getDefaultColumns());
    }

    /**
     * Get table ID
     *
     * @unreleased
     */
    abstract public function id(): string;

    /**
     * Define table columns
     *
     * @unreleased
     *
     * @return ModelColumn[]
     */
    abstract protected function getDefaultColumns(): array;

    /**
     * Define default visible table columns
     *
     * @unreleased
     *
     * @return string[]
     */
    abstract protected function getDefaultVisibleColumns(): array;

    /**
     * Get table definitions
     *
     * @unreleased
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'columns' => $this->getColumnsArray()
        ];
    }

    /**
     * Set table items
     *
     * @unreleased
     *
     * @param array  $items
     * @param string $locale
     *
     * @return void
     */
    public function items(array $items, string $locale = '')
    {
        $data = [];

        $columns = $this->getColumns();

        foreach ($items as $model) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column::getId()] = $column->getCellValue($model, $locale);
            }

            $data[] = $row;
        }

        $this->items = $data;
    }

    /**
     * @unreleased
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}

