<?php

namespace Give\Framework\ListTable;

use Give\Framework\ListTable\Concerns\Columns;
use Give\Framework\ListTable\Concerns\Items;
use Give\Framework\Support\Contracts\Arrayable;

/**
 * @unreleased
 */
abstract class ListTable implements Arrayable
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
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'columns' => $this->getColumnsArray()
        ];
    }
}

