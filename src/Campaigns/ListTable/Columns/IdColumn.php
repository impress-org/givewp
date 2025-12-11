<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 4.0.0
 */
class IdColumn extends ModelColumn
{
    protected $sortColumn = 'id';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
