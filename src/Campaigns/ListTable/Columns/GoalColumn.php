<?php

namespace Give\Campaigns\ListTable\Columns;

use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Repositories\CampaignsDataRepository;
use Give\Framework\ListTable\ModelColumn;

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
            $goalData['percentage'],
            $goalData['actualFormatted'],
            sprintf(
                ' %s %s',
                __('of', 'give'),
                $goalData['goalFormatted']
            ),
            sprintf(
                '<span style="opacity:%1$s" class="goalProgress--achieved"><img src="%2$s" alt="%3$s" />%4$s</span>',
                apply_filters('givewp_list_table_goal_progress_achieved_opacity', $goalData['percentage'] >= 100 ? 1 : 0),
                GIVE_PLUGIN_URL . 'build/assets/dist/images/list-table/star-icon.svg',
                __('Goal achieved icon', 'give'),
                __('Goal achieved!', 'give')
            )
        );
    }
}
