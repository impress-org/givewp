<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @since 2.24.0
 *
 * @template M of Give\Framework\Models\Model
 */
abstract class ModelColumn implements Arrayable
{
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
        ];
    }
}
