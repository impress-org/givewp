<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\ListTable\Concerns\Items;

/**
 * @unreleased
 */
abstract class ListTable
{
    use Columns;
    use Items;

    /**
     * @unreleased
     * @throws Exceptions\ColumnIdCollisionException
     */
    public function __construct()
    {
        $this->addColumns(...$this->columns());
    }

    /**
     * Get table ID
     *
     * @unreleased
     */
    abstract public function id(): string;

    /**
     * Define table columns
     *
     * @unreleased
     * @return Column[]
     */
    abstract public function columns(): array;

    /**
     * Get table definitions
     *
     * @unreleased
     */
    public function getTable(): array
    {
        return [
            'id' => $this->id(),
            'columns' => $this->getColumnsArray()
        ];
    }
}

