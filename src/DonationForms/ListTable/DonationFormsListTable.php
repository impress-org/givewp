<?php

namespace Give\DonationForms\ListTable;

use Give\DonationForms\ListTable\Columns\DonationCountColumn;
use Give\DonationForms\ListTable\Columns\IdColumn;
use Give\DonationForms\ListTable\Columns\LevelsColumn;
use Give\DonationForms\ListTable\Columns\TitleColumn;
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
            new TitleColumn(),
            new LevelsColumn(),
            new DonationCountColumn(),
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
            TitleColumn::getId(),
            LevelsColumn::getId(),
            DonationCountColumn::getId(),
        ];
    }
}
