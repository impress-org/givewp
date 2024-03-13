<?php

namespace Give\EventTickets\ListTable;

use Give\EventTickets\ListTable\Columns\DateColumn;
use Give\EventTickets\ListTable\Columns\DescriptionColumn;
use Give\EventTickets\ListTable\Columns\SalesAmountColumn;
use Give\EventTickets\ListTable\Columns\SalesCountColumn;
use Give\EventTickets\ListTable\Columns\TitleColumn;
use Give\Framework\ListTable\ListTable;
use Give\Subscriptions\ListTable\Columns\IdColumn;

/**
 * @since 3.6.0
 */
class EventTicketsListTable extends ListTable
{
    /**
     * @since 3.6.0
     *
     * @inheritDoc
     */
    public function id(): string
    {
        return 'event-tickets';
    }

    /**
     * @since 3.6.0
     *
     * @inheritDoc
     */
    public function getDefaultColumns(): array
    {
        return [
            new IdColumn(),
            new TitleColumn(),
            new DescriptionColumn(),
            new SalesAmountColumn(),
            new SalesCountColumn(),
            new DateColumn(),
        ];
    }

    /**
     * @since 3.6.0
     *
     * @inheritDoc
     */
    public function getDefaultVisibleColumns(): array
    {
        return [
            IdColumn::getId(),
            TitleColumn::getId(),
            DescriptionColumn::getId(),
            SalesAmountColumn::getId(),
            SalesCountColumn::getId(),
            DateColumn::getId(),
        ];
    }
}
