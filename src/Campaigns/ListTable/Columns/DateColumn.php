<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class DateColumn extends ModelColumn
{
    protected $sortColumn = 'date';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'date';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Date', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model, $locale = ''): string
    {
        $format = _x('m/d/Y \a\t g:ia', 'date format', 'give');

        return $model->createdAt->format($format);
    }
}
