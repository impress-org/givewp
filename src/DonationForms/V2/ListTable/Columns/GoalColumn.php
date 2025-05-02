<?php

declare(strict_types=1);

namespace Give\DonationForms\V2\ListTable\Columns;

use Give\DonationForms\DataTransferObjects\DonationFormGoalData;
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
        $form = $model->toV3Form();

        if (! $form->settings->enableDonationGoal) {
            return __('No Goal Set', 'give');
        }

        $goalData = (new DonationFormGoalData($form->id, $form->settings))->toArray();

        $stats = apply_filters('give_goal_progress_stats', [
            'raw_actual' => $goalData['currentAmount'],
            'raw_goal' => $goalData['targetAmount'],
            'progress' => $goalData['percentage'],
            'actual' => $goalData['typeIsMoney'] ? give_currency_filter(give_format_amount($goalData['currentAmount'])) : $goalData['currentAmount'],
            'goal' => $goalData['typeIsMoney'] ? give_currency_filter(give_format_amount($goalData['targetAmount'])) : $goalData['targetAmount'],
            'format' => $goalData['typeIsMoney'] ? 'percentage' : 'amount',
            'form_id' => $form->id,
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
            <div id="giveDonationFormsProgressBar-%1$d">
                <span class="goal">%3$s</span>%4$s %5$s
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
            sprintf(
                '<span style="opacity:%1$s" class="goalProgress--achieved"><img src="%2$s" alt="%3$s" />%4$s</span>',
                apply_filters('givewp_list_table_goal_progress_achieved_opacity', $stats['progress'] >= 100 ? 1 : 0),
                GIVE_PLUGIN_URL . 'build/assets/dist/images/list-table/star-icon.svg',
                __('Goal achieved icon', 'give'),
                __('Goal achieved!', 'give')
            )
        );
    }
}
