<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\DataTransferObjects\CampaignGoalData;
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
    public function getCellValue($model): string
    {
        $goalData = new CampaignGoalData($model);

        $template = '
            <div
                role="progressbar"
                aria-labelledby="giveDonationFormsProgressBar-%1$d"
                aria-valuenow="%2$s"
                aria-valuemin="0"
                aria-valuemax="100"
                class="goalProgress"
            >
                <span style="width: %2$s%%"></span>
            </div>
            <div id="giveDonationFormsProgressBar-%1$d">
                <span class="goal">%3$s</span>%4$s %5$s
            </div>
        ';

        return sprintf(
            $template,
            $model->id,
            $goalData->percentage,
            $goalData->actualFormatted,
            sprintf(
                ' %s %s',
                __('of', 'give'),
                $goalData->goalFormatted
            ),
            sprintf(
                '<span style="opacity:%1$s" class="goalProgress--achieved"><img src="%2$s" alt="%3$s" />%4$s</span>',
                apply_filters('givewp_list_table_goal_progress_achieved_opacity', $goalData->percentage >= 100 ? 1 : 0),
                GIVE_PLUGIN_URL . 'assets/dist/images/list-table/star-icon.svg',
                __('Goal achieved icon', 'give'),
                __('Goal achieved!', 'give')
            )
        );
    }
}
