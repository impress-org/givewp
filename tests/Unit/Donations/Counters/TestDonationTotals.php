<?php

namespace Give\Tests\Unit\Donations\Counters;

use Give\Donations\Counters\DonationTotals;
use Give\Donations\Repositories\DonationTotalsCache;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TestDonationTotals extends TestCase
{
    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        delete_option(DonationTotalsCache::OPTION);
    }

    /**
     * @since TBD
     */
    public function testACountedValueIsReturnedAndSeededWithoutComputing(): void
    {
        $computed = 0;
        $compute = static function () use (&$computed) {
            $computed++;

            return 1;
        };

        $this->assertSame(7, DonationTotals::resolve('totals-counted', $compute, 7));
        $this->assertSame(0, $computed);

        // The cache now holds the counted value for the fallback path.
        $this->assertSame(7, DonationTotals::resolve('totals-counted', $compute, null));
        $this->assertSame(0, $computed);
    }

    /**
     * @since TBD
     */
    public function testALiveTotalIsComputedEveryTime(): void
    {
        $computed = 0;
        $compute = static function () use (&$computed) {
            return ++$computed;
        };

        $this->assertSame(1, DonationTotals::resolve('totals-live', $compute, null, true));
        $this->assertSame(2, DonationTotals::resolve('totals-live', $compute, null, true));
    }

    /**
     * @since TBD
     */
    public function testOtherwiseTheCachedTotalIsReturned(): void
    {
        $computed = 0;
        $compute = static function () use (&$computed) {
            return ++$computed;
        };

        $this->assertSame(1, DonationTotals::resolve('totals-cached', $compute));
        $this->assertSame(1, DonationTotals::resolve('totals-cached', $compute));
        $this->assertSame(1, $computed);
    }
}
