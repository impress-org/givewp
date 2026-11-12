<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\ListTableData;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 2.24.0
 *
 * @template M of Give\Framework\Models\Model
 */
abstract class ModelColumn implements Arrayable
{

    use ListTableData;

    /**
     * @var string|array Define the meta key to be used when sorting the query by this column
     */
    protected $sortColumn;

    /**
     * @var bool Define if the column is visible
     */
    protected $visibleColumn;

    /**
     * Returns the id for that column.
     *
     * @since 2.24.0
     */
    abstract public static function getId(): string;

    /**
     * Returns the label for that column.
     *
     * @since 2.24.0
     */
    abstract public function getLabel(): string;

    /**
     * Returns the value to be displayed in the specific cell for that column and row.
     *
     * @since 2.24.0
     *
     * @param M $model
     *
     * @return int|string
     */
    abstract public function getCellValue($model);

    /**
     * Returns what the column holds, one of the ColumnKind constants. Clients use it to lay out the column.
     *
     * @since TBD
     */
    public function getKind(): string
    {
        return ColumnKind::TEXT;
    }

    /**
     * Returns the unformatted value of the cell, or null when the column has none. Sent next to the formatted
     * cell in the row's "rawValues" key: money as ['amount' => '1331.28', 'currency' => 'USD'], a date as an
     * ISO 8601 string in UTC, a count as an int.
     *
     * @since TBD
     *
     * @param M $model
     *
     * @return mixed
     */
    public function getRawValue($model)
    {
        return null;
    }

    /**
     * @since 2.24.0
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return null !== $this->sortColumn;
    }

    /**
     * @since 2.24.0
     *
     * @return array
     */
    public function getSortColumn(): array
    {
        if ( ! $this->isSortable() ) {
            return [];
        }

        return is_array( $this->sortColumn ) ? $this->sortColumn : [$this->sortColumn];
    }

    /**
     * @since 2.24.0
     *
     * @param bool $visible
     *
     * @return void
     */
    public function visible(bool $visible)
    {
        $this->visibleColumn = $visible;
    }

    /**
     * @since 2.24.0
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visibleColumn;
    }

    /**
     * @since TBD Add the column kind.
     * @since 2.24.0
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'sortable' => $this->isSortable(),
            'visible' => $this->isVisible(),
            'kind' => $this->getKind(),
        ];
    }
}
