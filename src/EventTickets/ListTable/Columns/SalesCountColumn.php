<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\EventTickets\Repositories\EventsDataRepository;
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
     * @since TBD Read the sold count and the capacity from the page data, and write both numbers in the admin's locale.
     * @since 4.19.1 Add translators comments.
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model): string
    {
        $data = $this->dataFor($model);
        $soldTicketsCount = $data->getSoldCount($model->id);
        $capacity = $data->getCapacity($model->id);

        // The numbers go in after the translation, so the text still matches the translations of the original.
        return strtr(
            /* translators: 1: Number of tickets sold, 2: Total ticket capacity */
            __('%1$d out of %2$d', 'give'),
            [
                '%1$d' => number_format_i18n($soldTicketsCount),
                '%2$d' => number_format_i18n($capacity),
            ]
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
        return $this->dataFor($model)->getSoldCount($model->id);
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
