<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class DescriptionColumn extends ModelColumn
{
    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'shortDescription';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Short Description', 'give');
    }

    /**
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        return wp_strip_all_tags($model->shortDescription, true);
    }
}
