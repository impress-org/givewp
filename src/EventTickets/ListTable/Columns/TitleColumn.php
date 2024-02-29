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
class TitleColumn extends ModelColumn
{
    protected $sortColumn = 'title';

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function getId(): string
    {
        return 'title';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Event', 'give');
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
        return sprintf(
            '<a href="%s" aria-label="%s">%s</a>',
            admin_url("edit.php?post_type=give_forms&page=give-event-tickets&id={$model->id}"),
            __('Visit event page', 'give'),
            $model->title
        );
    }
}
