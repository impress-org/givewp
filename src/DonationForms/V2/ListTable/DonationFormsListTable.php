<?php

namespace Give\DonationForms\V2\ListTable;

use Give\DonationForms\V2\ListTable\Columns\DateCreatedColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationCountColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationRevenueColumn;
use Give\DonationForms\V2\ListTable\Columns\GoalColumn;
use Give\DonationForms\V2\ListTable\Columns\IdColumn;
use Give\DonationForms\V2\ListTable\Columns\LevelsColumn;
use Give\DonationForms\V2\ListTable\Columns\ShortcodeColumn;
use Give\DonationForms\V2\ListTable\Columns\StatusColumn;
use Give\DonationForms\V2\ListTable\Columns\TitleColumn;
use Give\Framework\ListTable\ListTable;

/**
 * @since 2.24.0
 */
class DonationFormsListTable extends ListTable
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'donationForms';
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
            new TitleColumn(),
            new LevelsColumn(),
            new GoalColumn(),
            new DonationCountColumn(),
            new DonationRevenueColumn(),
            new ShortcodeColumn(),
            new DateCreatedColumn(),
            new StatusColumn(),
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
            TitleColumn::getId(),
            LevelsColumn::getId(),
            GoalColumn::getId(),
            DonationCountColumn::getId(),
            DonationRevenueColumn::getId(),
            ShortcodeColumn::getId(),
            DateCreatedColumn::getId(),
            StatusColumn::getId(),
        ];
    }
}
