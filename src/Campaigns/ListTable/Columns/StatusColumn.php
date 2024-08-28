<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class StatusColumn extends ModelColumn
{
    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'status';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Status', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model)
    {
        return $model->status->getValue();
    }
}
