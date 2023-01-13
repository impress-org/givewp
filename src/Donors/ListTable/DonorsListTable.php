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
     * @unreleased
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
