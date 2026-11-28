<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\Formatting\DateFormatter;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class StartDateColumn extends ModelColumn
{
    protected $sortColumn = 'startDate';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'startDate';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Start Date', 'give');
    }

    /**
     * @since TBD Format the date only, in the site date format; no date shows the dash.
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        return DateFormatter::date($model->startDate);
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
        return DateFormatter::iso($model->startDate);
    }
}
