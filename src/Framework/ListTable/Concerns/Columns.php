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
        $columnName = $column->getName();

        if (isset($this->columns[$columnName])) {
            throw new ColumnIdCollisionException($columnName);
        }

        $this->columns[$columnName] = $column;
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
        }, array_values($this->columns));
    }

    /**
     * Add column before specific column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnBefore(string $columnName, Column $column)
    {
        if (is_int($index = $this->getColumnIndexByName($columnName))) {
            $this->insertAtIndex($index, $column);
            return;
        }

        throw new ReferenceColumnNotFoundException($columnName);
    }

    /**
     * Add column after specific column
     *
     * @unreleased
     * @throws ReferenceColumnNotFoundException
     */
    public function addColumnAfter(string $columnName, Column $column)
    {
        if (is_int($index = $this->getColumnIndexByName($columnName))) {
            $this->insertAtIndex($index + 1, $column);
            return;
        }

        throw new ReferenceColumnNotFoundException($columnName);
    }

    /**
     * Get registered column by column id
     *
     * @unreleased
     * @param string $name
     * @return Column|null
     */
    public function getColumnByName(string $name)
    {
        return $this->columns[$name] ?? null;
    }

    /**
     * Get column position index
     *
     * @unreleased
     * @return int|false
     */
    public function getColumnIndexByName(string $columnName)
    {
        return array_search($columnName, array_keys($this->columns), true);
    }

    /**
     * @unreleased
     */
    protected function insertAtIndex(int $index, Column $column)
    {
        $this->columns = array_merge(
            array_splice($this->columns, 0, $index),
            [$column->getName() => $column],
            $this->columns
        );
    }
}
