<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ColumnKind;
use Give\Framework\ListTable\ModelColumn;
use Give\Framework\ListTable\Views\GoalProgress;
use Give\Framework\ListTable\Views\Placeholder;

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
     * @since TBD Show a labeled dash when the campaign has no goal.
     * @since TBD Format the amounts with the shared money formatter.
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

        $goalData = $campaignsData->getGoalData($model, true);

        if ($goalData['goal'] === 0) {
            return Placeholder::dash(__('No Goal Set', 'give'));
        }

        return GoalProgress::render($model->id, $goalData['percentage'], $goalData['actualFormatted'], $goalData['goalFormatted']);
    }

    /**
     * @since TBD
     *
     * @inheritDoc
     */
    public function getKind(): string
    {
        return ColumnKind::TEXT;
    }
}
