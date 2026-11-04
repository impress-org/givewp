<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\MoneyFormatter;
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
     * The sales totals by event id, so the raw value reuses the queries of the cell.
     *
     * @since TBD
     *
     * @var array<int, array{0: Money, 1: Money}>
     */
    private $sales = [];

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
     * @since TBD Format with the shared money formatter; $locale is kept for add-on filters and no longer used.
     * @since 3.20.0 Refactored to use event ticket amount instead of ticket type price
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        [$salesTotal, $maxCapacitySales] = $this->salesFor($model);

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
            MoneyFormatter::format($salesTotal),
            __('of', 'give'),
            MoneyFormatter::format($maxCapacitySales)
        );
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::MONEY;
    }

    /**
     * The raw value is the sales total.
     *
     * @since TBD
     *
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getRawValue($model)
    {
        return MoneyFormatter::raw($this->salesFor($model)[0]);
    }

    /**
     * Returns [sales total, maximum sales at full capacity] once per event.
     *
     * @since TBD
     *
     * @return array{0: Money, 1: Money}
     */
    private function salesFor(Event $event): array
    {
        if (isset($this->sales[$event->id])) {
            return $this->sales[$event->id];
        }

        $ticketTypes = [];
        foreach ($event->ticketTypes()->getAll() ?? [] as $ticketType) {
            $salesCount = $ticketType->eventTickets()->count();
            $ticketsAvailable = $ticketType->capacity - $salesCount;

            $ticketTypes[$ticketType->id] = [
                'price' => $ticketType->price,
                'capacity' => $ticketType->capacity,
                'ticketsAvailable' => $ticketsAvailable,
            ];
        }

        $salesTotal = array_reduce(
            $event->eventTickets()->getAll() ?? [],
            function (Money $carry, $eventTicket) {
                return $carry->add($eventTicket->amount);
            },
            new Money(0, give_get_currency())
        );
        $maxCapacitySales = array_reduce($ticketTypes, function (Money $carry, $ticketType) {
            return $carry->add($ticketType['price']->multiply($ticketType['ticketsAvailable']));
        }, $salesTotal);

        return $this->sales[$event->id] = [$salesTotal, $maxCapacitySales];
    }
}
