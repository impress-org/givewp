<?php

namespace Give\Donations;

use Give\Framework\ListTableAPI\Column;
use Give\Framework\ListTableAPI\ListTable;

/**
 * @unreleased
 */
class DonationsListTable extends ListTable
{
    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donations';
    }

    /**
     * @inheritDoc
     */
    public function columns(): array
    {
        return [
            Column::id('id')
                ->label('ID')
                ->sortable(true),

            Column::id('amount')
                ->label(__('Amount', 'give'))
                ->sortable(true),

            Column::id('donationType')
                ->label(__('Payment Type', 'give')),

            Column::id('createdAt')
                ->label(__('Date / Time', 'give'))
                ->sortable(true),

            Column::id('name')
                ->label(__('Donor Name', 'give')),

            Column::id('formTitle')
                ->label(__('Donation Form', 'give'))
                ->sortable(true),

            Column::id('gateway')
                ->label(__('Gateway', 'give')),

            Column::id('status')
                ->label(__('Status', 'give'))
        ];
    }
}
