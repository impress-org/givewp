<?php

declare(strict_types=1);

namespace Give\EventTickets\ListTable\Columns;

use Give\EventTickets\Models\Event;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 3.6.0
 *
 * @extends ModelColumn<Event>
 */
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
