<?php

declare(strict_types=1);

namespace Give\Framework\ListTable;

class SimpleColumn implements ColumnInterface
{
    /**
     * @unreleased
     *
     * @param int[] $rowIds
     */
    abstract public function getRowValues(array $rowIds): array;
}
