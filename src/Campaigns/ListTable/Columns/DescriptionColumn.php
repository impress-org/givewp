<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class DescriptionColumn extends ModelColumn
{
    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'description';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Description', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        return wpautop($model->shortDescription);
    }
}
