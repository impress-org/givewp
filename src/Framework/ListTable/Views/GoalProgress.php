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
     * The ring is an image, so it cannot read CSS variables. These match the Event Tickets sales bar.
     *
     * @since TBD
     */
    private const RING_COLOR = '#646970';
    private const TRACK_COLOR = '#646970';

    /**
     * @since TBD
     *
     * @param int|string $id Record id, used to label the progress ring.
     * @param int|float $percentage How much of the goal is reached; the ring shows 0–100.
     * @param string $target The goal with what it counts, from GoalLabel::make().
     */
    public static function render($id, $percentage, string $target): string
    {
        // The cell HTML goes through Interweave, which drops inline SVG, so the ring is an SVG image.
        $template = '
            <div class="givewp-goal-progress%5$s">
                <span
                    class="givewp-goal-progress__ring"
                    role="progressbar"
                    aria-labelledby="giveDonationFormsProgressBar-%1$d"
                    aria-valuenow="%2$s"
                    aria-valuemin="0"
                    aria-valuemax="100"
                ><img class="givewp-goal-progress__ring-image" src="%6$s" alt="" /></span>
                <span class="givewp-goal-progress__check" aria-hidden="true"></span>
                <div id="giveDonationFormsProgressBar-%1$d" class="givewp-goal-progress__amount">
                    <span class="goal">%3$s</span>
                    %4$s
                </div>
            </div>
        ';

        // The ring stops at 100%; past that, the cell shows the goal as achieved.
        $ringPercentage = max(0, min(100, $percentage));

        return sprintf(
            $template,
            $id,
            esc_attr($ringPercentage),
            $target,
            self::status($ringPercentage),
            self::isAchieved($percentage) ? ' givewp-goal-progress--achieved' : '',
            esc_attr(self::ringImage($ringPercentage))
        );
    }

    /**
     * The ring as an SVG data URI: a see-through track and an arc, both in the gray of the cell text, with round ends. The round ends add
     * half the stroke width at each end, so the arc is drawn that much shorter: a goal at 99% still
     * shows a gap, and any progress above 0 shows at least a dot. At 0 there is no arc.
     *
     * @since TBD
     */
    private static function ringImage($ringPercentage): string
    {
        // Two half-stroke ends (2 + 2 units) on a circle of 2π × 12 units, as a percentage.
        $roundEnds = 4 / (2 * M_PI * 12) * 100;
        $arc = $ringPercentage >= 100 ? 100 : round(max(0, $ringPercentage - $roundEnds), 1);
        $arcColor = $ringPercentage > 0 ? self::RING_COLOR : 'none';

        $svg = sprintf(
            "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 28 28'>" .
            "<circle cx='14' cy='14' r='12' fill='none' stroke='%s' stroke-opacity='0.25' stroke-width='4'/>" .
            "<circle cx='14' cy='14' r='12' fill='none' stroke='%s' stroke-width='4' stroke-linecap='round'" .
            " pathLength='100' stroke-dasharray='%s 100' transform='rotate(-90 14 14)'/>" .
            "</svg>",
            self::TRACK_COLOR,
            $arcColor,
            $arc
        );

        return 'data:image/svg+xml,' . str_replace(['<', '>', '#'], ['%3C', '%3E', '%23'], $svg);
    }

    /**
     * Both lines are rendered; the achieved modifier picks the one shown.
     *
     * @since TBD
     */
    private static function status($ringPercentage): string
    {
        $percent = esc_html(floor($ringPercentage));

        return sprintf(
            '<span class="givewp-goal-progress__status givewp-goal-progress__status--progress">%s</span>' .
            '<span class="givewp-goal-progress__status givewp-goal-progress__status--achieved">%s</span>',
            sprintf(
                /* translators: %s: how much of the goal is reached, as a whole number */
                esc_html__('%s%% reached', 'give'),
                '<span class="givewp-goal-progress__percent">' . $percent . '</span>'
            ),
            esc_html__('Goal achieved', 'give')
        );
    }

    /**
     * The filter used to set the opacity of the old "Achieved" icon. Any value above 0 still shows
     * the goal as achieved.
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
