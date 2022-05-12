<?php

namespace Give\Framework\ListTable\Concerns;

use Give\Framework\ListTable\Column;
use Give\Framework\ListTable\Exceptions\ColumnIdCollisionException;
use Give\Framework\ListTable\Exceptions\ReferenceColumnNotFoundException;

/**
 * @unreleased
 */
trait Columns
{
    /**
     * @var Column[]
     */
    private $columns = [];

    /**
     * Add List Table column
     *
     * @unreleased
     * @throws ColumnIdCollisionException
     */
    public function addColumn(Column $column)
    {
        $id = $column->getId();

        if (isset($this->columns[$id])) {
            throw new ColumnIdCollisionException($id);
        }

        $this->columns[$id] = $column;
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
     * @return Column[]
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
        return array_map(static function (Column $column) {
            return $column->toArray();
        }, $this->columns);
    }

    /**
     * Add column before specific column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnBefore(string $columnId, Column $column)
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
    public function addColumnAfter(string $columnId, Column $column)
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
     * @param string $id
     * @return Column|null
     */
    public function getColumnById(string $id)
    {
        return $this->columns[$id] ?? null;
    }

    /**
     * Get column position index
     *
     * @unreleased
     * @return int|false
     */
    public function getColumnIndexById(string $id)
    {
        return array_search($id, array_keys($this->columns), true);
    }

    /**
     * @unreleased
     */
    protected function insertAtIndex(int $index, Column $column)
    {
        $this->columns = array_merge(
            array_splice($this->columns, 0, $index),
            [$column->getId() => $column],
            $this->columns
        );
    }
}
