<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class IdColumn extends ModelColumn
{
    protected $sortColumn = 'id';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'id';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('ID', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): int
    {
        return $model->id;
    }
}
