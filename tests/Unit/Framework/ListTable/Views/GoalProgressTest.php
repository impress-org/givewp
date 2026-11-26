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
        $html = GoalProgress::render(7, 150, '$100 raised');

        $this->assertStringContainsString('givewp-goal-progress--achieved', $html);
        $this->assertStringContainsString('aria-valuenow="100"', $html);
        $this->assertStringContainsString('stroke-dasharray=&#039;100 100&#039;', $html);
    }

    /**
     * @since TBD
     */
    public function testShowsTheRingAndPercentageBelowTheGoal(): void
    {
        $html = GoalProgress::render(7, 62.4, '$100 raised');

        $this->assertStringNotContainsString('givewp-goal-progress--achieved', $html);
        $this->assertStringContainsString('aria-valuenow="62.4"', $html);
        $this->assertStringContainsString('<span class="givewp-goal-progress__percent">62</span>% reached', $html);
        $this->assertStringContainsString('stroke-dasharray=&#039;57.1 100&#039;', $html);
    }

    /**
     * A see-through track stays quieter than the arc on white, on selected rows and in dark high contrast.
     *
     * @since TBD
     */
    public function testDrawsTheTrackAsASeeThroughGray(): void
    {
        $this->assertStringContainsString('stroke-opacity=&#039;0.25&#039;', GoalProgress::render(7, 40, '$100 raised'));
    }

    /**
     * @since TBD
     */
    public function testLeavesTheRingEmptyAt0(): void
    {
        $html = GoalProgress::render(7, -5, '$100 raised');

        $this->assertStringContainsString('aria-valuenow="0"', $html);
        // The track has the same gray, so the empty ring is told by an arc with no stroke.
        $this->assertStringContainsString('stroke=&#039;none&#039;', $html);
    }

    /**
     * @since TBD
     */
    public function testShowsADotForSmallProgress(): void
    {
        $html = GoalProgress::render(7, 3, '$100 raised');

        $this->assertStringContainsString('stroke=&#039;%23646970&#039;', $html);
        $this->assertStringContainsString('stroke-dasharray=&#039;0 100&#039;', $html);
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

        $html = GoalProgress::render(7, 40, '$100 raised');

        remove_filter('givewp_list_table_goal_progress_achieved_opacity', $filter);

        $this->assertStringContainsString('givewp-goal-progress--achieved', $html);
    }

    /**
     * The current amount is in the Revenue, Donations or Donors column, so the cell names only the target.
     *
     * @since TBD
     */
    public function testShowsTheTargetOnTheFirstLine(): void
    {
        $html = GoalProgress::render(7, 40, '500 donors');

        $this->assertStringContainsString('<span class="goal">500 donors</span>', $html);
        $this->assertStringNotContainsString(' of ', $html);
    }
}
