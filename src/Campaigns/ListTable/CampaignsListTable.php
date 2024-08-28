<?php

namespace Give\Campaigns\ListTable;

use Give\Campaigns\ListTable\Columns\IdColumn;
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
        ];
    }
}
