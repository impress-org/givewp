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
    public function testStopsTheBarAt100AndShowsTheAchievedPill(): void
    {
        $html = GoalProgress::render(7, 150, '$150', '$100');

        $this->assertStringContainsString('aria-valuenow="100"', $html);
        $this->assertStringContainsString('width: 100%', $html);
        $this->assertStringNotContainsString('aria-hidden="true"', $html);
    }

    /**
     * @since TBD
     */
    public function testStartsTheBarAt0AndHidesTheAchievedPill(): void
    {
        $html = GoalProgress::render(7, -5, '$0', '$100');

        $this->assertStringContainsString('aria-valuenow="0"', $html);
        $this->assertStringContainsString('width: 0%', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }
}
