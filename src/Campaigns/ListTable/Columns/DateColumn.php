<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Framework\ListTable\ModelColumn;

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
     * @since TBD Format with the shared date formatter.
     * @since 4.13.0 updated date format
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return DateFormatter::dateTime($model->createdAt);
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
     * @param Campaign $model
     */
    public function getRawValue($model)
    {
        return DateFormatter::iso($model->createdAt);
    }
}
