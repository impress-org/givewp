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
                'price' => $ticketType->price->formatToDecimal(),
                'maxTicketsAvailable' => $ticketType->maxTicketsAvailable,
            ];
        }

        $salesTotal = array_reduce($model->eventTickets()->getAll() ?? [], function ($acc, $eventTicket) use ($ticketTypes) {
            return $acc + $ticketTypes[$eventTicket->ticketTypeId]['price'];
        }, 0);
        $maxTicketsAvailableTotal = array_reduce($ticketTypes, function ($acc, $ticketType) {
            return $acc + ($ticketType['maxTicketsAvailable'] * $ticketType['price']);
        }, 0);

        $salesPercentage = $maxTicketsAvailableTotal > 0 ? max(min($salesTotal / $maxTicketsAvailableTotal, 100), 0) : 0;

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
            Money::fromDecimal($salesTotal, give_get_currency())->formatToLocale($locale),
            __('of', 'give'),
            Money::fromDecimal($maxTicketsAvailableTotal, give_get_currency())->formatToLocale($locale)
        );
    }
}
