<?php

namespace Give\Tests\Unit\Donors;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Donor_Stats;

/**
 * Covers the donation meta query of Give_Donor_Stats.
 *
 * @since TBD
 */
class LegacyDonorStatsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    private function donate(Donor $donor, int $cents, bool $anonymous = false): void
    {
        Donation::factory()->create([
            'donorId' => $donor->id,
            'status' => DonationStatus::COMPLETE(),
            'amount' => new Money($cents, 'USD'),
            'anonymous' => $anonymous,
        ]);
    }

    /**
     * @since TBD
     */
    public function testDonatedAddsUpTheDonationsOfOneDonor()
    {
        $donor = Donor::factory()->create();
        $other = Donor::factory()->create();

        $this->donate($donor, 1000);
        $this->donate($donor, 2550);
        $this->donate($other, 9999);

        $this->assertEquals(35.5, Give_Donor_Stats::donated(['donor' => $donor->id]));
    }

    /**
     * @since TBD
     */
    public function testDonatedSkipsAnonymousDonations()
    {
        $donor = Donor::factory()->create();

        $this->donate($donor, 1000);
        $this->donate($donor, 2000, true);

        $this->assertEquals(10, Give_Donor_Stats::donated(['donor' => $donor->id]));
    }

    /**
     * @since TBD
     */
    public function testDonatedIsZeroForADonorWithoutDonations()
    {
        $donor = Donor::factory()->create();

        $this->assertSame(0, Give_Donor_Stats::donated(['donor' => $donor->id]));
    }

    /**
     * @since TBD
     */
    public function testDonatedIsZeroWithoutADonor()
    {
        $this->assertSame(0, Give_Donor_Stats::donated([]));
    }
}
