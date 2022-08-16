<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Enums\CellValueType;

/**
 * @template M of Give\Framework\Models\Model
 */
abstract class ModelColumn implements ColumnInterface
{
    /**
     * The column when sorting the query by this column
     *
     * @unreleased
     *
     * @var string|null
     */
    public $sortColumn;

    /**
     * Whether the query can be sorted by this column.
     *
     * @unreleased
     */
    public function isSortable(): bool
    {
        return !empty($this->sortColumn);
    }

    /**
     * Returns the value to be displayed in the specific cell for that column and row.
     *
     * @unreleased
     *
     * @param M $model
     */
    abstract public function getCellValue($model);

    /**
     * Returns the type of value that should be displayed in the cell. This informs the front-end on how the value
     * should be rendered.
     *
     * @unreleased
     */
    public function getCellValueType(): CellValueType
    {
        return CellValueType::SIMPLE();
    }
}
