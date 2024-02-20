<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 *
 * @extends ModelColumn<Event>
 */
class SalesCountColumn extends ModelColumn
{
    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function getId(): string
    {
        return 'sales-count';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('No. of tickets sold', 'give');
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     *
     * @param Event $model
     */
    public function getCellValue($model): string
    {
        $soldTicketsCount = $model->eventTickets()->count() ?? 0;
        $capacity = array_reduce($model->ticketTypes()->getAll() ?? [], function ($acc, $ticketType) {
            return $acc + $ticketType->capacity;
        }, 0);

        return sprintf(
            __('%1$d out of %2$d', 'give'),
            $soldTicketsCount,
            $capacity
        );
    }
}
