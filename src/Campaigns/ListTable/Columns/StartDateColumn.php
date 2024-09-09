<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class StartDateColumn extends ModelColumn
{
    protected $sortColumn = 'startDate';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'startDate';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Start Date', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $format = _x('m/d/Y \a\t g:ia', 'date format', 'give');

        return $model->startDate->format($format);
    }
}
