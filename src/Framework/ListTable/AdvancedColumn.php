<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Enums\CellValueType;
use Give\Framework\QueryBuilder\QueryBuilder;

abstract class AdvancedColumn implements ColumnInterface
{
    /**
     * The query builder instance which will be used to build the query. This method
     * should be used to modify the query to include the necessary data for the column.
     *
     * @unreleased
     *
     * @return void
     */
    abstract public function modifyQuery(QueryBuilder $query);

    /**
     * The name of the query select which can be used to sort the query.
     *
     * @unreleased
     *
     * @return string|null
     */
    abstract public function getSortingKey();

    /**
     * Whether the query can be sorted by this column.
     *
     * @unreleased
     */
    abstract public function isSortable(): bool;

    /**
     * Returns the value to be displayed in the specific cell for that column and row.
     *
     * @unreleased
     *
     * @param object $row
     */
    abstract public function getCellValue($row);

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
