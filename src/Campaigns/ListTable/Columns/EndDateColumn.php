<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class EndDateColumn extends ModelColumn
{
    protected $sortColumn = 'endDate';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'endDate';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('End Date', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $format = _x('m/d/Y \a\t g:ia', 'date format', 'give');

        return $model->endDate->format($format);
    }
}
