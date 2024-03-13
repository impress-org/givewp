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
class IdColumn extends ModelColumn
{

    protected $sortColumn = 'id';

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @inheritDoc
     *
     * @unreleased
     *
     * @param Event $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
