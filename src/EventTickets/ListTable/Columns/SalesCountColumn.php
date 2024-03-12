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
        $ticketTypes = $model->ticketTypes()->getAll() ?? [];
        $soldTicketsCount = $model->eventTickets()->count() ?? 0;

        $hasUnlimitedCapacity = array_filter($ticketTypes, function ($ticketType) {
            return is_null($ticketType->capacity);
        });

        if ( ! empty($hasUnlimitedCapacity)) {
            $capacity = __('Unlimited', 'give');
        } else {
            $capacity = array_reduce($ticketTypes, function (int $carry, $ticketType) {
                return $carry + $ticketType->capacity;
            }, 0);
        }

        return sprintf(
            __('%1$d of %2$s', 'give'),
            $soldTicketsCount,
            $capacity
        );
    }
}
