<?php

namespace Give\Tests\Unit\Subscriptions;

use Give\Donations\Repositories\DonationTotalsCache;
use Give\Tests\TestCase;

/**
 * A subscription trash or restore drops the cached totals, so the stats never keep a trashed or restored
 * subscription in the wrong group.
 *
 * @since TBD
 */
class TestSubscriptionTotalsFlush extends TestCase
{
    /**
     * @since TBD
     *
     * @dataProvider writeHooksProvider
     */
    public function testASubscriptionWriteFlushesTheTotals(string $hook): void
    {
        // A stored total, so the next change is recorded again.
        DonationTotalsCache::remember('subscription-flush-' . $hook, static function () {
            return 1;
        });
        $before = (float)get_option(DonationTotalsCache::CHANGED_AT_OPTION, 0);
        usleep(2000);

        do_action($hook, new \stdClass());

        $this->assertGreaterThan($before, (float)get_option(DonationTotalsCache::CHANGED_AT_OPTION, 0));
    }

    /**
     * @since TBD
     */
    public function writeHooksProvider(): array
    {
        return [
            'trashed' => ['givewp_subscription_trashed'],
            'untrashed' => ['givewp_subscription_untrashed'],
        ];
    }
}
