<?php

namespace Give\Campaigns\ListTable\Columns;

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
     */
    public function getCellValue($model): int
    {
        return 1; //return $model->id;
    }
}
