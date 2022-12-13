<?php

namespace Give\Subscriptions\ListTable;

use Give\Subscriptions\ListTable\Columns\AmountColumn;
use Give\Subscriptions\ListTable\Columns\DonorColumn;
use Give\Subscriptions\ListTable\Columns\FormColumn;
use Give\Subscriptions\ListTable\Columns\IdColumn;
use Give\Framework\ListTable\ListTable;
use Give\Subscriptions\ListTable\Columns\BillingPeriodColumn;
use Give\Subscriptions\ListTable\Columns\RenewalDateColumn;
use Give\Subscriptions\ListTable\Columns\StatusColumn;

/**
 * @unreleased
 */
class SubscriptionsListTable extends ListTable
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'subscriptions';
    }

    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            new IdColumn(),
            new AmountColumn(),
            new DonorColumn(),
            new FormColumn(),
            new BillingPeriodColumn(),
            new RenewalDateColumn(),
            new StatusColumn(),
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
            AmountColumn::getId(),
            DonorColumn::getId(),
            FormColumn::getId(),
            BillingPeriodColumn::getId(),
            RenewalDateColumn::getId(),
            StatusColumn::getId(),
        ];
    }
}
