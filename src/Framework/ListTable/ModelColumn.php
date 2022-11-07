<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\Support\Contracts\Arrayable;

/**
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
     * @unreleased
     */
    abstract public static function getId();

    /**
     * Returns the label for that column.
     *
     * @unreleased
     */
    abstract public function getLabel();

    /**
     * Returns the value to be displayed in the specific cell for that column and row.
     *
     * @unreleased
     *
     * @param M $model
     */
    abstract public function getCellValue($model);

    /**
     * @return bool
     */
    public function isSortable(): bool
    {
        return null !== $this->sortColumn;
    }

    /**
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
     * @param bool $visible
     *
     * @return void
     */
    public function visible(bool $visible)
    {
        $this->visibleColumn = $visible;
    }

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visibleColumn;
    }

    /**
     * @unreleased
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
