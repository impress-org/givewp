<?php

namespace Give\Donors\ListTable;

use Give\Framework\ListTable\ListTable;

/**
 * @unreleased
 */
class DonorsListTable extends ListTable
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donors';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getColumns(): array
    {
        return [
        ];
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getDefaultVisibleColumns(): array
    {
        return [
        ];
    }
}
