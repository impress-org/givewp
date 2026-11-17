<?php

namespace Give\Tests\Unit\Donations\Repositories;

use Give\Donations\Repositories\DonationTotalsCache;
use Give\Tests\TestCase;

/**
 * The final flush of a request marks a total outdated that is still being computed when the request ends.
 *
 * @since TBD
 */
class TestDonationTotalsFlushFinal extends TestCase
{
    /**
     * @since TBD
     */
    public function testFlushFinalOutdatesATotalThatFinishesAfterTheRequestEnds(): void
    {
        add_filter('givewp_donation_totals_max_age', '__return_zero');

        DonationTotalsCache::flush();

        // Another request starts counting after that flush; the first request ends while it counts.
        DonationTotalsCache::recompute('flush-final-key', static function () {
            DonationTotalsCache::flushFinal();

            return 1;
        });

        $this->assertSame(2, DonationTotalsCache::remember('flush-final-key', static function () {
            return 2;
        }));

        remove_filter('givewp_donation_totals_max_age', '__return_zero');
    }

    /**
     * @since TBD
     */
    public function testFlushAloneDoesNotOutdateThatTotal(): void
    {
        add_filter('givewp_donation_totals_max_age', '__return_zero');

        DonationTotalsCache::flush();

        DonationTotalsCache::recompute('flush-final-key', static function () {
            DonationTotalsCache::flush();

            return 1;
        });

        $this->assertSame(1, DonationTotalsCache::remember('flush-final-key', static function () {
            return 2;
        }));

        remove_filter('givewp_donation_totals_max_age', '__return_zero');
    }
}
