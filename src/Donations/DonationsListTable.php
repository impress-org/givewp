<?php

namespace Give\Donations;

use Give\Framework\ListTable\Column;
use Give\Framework\ListTable\ListTable;

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
            Column::name('id')
                ->text('ID')
                ->sortable(true),

            Column::name('amount')
                ->text(__('Amount', 'give'))
                ->sortable(true),

            Column::name('donationType')
                ->text(__('Payment Type', 'give')),

            Column::name('createdAt')
                ->text(__('Date / Time', 'give'))
                ->sortable(true),

            Column::name('name')
                ->text(__('Donor Name', 'give')),

            Column::name('formTitle')
                ->text(__('Donation Form', 'give'))
                ->sortable(true),

            Column::name('gateway')
                ->text(__('Gateway', 'give')),

            Column::name('status')
                ->text(__('Status', 'give'))
        ];
    }
}
