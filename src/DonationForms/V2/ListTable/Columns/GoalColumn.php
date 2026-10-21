<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\Repositories\DonationFormDataRepository;
use Give\DonationForms\V2\Models\DonationForm;
use Give\Framework\ListTable\ModelColumn;

/**
 * @since 2.24.0
 *
 * @extends ModelColumn<DonationForm>
 */
class GoalColumn extends ModelColumn
{
    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'goal';
    }

    /**
     * @since 2.24.0
     *
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return __('Goal', 'give');
    }

    /**
     * @since TBD Show "Achieved" as a pill next to the amount, only when the goal is met (or the givewp_list_table_goal_progress_achieved_opacity filter returns a truthy value).
     * @since 4.3.0 use DonationFormDataRepository
     * @since 4.2.0 convert goal data to v3 form format
     * @since 3.16.0 Remove "give_get_form_earnings_stats" filter logic and add filters to change the cell value content
     * @since 3.14.0 Use the "give_get_form_earnings_stats" filter to ensure the correct value will be displayed in the form  progress bar
     * @since 2.24.0
     *
     * @inheritDoc
     *
     * @param DonationForm $model
     */
    public function getCellValue($model): string
    {
        if (! $model->goalSettings->enableDonationGoal) {
            return __('No Goal Set', 'give');
        }

        /**
         * @var DonationFormDataRepository $donationFormData
         */
        $donationFormData = $this->getListTableData();

        $goalData = $donationFormData->getGoalData($model);

        $stats = apply_filters('give_goal_progress_stats', [
            'raw_actual' => $goalData['actual'],
            'raw_goal' => $goalData['goal'],
            'progress' => $goalData['percentage'],
            'actual' => $goalData['actualFormatted'] ,
            'goal' => $goalData['goalFormatted'],
            'format' => $goalData['typeIsMoney'] ? 'percentage' : 'amount',
            'form_id' => $model->id,
        ]);


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
            <div id="giveDonationFormsProgressBar-%1$d" class="goalProgress__amount">
                <span><span class="goal">%3$s</span>%4$s</span>%5$s
            </div>
        ';

        return sprintf(
            $template,
            $model->id,
            $stats['progress'],
            $stats['actual'],
            sprintf(
                ' %s %s',
                __('of', 'give'),
                $stats['goal']
            ),
            apply_filters('givewp_list_table_goal_progress_achieved_opacity', $stats['progress'] >= 100 ? 1 : 0)
                ? sprintf(
                    '<span class="goalProgress--achieved"><img src="%s" alt="" />%s</span>',
                    GIVE_PLUGIN_URL . 'build/assets/dist/images/list-table/star-icon.svg',
                    __('Achieved', 'give')
                )
                : ''
        );
    }
}
