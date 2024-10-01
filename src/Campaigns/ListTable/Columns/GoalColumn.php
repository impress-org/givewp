<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Framework\ListTable\ModelColumn;

/**
 * @unreleased
 */
class GoalColumn extends ModelColumn
{
    protected $sortColumn = 'goal';

    /**
     * @unreleased
     */
    public static function getId(): string
    {
        return 'goal';
    }

    /**
     * @unreleased
     */
    public function getLabel(): string
    {
        return __('Goal', 'give');
    }

    /**
     * @unreleased
     *
     * @param Campaign $model
     */
    public function getCellValue($model): int
    {
        return $model->goal;
    }
}
