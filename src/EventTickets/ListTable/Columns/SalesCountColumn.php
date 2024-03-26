<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 3.6.0
 *
 * @extends ModelColumn<Event>
 */
class SalesCountColumn extends ModelColumn
{
    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public static function getId(): string
    {
        return 'salesCount';
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function getLabel(): string
    {
        return __('No. of tickets sold', 'give');
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model): string
    {
        $soldTicketsCount = $model->eventTickets()->count() ?? 0;
        $capacity = array_reduce($model->ticketTypes()->getAll() ?? [], function (int $carry, $ticketType) {
            return $carry + $ticketType->capacity;
        }, 0);

        return sprintf(
            __('%1$d out of %2$d', 'give'),
            $soldTicketsCount,
            $capacity
        );
    }
}
