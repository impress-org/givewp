<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 3.6.0
 *
 * @extends ModelColumn<Event>
 */
class SalesCountColumn extends ModelColumn
{
    /**
     * The sold ticket count by event id, so the raw value reuses the query of the cell.
     *
     * @since TBD
     *
     * @var array<int, int>
     */
    private $soldCounts = [];
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
     * @since TBD Read the sold count through a per-event memo.
     * @since 4.19.1 Add translators comments.
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model): string
    {
        $soldTicketsCount = $this->soldCount($model);
        $capacity = array_reduce($model->ticketTypes()->getAll() ?? [], function (int $carry, $ticketType) {
            return $carry + $ticketType->capacity;
        }, 0);

        return sprintf(
            /* translators: 1: Number of tickets sold, 2: Total ticket capacity */
            __('%1$d out of %2$d', 'give'),
            $soldTicketsCount,
            $capacity
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::COUNT;
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getRawValue($model)
    {
        return $this->soldCount($model);
    }

    /**
     * Counts the sold tickets once per event.
     *
     * @since TBD
     */
    private function soldCount(Event $event): int
    {
        if (!isset($this->soldCounts[$event->id])) {
            $this->soldCounts[$event->id] = (int) ($event->eventTickets()->count() ?? 0);
        }

        return $this->soldCounts[$event->id];
    }
}
