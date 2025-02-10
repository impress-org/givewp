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
class DescriptionColumn extends ModelColumn
{
    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public static function getId(): string
    {
        return 'description';
    }

    /**
     * @inheritDoc
     *
     * @since 3.6.0
     */
    public function getLabel(): string
    {
        return __('Description', 'give');
    }

    /**
     * @inheritDoc
     *
     * @since 3.20.0 Truncate description to 200 characters
     * @since 3.6.0
     *
     * @param Event $model
     */
    public function getCellValue($model): string
    {
        $maxChars = 200;
        $truncatedDescription = mb_substr($model->description, 0, $maxChars);

        if (mb_strlen($model->description) > $maxChars) {
            $truncatedDescription .= '...';
        }

        return sprintf('<div class="event-description">%s</div>', wpautop($truncatedDescription));
    }
}
