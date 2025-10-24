<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\Support\Facades\DateTime\Temporal;

/**
 * @since 4.0.0
 */
class DateColumn extends ModelColumn
{
    protected $sortColumn = 'date';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'date';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Date', 'give');
    }

    /**
     * @unreleased updated date format
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return Temporal::getFormattedDateTimeUsingTimeZoneAndFormatSettings($model->createdAt);
    }
}
