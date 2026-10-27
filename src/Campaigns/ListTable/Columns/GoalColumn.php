<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\ListTable\Views\GoalProgress;

/**
 * @since 4.0.0
 */
class GoalColumn extends ModelColumn
{
    protected $useData = true;
    protected $sortColumn = 'goal';

    /**
     * @since 4.0.0
     */
    public static function getId(): string
    {
        return 'goal';
    }

    /**
     * @since 4.0.0
     */
    public function getLabel(): string
    {
        return __('Goal', 'give');
    }

    /**
     * @since TBD Show a progress ring next to the amount, and a check with "Goal achieved" once the goal is met or the givewp_list_table_goal_progress_achieved_opacity filter returns a value above 0.
     * @since 4.0.0
     *
     * @param Campaign $model
     */
    public function getCellValue($model): string
    {
        /**
         * @var CampaignsDataRepository $campaignsData
         */
        $campaignsData = $this->getListTableData();

        $goalData = $campaignsData->getGoalData($model);

        if ($goalData['goal'] === 0) {
            return __('No Goal Set', 'give');
        }

        return GoalProgress::render($model->id, $goalData['percentage'], $goalData['actualFormatted'], $goalData['goalFormatted']);
    }
}
