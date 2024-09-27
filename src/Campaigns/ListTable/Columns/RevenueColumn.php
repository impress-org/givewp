<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class RevenueColumn extends ModelColumn
{
    protected $sortColumn = 'revenue';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'revenue';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Revenue', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): int
    {
        return 0;
    }
}
