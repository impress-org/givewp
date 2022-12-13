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
     *
     * @param ModelColumn $column
     *
     * @return self
     * @throws ColumnIdCollisionException
     */
    public function addColumn(ModelColumn $column): self
    {
        $columnId = $column::getId();

        if (isset($this->columns[$columnId])) {
            throw new ColumnIdCollisionException($columnId);
        }

        $this->columns[$columnId] = $column;
        $this->setColumnVisibility($columnId);

        return $this;
    }

    /**
     * Add List Table columns
     *
     * @unreleased
     *
     * @param ModelColumn ...$columns
     *
     * @return void
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
     *
     * @return self
     * @throws ReferenceColumnNotFoundException
     */
    public function removeColumn(string $columnId): self
    {
        if ( ! isset($this->columns[$columnId])) {
            throw new ReferenceColumnNotFoundException($columnId);
        }

        unset($this->columns[$columnId]);

        return $this;
    }

    /**
     * @unreleased
     *
     * @return ModelColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @unreleased
     *
     * @return array
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
     *
     * @return self
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnBefore(string $columnId, ModelColumn $column): self
    {
        if (is_int($index = $this->getColumnIndexById($columnId))) {
            return $this->insertAtIndex($index, $column);
        }

        throw new ReferenceColumnNotFoundException($columnId);
    }

    /**
     * Add column after specific column
     *
     * @unreleased
     *
     * @return self
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnAfter(string $columnId, ModelColumn $column): self
    {
        if (is_int($index = $this->getColumnIndexById($columnId))) {
            return $this->insertAtIndex($index + 1, $column);
        }

        throw new ReferenceColumnNotFoundException($columnId);
    }

    /**
     * Get registered column by column id
     *
     * @unreleased
     *
     * @param string $columnId
     *
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
     *
     * @return int|false
     */
    public function getColumnIndexById(string $columnId)
    {
        return array_search($columnId, array_keys($this->columns), true);
    }

    /**
     * @unreleased
     *
     * @return self
     */
    protected function insertAtIndex(int $index, ModelColumn $column): self
    {
        $this->columns = array_merge(
            array_splice($this->columns, 0, $index),
            [$column::getId() => $column],
            $this->columns
        );

        return $this;
    }

    /**
     * @unreleased
     *
     * @return self
     */
    public function setColumnVisibility($columnId, $isVisible = null): self
    {
        if (is_null($isVisible)) {
            $isVisible = in_array($columnId, $this->getDefaultVisibleColumns(), true);
        }

        $this->getColumnById($columnId)->visible($isVisible);

        return $this;
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
