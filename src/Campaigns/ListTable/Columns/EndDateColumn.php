<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class EndDateColumn extends ModelColumn
{
    protected $sortColumn = 'endDate';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'endDate';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('End Date', 'give');
    }

    /**
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $format = _x('m/d/Y \a\t g:ia', 'date format', 'give');

        return $model->endDate->format($format);
    }
}
