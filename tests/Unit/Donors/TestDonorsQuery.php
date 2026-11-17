<?php

namespace Give\Tests\Unit\Donors;

use Give\Donations\Repositories\DonationTotalsCache;
use Give\Donors\DonorsQuery;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * The donors count follows every where clause of the query, also chained ones, and a legacy donor
 * delete drops the cached count once the donor is gone.
 *
 * @since TBD
 */
class TestDonorsQuery extends TestCase
{
    use RefreshDatabase;

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
    public function tearDown(): void
    {
        remove_action('shutdown', [DonationTotalsCache::class, 'flush']);

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testChainedWhereClausesReachTheCountQuery(): void
    {
        Donor::factory()->create(['name' => 'Alice Count', 'email' => 'alice-one@example.test']);
        Donor::factory()->create(['name' => 'Alice Count', 'email' => 'alice-two@example.test']);
        Donor::factory()->create(['name' => 'Bob Count', 'email' => 'bob@example.test']);

        $query = (new DonorsQuery())
            ->where('name', 'Alice Count')
            ->where('email', 'alice-one@example.test');

        $this->assertSame(1, $query->count());
        $this->assertCount(1, $query->getAll());
    }

    /**
     * @since TBD
     */
    public function testAMethodTheCountQueryDoesNotRepeatMakesTheModelQueryCount(): void
    {
        Donor::factory()->count(2)->create();

        $query = (new DonorsQuery())->where('id', 0, '>');
        $query->groupBy('id');

        $this->assertSame(2, $query->count());
    }

    /**
     * @since TBD
     */
    public function testALegacyDonorDeleteFlushesTheTotalsAgainWhenTheRequestEnds(): void
    {
        $this->assertFalse(has_action('shutdown', [DonationTotalsCache::class, 'flush']));

        do_action('give_pre_delete_donor', 1, true, false);
        do_action('give_pre_delete_donor', 2, true, false);

        $this->assertNotFalse(has_action('shutdown', [DonationTotalsCache::class, 'flush']));
    }
}
