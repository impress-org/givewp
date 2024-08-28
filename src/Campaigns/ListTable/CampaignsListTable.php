<?php

namespace Give\Campaigns\ListTable;

use Give\Campaigns\ListTable\Columns\DescriptionColumn;
use Give\Campaigns\ListTable\Columns\DonationsCountColumn;
use Give\Campaigns\ListTable\Columns\IdColumn;
use Give\Campaigns\ListTable\Columns\StartDateColumn;
use Give\Campaigns\ListTable\Columns\StatusColumn;
use Give\Campaigns\ListTable\Columns\TitleColumn;
use Give\Framework\ListTable\ListTable;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class CampaignsListTable extends ListTable
{
    /**
     * @unreleased
     */
    public function id(): string
    {
        return 'campaigns';
    }

    /**
     * @unreleased
     *
     * @return array|ModelColumn[]
     */
    protected function getDefaultColumns(): array
    {
        return [
            new IdColumn(),
            new TitleColumn(),
            new DescriptionColumn(),
            new DonationsCountColumn(),
            new StartDateColumn(),
            new StatusColumn(),
        ];
    }

    /**
     * @unreleased
     *
     * @return array|string[]
     */
    protected function getDefaultVisibleColumns(): array
    {
        return [
            IdColumn::getId(),
            TitleColumn::getId(),
            DescriptionColumn::getId(),
            DonationsCountColumn::getId(),
            StartDateColumn::getId(),
            StatusColumn::getId(),
        ];
    }
}
