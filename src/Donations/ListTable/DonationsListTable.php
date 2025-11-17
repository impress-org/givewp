<?php

namespace Give\Donations\ListTable;

use Give\Donations\ListTable\Columns\AmountColumn;
use Give\Donations\ListTable\Columns\CreatedAtColumn;
use Give\Donations\ListTable\Columns\DonorColumn;
use Give\Donations\ListTable\Columns\CampaignColumn;
use Give\Donations\ListTable\Columns\GatewayColumn;
use Give\Donations\ListTable\Columns\IdColumn;
use Give\Donations\ListTable\Columns\PaymentTypeColumn;
use Give\Donations\ListTable\Columns\StatusColumn;
use Give\Donations\ListTable\Columns\SubscriptionDonationTypeColumn;
use Give\Framework\ListTable\ListTable;

/**
 * @since 4.3.0 show campaign title instead of form title
 * @since 2.24.0
 */
class DonationsListTable extends ListTable
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donations';
    }

    /**
     * @since 4.10.0 Updated columns order
     * @since 4.8.0 Added Subscription Donation Type column
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            new IdColumn(),
            new AmountColumn(),
            new PaymentTypeColumn(),
            new DonorColumn(),
            new CampaignColumn(),
            new GatewayColumn(),
            new CreatedAtColumn(),
            new StatusColumn(),
            new SubscriptionDonationTypeColumn(),
        ];
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getDefaultVisibleColumns(): array
    {
        return [
            IdColumn::getId(),
            AmountColumn::getId(),
            PaymentTypeColumn::getId(),
            CreatedAtColumn::getId(),
            DonorColumn::getId(),
            CampaignColumn::getId(),
            StatusColumn::getId(),
        ];
    }
}
