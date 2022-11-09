<?php

namespace Give\Donors\ListTable;

use Give\Donors\ListTable\Columns\DonorInformationColumn;
use Give\Donors\ListTable\Columns\IdColumn;
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
            new IdColumn(),
            new DonorInformationColumn(),
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
            DonorInformationColumn::getId()
        ];
    }
}
