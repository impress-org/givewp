<?php

namespace Give\DonationForms\ListTable;

use Give\DonationForms\ListTable\Columns\IdColumn;
use Give\Framework\ListTable\ListTable;

/**
 * @unreleased
 */
class DonationFormsListTable extends ListTable
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donationForms';
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
            IdColumn::getId(),
        ];
    }
}
