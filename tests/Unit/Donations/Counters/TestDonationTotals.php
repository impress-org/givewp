<?php

namespace Give\Tests\Unit\Donations\Counters;

use Give\Donations\Counters\DonationTotals;
use Give\Tests\TestCase;

/**
 * @since TBD
 */
class TestDonationTotals extends TestCase
{
    /**
     * @var array<int, array{0: string, 1: callable}>
     */
    private $filters = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->deleteTotalTransients();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        foreach ($this->filters as list($hook, $callback)) {
            remove_filter($hook, $callback, 99);
        }

        $this->filters = [];
        $this->deleteTotalTransients();

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testACountedValueIsReturnedWithoutComputing(): void
    {
        $compute = $this->counter($computed);

        $this->assertSame(7, DonationTotals::resolve('totals-counted', $compute, 7));
        $this->assertSame(0, $computed);
    }

    /**
     * @since TBD
     */
    public function testAStoredTotalIsReturnedWithoutComputing(): void
    {
        $compute = $this->counter($computed);
        set_transient($this->transientName('totals-hit'), 41, 60);

        $this->assertSame(41, DonationTotals::resolve('totals-hit', $compute));
        $this->assertSame(0, $computed);
    }

    /**
     * @since TBD
     */
    public function testAMissComputesOnceAndStoresTheTotal(): void
    {
        $compute = $this->counter($computed);

        $this->assertSame(1, DonationTotals::resolve('totals-miss', $compute));
        $this->assertSame(1, DonationTotals::resolve('totals-miss', $compute));
        $this->assertSame(1, $computed);
        $this->assertSame(1, get_transient($this->transientName('totals-miss')));
    }

    /**
     * @since TBD
     */
    public function testATtlOfZeroComputesEveryTimeAndStoresNothing(): void
    {
        $this->addTestFilter('givewp_donation_totals_ttl', '__return_zero');
        $compute = $this->counter($computed);

        $this->assertSame(1, DonationTotals::resolve('totals-off', $compute));
        $this->assertSame(2, DonationTotals::resolve('totals-off', $compute));
        $this->assertFalse(get_transient($this->transientName('totals-off')));
    }

    /**
     * @since TBD
     */
    public function testALiveTotalIsComputedEveryTimeAndRefreshesTheTransient(): void
    {
        $compute = $this->counter($computed);
        set_transient($this->transientName('totals-live'), 99, 60);

        $this->assertSame(1, DonationTotals::resolve('totals-live', $compute, null, true));
        $this->assertSame(2, DonationTotals::resolve('totals-live', $compute, null, true));
        $this->assertSame(2, DonationTotals::resolve('totals-live', $compute), 'the next read sees the refreshed value');
        $this->assertSame(2, $computed);
    }

    /**
     * @since TBD
     */
    public function testTheTtlFilterSetsHowLongTheTotalIsKept(): void
    {
        $this->addTestFilter('givewp_donation_totals_ttl', static function () {
            return 5;
        });

        DonationTotals::resolve('totals-ttl', $this->counter($computed));

        $timeout = (int)get_option('_transient_timeout_' . $this->transientName('totals-ttl'));
        $this->assertGreaterThan(time(), $timeout);
        $this->assertLessThanOrEqual(time() + 5, $timeout);
    }

    /**
     * @since TBD
     */
    private function addTestFilter(string $hook, callable $callback): void
    {
        add_filter($hook, $callback, 99);
        $this->filters[] = [$hook, $callback];
    }

    /**
     * @since TBD
     */
    private function counter(&$computed): callable
    {
        $computed = 0;

        return static function () use (&$computed) {
            return ++$computed;
        };
    }

    /**
     * @since TBD
     */
    private function transientName(string $key): string
    {
        return DonationTotals::TRANSIENT_PREFIX . md5($key);
    }

    /**
     * @since TBD
     */
    private function deleteTotalTransients(): void
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like('_transient_' . DonationTotals::TRANSIENT_PREFIX) . '%',
            $wpdb->esc_like('_transient_timeout_' . DonationTotals::TRANSIENT_PREFIX) . '%'
        ));
        wp_cache_flush();
    }
}
