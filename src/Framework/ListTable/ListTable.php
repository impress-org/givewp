<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 2.24.0
 */
abstract class ListTable implements Arrayable
{
    use Columns;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @since 2.24.0
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
     * @since 2.24.0
     */
    abstract public function id(): string;

    /**
     * Define table columns
     *
     * @since 2.24.0
     *
     * @return ModelColumn[]
     */
    abstract protected function getDefaultColumns(): array;

    /**
     * Define default visible table columns
     *
     * @since 2.24.0
     *
     * @return string[]
     */
    abstract protected function getDefaultVisibleColumns(): array;

    /**
     * Get table definitions
     *
     * @since 2.24.0
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
     * @since 2.24.0
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
     * @since 2.24.0
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }
}

