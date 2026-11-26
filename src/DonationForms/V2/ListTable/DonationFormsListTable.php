<?php

namespace Give\DonationForms\V2\ListTable;

use Give\DonationForms\V2\ListTable\Columns\CampaignColumn;
use Give\DonationForms\V2\ListTable\Columns\DateCreatedColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationCountColumn;
use Give\DonationForms\V2\ListTable\Columns\DonorCountColumn;
use Give\DonationForms\V2\ListTable\Columns\DonationRevenueColumn;
use Give\DonationForms\V2\ListTable\Columns\GoalColumn;
use Give\DonationForms\V2\ListTable\Columns\IdColumn;
use Give\DonationForms\V2\ListTable\Columns\StatusColumn;
use Give\DonationForms\V2\ListTable\Columns\TitleColumn;
use Give\Framework\ListTable\ListTable;
use Give\Framework\ListTable\StatusVocabulary;
use Give\DonationForms\V2\ValueObjects\DonationFormStatus;

/**
 * @since TBD Send the status vocabulary.
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
     * @since TBD Add the Donors column, hidden by default.
     * @since TBD Put Status right after the title, then ID and Date.
     * @since 4.19.0 Add the Campaign column.
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            new TitleColumn(),
            new StatusColumn(),
            new IdColumn(),
            new DateCreatedColumn(),
            new CampaignColumn(),
            new GoalColumn(),
            new DonationCountColumn(),
            new DonorCountColumn(),
            new DonationRevenueColumn(),
        ];
    }

    /**
     * @since TBD Hide the Goal column by default.
     * @since 4.19.0 Add the Campaign column.
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getDefaultVisibleColumns(): array
    {
        return [
            IdColumn::getId(),
            TitleColumn::getId(),
            CampaignColumn::getId(),
            DonationCountColumn::getId(),
            DonationRevenueColumn::getId(),
            DateCreatedColumn::getId(),
            StatusColumn::getId(),
        ];
    }

    /**
     * @since TBD
     */
    protected function statuses(): ?StatusVocabulary
    {
        return DonationFormStatus::vocabulary();
    }
}
