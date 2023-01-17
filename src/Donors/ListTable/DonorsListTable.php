<?php

namespace Give\Donors\ListTable;

use Give\Donors\ListTable\Columns\DateCreatedColumn;
use Give\Donors\ListTable\Columns\DonationRevenueColumn;
use Give\Donors\ListTable\Columns\DonationCountColumn;
use Give\Donors\ListTable\Columns\DonorInformationColumn;
use Give\Donors\ListTable\Columns\DonorTypeColumn;
use Give\Donors\ListTable\Columns\IdColumn;
use Give\Donors\ListTable\Columns\LatestDonationColumn;
use Give\Framework\ListTable\ListTable;

/**
 * @since 2.24.0
 */
class DonorsListTable extends ListTable
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donors';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            new IdColumn(),
            new DonorInformationColumn(),
            new DonationRevenueColumn(),
            new DonationCountColumn(),
            new LatestDonationColumn(),
            new DonorTypeColumn(),
            new DateCreatedColumn(),
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
            DonorInformationColumn::getId(),
            DonationRevenueColumn::getId(),
            DonationCountColumn::getId(),
            LatestDonationColumn::getId(),
            DonorTypeColumn::getId(),
            DateCreatedColumn::getId(),
        ];
    }
}
