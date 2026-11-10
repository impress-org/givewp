<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\EventTickets\Repositories\EventsDataRepository;
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
     * Data loaded here, one event at a time, when the list table has no page data.
     *
     * @since TBD
     *
     * @var array<int, EventsDataRepository>
     */
    private $fallbackData = [];

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
     * @since TBD Read the sales from the data the endpoint loads for the whole page.
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
     * Returns [sales total, maximum sales at full capacity] from the data the endpoint loaded for the whole page.
     *
     * @since TBD
     *
     * @return array{0: Money, 1: Money}
     */
    private function salesFor(Event $event): array
    {
        $data = $this->dataFor($event);

        return [$data->getSalesTotal($event->id), $data->getMaxCapacitySales($event->id)];
    }

    /**
     * The page data, or data for this event alone when the column is used without page data.
     *
     * @since TBD
     */
    private function dataFor(Event $event): EventsDataRepository
    {
        $data = $this->getListTableData();

        if ($data instanceof EventsDataRepository) {
            return $data;
        }

        if (!isset($this->fallbackData[$event->id])) {
            $this->fallbackData[$event->id] = EventsDataRepository::events([$event->id]);
        }

        return $this->fallbackData[$event->id];
    }
}
