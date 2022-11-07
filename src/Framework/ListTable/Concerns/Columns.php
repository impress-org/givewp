<?php

namespace Give\Framework\ListTable\Concerns;

use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\ListTable\Exceptions\ReferenceColumnNotFoundException;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
trait Columns
{
    /**
     * @var ModelColumn[]
     */
    private $columns = [];

    /**
     * Add List Table column
     *
     * @unreleased
     * @throws ColumnIdCollisionException
     */
    public function addColumn(ModelColumn $column)
    {
        $columnId = $column::getId();

        if (isset($this->columns[$columnId])) {
            throw new ColumnIdCollisionException($columnId);
        }

        $this->setColumnVisibility($column);
        $this->columns[$columnId] = $column;
    }

    /**
     * Add List Table columns
     *
     * @unreleased
     * @throws ColumnIdCollisionException
     */
    public function addColumns(...$columns)
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * Remove List Table column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function removeColumn(string $columnId)
    {
        if (!isset($this->columns[$columnId])) {
            throw new ReferenceColumnNotFoundException($columnId);
        }

        unset($this->columns[$columnId]);
    }

    /**
     * @unreleased
     * @return ModelColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @unreleased
     */
    public function getColumnsArray(): array
    {
        return array_map(static function (ModelColumn $column) {
            return $column->toArray();
        }, array_values($this->columns));
    }

    /**
     * Add column before specific column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnBefore(string $columnId, ModelColumn $column)
    {
        if (is_int($index = $this->getColumnIndexById($columnId))) {
            $this->insertAtIndex($index, $column);
            return;
        }

        throw new ReferenceColumnNotFoundException($columnId);
    }

    /**
     * Add column after specific column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnAfter(string $columnId, ModelColumn $column)
    {
        if (is_int($index = $this->getColumnIndexById($columnId))) {
            $this->insertAtIndex($index + 1, $column);
            return;
        }

        throw new ReferenceColumnNotFoundException($columnId);
    }

    /**
     * Get registered column by column id
     *
     * @unreleased
     * @param string $columnId
     * @return ModelColumn|null
     */
    public function getColumnById(string $columnId)
    {
        return $this->columns[$columnId] ?? null;
    }

    /**
     * Get column position index
     *
     * @unreleased
     * @return int|false
     */
    public function getColumnIndexById(string $columnId)
    {
        return array_search($columnId, array_keys($this->columns), true);
    }

    /**
     * @unreleased
     */
    protected function insertAtIndex(int $index, ModelColumn $column)
    {
        $this->columns = array_merge(
            array_splice($this->columns, 0, $index),
            [$column::getId() => $column],
            $this->columns
        );
    }

    /**
     * @unreleased
     *
     * @return void
     */
    protected function setColumnVisibility($column)
    {
        $column->visible(in_array($column->getId(), $this->getVisibleColumns()));
    }

    /**
     * @unreleased
     *
     * @return string[]
     */
    public function getSortColumnById(string $columnId): array
    {
        $column = $this->getColumnById($columnId);

        return $column->getSortColumn() ?: ['id'];
    }
}
