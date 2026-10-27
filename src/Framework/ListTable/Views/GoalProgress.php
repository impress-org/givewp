<?php

namespace Give\Framework\ListTable\Views;

/**
 * The goal cell of a list table: a progress ring next to the raised amount. Once the goal is met, a
 * check replaces the ring and "Goal achieved" replaces the percentage.
 *
 * @since TBD
 */
class GoalProgress
{
    /**
     * @since TBD
     *
     * @param int|string $id Record id, used to label the progress ring.
     * @param int|float $percentage How much of the goal is reached; the ring shows 0–100.
     * @param string $actual The raised amount, formatted.
     * @param string $goal The goal, formatted.
     */
    public static function render($id, $percentage, $actual, $goal): string
    {
        // The cell HTML goes through Interweave, which drops SVG and CSS variables, so the ring is a
        // conic gradient in an inline style. The cap rounds the end of the filled part.
        $template = '
            <div class="givewp-goal-progress%6$s">
                <span
                    class="givewp-goal-progress__ring"
                    role="progressbar"
                    aria-labelledby="giveDonationFormsProgressBar-%1$d"
                    aria-valuenow="%2$s"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="background-image: conic-gradient(currentColor %2$s%%, transparent 0)"
                ><span class="givewp-goal-progress__cap" style="transform: rotate(%7$sdeg)"></span></span>
                <span class="givewp-goal-progress__check" aria-hidden="true"></span>
                <div id="giveDonationFormsProgressBar-%1$d" class="givewp-goal-progress__amount">
                    <span><span class="goal">%3$s</span>%4$s</span>
                    %5$s
                </div>
            </div>
        ';

        // The ring stops at 100%; past that, the cell shows the goal as achieved.
        $ringPercentage = max(0, min(100, $percentage));

        return sprintf(
            $template,
            $id,
            $ringPercentage,
            $actual,
            sprintf(' %s %s', __('of', 'give'), $goal),
            self::status($ringPercentage, self::isLoading((string)$actual)),
            self::isAchieved($percentage) ? ' givewp-goal-progress--achieved' : '',
            $ringPercentage * 3.6
        );
    }

    /**
     * Both lines are rendered; the achieved modifier picks the one shown. The Forms list fills in the
     * percentage when it loads the goal in the background.
     *
     * @since TBD
     */
    private static function status($ringPercentage, bool $isLoading): string
    {
        $percent = $isLoading
            ? '<span class="give-skeleton" style="display: inline-block; width: 2ch; height: 1em;"></span>'
            : esc_html(floor($ringPercentage));

        return sprintf(
            '<span class="givewp-goal-progress__status givewp-goal-progress__status--progress">%s</span>' .
            '<span class="givewp-goal-progress__status givewp-goal-progress__status--achieved">%s</span>',
            sprintf(
                /* translators: %s: how much of the goal is reached, as a whole number */
                esc_html__('%s%% of goal', 'give'),
                '<span class="givewp-goal-progress__percent">' . $percent . '</span>'
            ),
            esc_html__('Goal achieved', 'give')
        );
    }

    /**
     * The Forms list can load the goal in the background; the raised amount is a skeleton until then.
     *
     * @since TBD
     */
    private static function isLoading(string $actual): bool
    {
        return false !== strpos($actual, 'give-skeleton');
    }

    /**
     * The filter used to set the opacity of the old "Achieved" icon. Any value above 0 still shows
     * the goal as achieved; the Forms list returns 0 while it loads the goal in the background.
     *
     * @since TBD
     */
    private static function isAchieved($percentage): bool
    {
        return (float)apply_filters(
            'givewp_list_table_goal_progress_achieved_opacity',
            $percentage >= 100 ? 1 : 0
        ) > 0;
    }
}
