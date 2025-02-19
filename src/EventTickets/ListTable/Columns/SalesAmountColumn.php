<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @since 3.6.0
 *
 * @extends ModelColumn<Event>
 */
class SalesAmountColumn extends ModelColumn
{
    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public static function getId(): string
    {
        return 'salesAmount';
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function getLabel(): string
    {
        return __('Ticket Sales', 'give');
    }

    /**
     * @inheritDoc
     *
     * @since 3.20.0 Refactored to use event ticket amount instead of ticket type price
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $ticketTypes = [];
        foreach ($model->ticketTypes()->getAll() ?? [] as $ticketType) {
            $salesCount = $ticketType->eventTickets()->count();
            $ticketsAvailable = $ticketType->capacity - $salesCount;

            $ticketTypes[$ticketType->id] = [
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
                'ticketsAvailable' => $ticketsAvailable,
            ];
        }

        $salesTotal = array_reduce(
            $model->eventTickets()->getAll() ?? [],
                function (Money $carry, $eventTicket) {
                    return $carry->add($eventTicket->amount);
            },
            new Money(0, give_get_currency())
        );
        $maxCapacitySales = array_reduce($ticketTypes, function (Money $carry, $ticketType) {
            return $carry->add($ticketType['price']->multiply($ticketType['ticketsAvailable']));
        }, $salesTotal);

        $salesPercentage = $maxCapacitySales->formatToMinorAmount() > 0 ? max(
            min($salesTotal->formatToMinorAmount() / $maxCapacitySales->formatToMinorAmount(), 100),
            0
        ) : 0;

        $template = '
            <div
                role="progressbar"
                aria-labelledby="giveEventTicketsProgressBar-%1$d"
                aria-valuenow="%2$s"
                aria-valuemin="0"
                aria-valuemax="100"
                class="goalProgress"
            >
                <span style="width: %2$s%%"></span>
            </div>
            <div id="giveEventTicketsProgressBar-%1$d">%3$s %4$s %5$s</div>
        ';

        return sprintf(
            $template,
            $model->id,
            $salesPercentage,
            $salesTotal->formatToLocale($locale),
            __('of', 'give'),
            $maxCapacitySales->formatToLocale($locale)
        );
    }
}
