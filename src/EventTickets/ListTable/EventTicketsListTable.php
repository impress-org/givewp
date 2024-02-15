<?php

namespace Give\EventTickets\ListTable;

use Give\Framework\ListTable\ListTable;
use Give\Subscriptions\ListTable\Columns\IdColumn;

/**
 * @unreleased
 */
class EventTicketsListTable extends ListTable
{
    /**
     * @unreleased
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'event-tickets';
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
        ];
    }
}
