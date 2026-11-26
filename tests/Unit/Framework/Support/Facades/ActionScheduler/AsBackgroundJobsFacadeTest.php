<?php

namespace Give\Tests\Unit\Framework\Support\Facades\ActionScheduler;

use ActionScheduler_Store;
use Give\Framework\Support\Facades\ActionScheduler\AsBackgroundJobs;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 */
class AsBackgroundJobsFacadeTest extends TestCase
{
    use RefreshDatabase;

    private const HOOK = 'givewp_test_scheduled_hook';

    private const GROUP = 'givewp_test_group';

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        as_unschedule_all_actions(self::HOOK);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testScheduleRecurringActionSchedulesOnce(): void
    {
        AsBackgroundJobs::scheduleRecurringAction(time() + 60, 120, self::HOOK, [], self::GROUP);
        AsBackgroundJobs::scheduleRecurringAction(time() + 60, 120, self::HOOK, [], self::GROUP);

        $this->assertSame(1, $this->pendingActions());
    }

    /**
     * @since TBD
     */
    public function testScheduleSingleActionSchedulesOncePerHookAndGroup(): void
    {
        AsBackgroundJobs::scheduleSingleAction(time() + 60, self::HOOK, [1], self::GROUP);
        AsBackgroundJobs::scheduleSingleAction(time() + 90, self::HOOK, [2], self::GROUP);

        $this->assertSame(1, $this->pendingActions());
    }

    /**
     * @since TBD
     */
    private function pendingActions(): int
    {
        return count(
            as_get_scheduled_actions(
                ['hook' => self::HOOK, 'group' => self::GROUP, 'status' => ActionScheduler_Store::STATUS_PENDING],
                'ids'
            )
        );
    }
}
