<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Exception;
use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\Models\Model;
use Give\Framework\Support\Contracts\Arrayable;
use Give\Log\Log;

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
            'columns' => $this->getColumnsArray(),
        ];
    }

    /**
     * Set table items
     *
     * @since 2.24.0
     *
     * @param array $items
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
                $row[$column::getId()] = $this->safelyGetCellValue($column, $model, $locale);;
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

    /**
     * Safely retrieves the cell value for a column. If an exception is thrown, it will be logged and the cell value
     * will be a human-readable error message. This is to prevent fatal errors from breaking the entire table.
     *
     * @since 2.24.1
     *
     * @return mixed
     */
    private function safelyGetCellValue(ModelColumn $column, Model $model, string $locale)
    {
        try {
            $cellValue = $column->getCellValue($model, $locale);
        } catch (Exception $exception) {
            Log::error(
                sprintf(
                    'Error while rendering column "%s" for table "%s".',
                    $column::getId(),
                    $this->id()
                ),
                [
                    'column' => $column::getId(),
                    'table' => $this->id(),
                    'model' => $model->toArray(),
                    'exception' => $exception->getMessage(),
                ]
            );

            $cellValue = __(
                sprintf(
                    'Something went wrong, more in detail in <a href="%s">logs</a>',
                    admin_url('edit.php?post_type=give_forms&page=give-tools&tab=logs')
                ),
                'give'
            );
        }

        return $cellValue;
    }
}

