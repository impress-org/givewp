<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
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
     * @unreleased
     */
    abstract public static function getId(): string;

    /**
     * Returns the label for that column.
     *
     * @unreleased
     */
    abstract public function getLabel(): string;

    /**
     * Returns the value to be displayed in the specific cell for that column and row.
     *
     * @unreleased
     *
     * @param M $model
     *
     * @return int|string
     */
    abstract public function getCellValue($model);

    /**
     * @unreleased
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return null !== $this->sortColumn;
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visibleColumn;
    }

    /**
     * @unreleased
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
