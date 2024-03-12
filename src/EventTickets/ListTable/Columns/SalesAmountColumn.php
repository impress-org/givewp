<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\ValueObjects\Money;

/**
 * @unreleased
 *
 * @extends ModelColumn<Event>
 */
class SalesAmountColumn extends ModelColumn
{
    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function getId(): string
    {
        return 'sales-amount';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Ticket Sales', 'give');
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     *
     * @param Event $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $ticketTypes = [];
        foreach ($model->ticketTypes()->getAll() ?? [] as $ticketType) {
            $ticketTypes[$ticketType->id] = [
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
            ];

            if (is_null($ticketType->capacity)) {
                $maxCapacitySales = __('Unlimited', 'give');
            }
        }

        $salesTotal = array_reduce(
            $model->eventTickets()->getAll() ?? [],
                function (Money $carry, $eventTicket) use ($ticketTypes) {
                    return $carry->add($ticketTypes[$eventTicket->ticketTypeId]['price']);
            },
            new Money(0, give_get_currency())
        );

        $salesPercentage = $salesTotal->getAmount() > 0 ? 100 : 0;

        if ( ! isset($maxCapacitySales)) {
            $maxCapacitySales = array_reduce($ticketTypes, function (Money $carry, $ticketType) {
                return $carry->add($ticketType['price']->multiply($ticketType['capacity']));
            }, new Money(0, give_get_currency()));

            $salesPercentage = $maxCapacitySales->formatToMinorAmount() > 0 ? max(
                min($salesTotal->formatToMinorAmount() / $maxCapacitySales->formatToMinorAmount(), 100),
                0
            ) : 0;
        }

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
            $maxCapacitySales instanceof Money ? $maxCapacitySales->formatToLocale($locale) : $maxCapacitySales
        );
    }
}
