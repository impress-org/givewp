<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Event>
 */
class DateColumn extends ModelColumn
{
    /**
     * @since TBD Sort by the start_datetime database column; startDateTime is the model property and failed the query.
     */
    protected $sortColumn = 'start_datetime';

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'startDateTime';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Start Date', 'give');
    }

    /**
     * @since TBD Format with the shared date formatter instead of a fixed pattern; $locale is no longer used.
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return DateFormatter::dateTime($model->startDateTime);
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::DATE;
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
        return DateFormatter::iso($model->startDateTime);
    }
}
