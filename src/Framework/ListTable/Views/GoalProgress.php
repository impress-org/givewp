<?php

namespace Give\Framework\ListTable\Views;

/**
 * The goal cell of a list table: a progress bar over the raised amount, with an "Achieved" pill.
 * The pill is in every row, hidden (also from screen readers) unless the goal is met, so every bar
 * has the same length.
 *
 * @since TBD
 */
class GoalProgress
{
    /**
     * @since TBD
     *
     * @param int|string $id Record id, used to label the progress bar.
     * @param int|float $percentage How much of the goal is reached; the bar shows 0–100.
     * @param string $actual The raised amount, formatted.
     * @param string $goal The goal, formatted.
     */
    public static function render($id, $percentage, $actual, $goal): string
    {
        $template = '
            <div class="goalProgress__container">
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
            </div>
        ';

        // The bar stops at 100%; the "Achieved" pill shows a goal passed.
        $barPercentage = max(0, min(100, $percentage));

        return sprintf(
            $template,
            $id,
            $barPercentage,
            $actual,
            sprintf(' %s %s', __('of', 'give'), $goal),
            self::achievedPill($percentage)
        );
    }

    /**
     * @since TBD
     */
    private static function achievedPill($percentage): string
    {
        $isVisible = apply_filters('givewp_list_table_goal_progress_achieved_opacity', $percentage >= 100 ? 1 : 0);

        return sprintf(
            '<span class="goalProgress--achieved"%s><img src="%s" alt="" />%s</span>',
            $isVisible ? '' : ' aria-hidden="true"',
            GIVE_PLUGIN_URL . 'build/assets/dist/images/list-table/star-icon.svg',
            __('Achieved', 'give')
        );
    }
}
