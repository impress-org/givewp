<?php

namespace Give\Tests\Unit\Framework\ListTable\Views;

use Give\Framework\ListTable\Views\GoalProgress;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class GoalProgressTest extends TestCase
{
    /**
     * @since TBD
     */
    public function testShowsTheGoalAsAchievedPast100(): void
    {
        $html = GoalProgress::render(7, 150, '$150', '$100');

        $this->assertStringContainsString('givewp-goal-progress--achieved', $html);
        $this->assertStringContainsString('aria-valuenow="100"', $html);
        $this->assertStringContainsString('conic-gradient(currentColor 100%', $html);
    }

    /**
     * @since TBD
     */
    public function testShowsTheRingAndPercentageBelowTheGoal(): void
    {
        $html = GoalProgress::render(7, 62.4, '$62', '$100');

        $this->assertStringNotContainsString('givewp-goal-progress--achieved', $html);
        $this->assertStringContainsString('aria-valuenow="62.4"', $html);
        $this->assertStringContainsString('<span class="givewp-goal-progress__percent">62</span>% of goal', $html);
        $this->assertStringContainsString('transform: rotate(224.64deg)', $html);
    }

    /**
     * @since TBD
     */
    public function testStartsTheRingAt0(): void
    {
        $html = GoalProgress::render(7, -5, '$0', '$100');

        $this->assertStringContainsString('aria-valuenow="0"', $html);
        $this->assertStringContainsString('conic-gradient(currentColor 0%', $html);
    }

    /**
     * @since TBD
     */
    public function testShowsASkeletonPercentageWhileTheAmountLoads(): void
    {
        $html = GoalProgress::render(7, 0, '<span class="give-skeleton js-give-async-data"></span>', '$100');

        $this->assertStringNotContainsString('<span class="givewp-goal-progress__percent">0</span>', $html);
        $this->assertStringContainsString('<span class="givewp-goal-progress__percent"><span class="give-skeleton"', $html);
    }

    /**
     * @since TBD
     */
    public function testShowsTheGoalAsAchievedWhenTheFilterReturnsAValueAbove0(): void
    {
        $filter = function () {
            return 0.5;
        };
        add_filter('givewp_list_table_goal_progress_achieved_opacity', $filter);

        $html = GoalProgress::render(7, 40, '$40', '$100');

        remove_filter('givewp_list_table_goal_progress_achieved_opacity', $filter);

        $this->assertStringContainsString('givewp-goal-progress--achieved', $html);
    }
}
