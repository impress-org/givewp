<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<Event>
 */
class DateColumn extends ModelColumn
{

    protected $sortColumn = 'startDateTime';

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
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param Event $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $format = _x('m/d/Y \a\t g:ia', 'date format', 'give');

        return $model->startDateTime->format($format);
    }
}
