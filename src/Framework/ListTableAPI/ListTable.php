<?php

namespace Give\Framework\ListTableAPI;

use Give\Framework\ListTableAPI\Concerns\Columns;
use Give\Framework\ListTableAPI\Concerns\Items;

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
     * Register table columns
     *
     * @unreleased
     * @return Column[]
     */
    abstract public function columns(): array;

    /**
     * @unreleased
     */
    public function getTableDefinitions(): array
    {
        return [
            'id' => $this->id(),
            'columns' => $this->getColumns()
        ];
    }
}

