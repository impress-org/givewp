<?php

namespace Give\Framework\ListTableAPI\Concerns;

use Give\Framework\ListTableAPI\Column;
use Give\Framework\ListTableAPI\Exceptions\ColumnIdCollisionException;
use Give\Framework\ListTableAPI\Exceptions\ReferenceColumnNotFoundException;

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
     */
    public function getColumns(): array
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
     * @return Column|false
     */
    public function getColumnById(string $id)
    {
        if (array_key_exists($id, $this->columns)) {
            return $this->columns[$id];
        }

        return false;
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
