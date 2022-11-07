<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
abstract class ListTable implements Arrayable
{
    use Columns;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @unreleased
     * @throws Exceptions\ColumnIdCollisionException
     */
    public function __construct(string $locale = '')
    {
        $this->locale = $locale;
        $this->addColumns(...$this->getColumns());
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
     * @return ModelColumn[]
     */
    abstract protected function getColumns(): array;

    /**
     * Define visible table columns
     *
     * @unreleased
     * @return string[]
     */
    abstract protected function getVisibleColumns(): array;

    /**
     * Get table definitions
     *
     * @unreleased
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
     */
    public function items(array $items)
    {
        $data = [];

        $columns = $this->getColumns();

        foreach ($items as $model) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column::getId()] = $column->getCellValue($model, $this->locale);
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

