<?php

namespace Give\Tests\Unit\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Revenue\Listeners\UpdateRevenueWhenDonationAmountUpdated;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 2.20.1
 */
class UpdateRevenueWhenDonationAmountUpdatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased updated action to accept Donation model
     * @since 2.20.1
     */
    public function testRevenueIsUpdatedWhenDonationIsUpdated()
    {
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'amount' => Money::fromDecimal(250.00, 'USD'),
        ]);

        $donation->amount = Money::fromDecimal(25.00, 'USD');
        $donation->save();

        $listener = new UpdateRevenueWhenDonationAmountUpdated();
        $listener->__invoke($donation);

        $this->assertEquals(
            Money::fromDecimal(25.00, 'USD')->formatToMinorAmount(),
            $this->getRevenueAmountForDonation($donation)
        );
    }

    /**
     * @param  Donation  $donation
     *
     * @return int
     */
    private function getRevenueAmountForDonation(Donation $donation)
    {
        global $wpdb;
        $revenue = DB::get_row("SELECT * FROM {$wpdb->give_revenue} WHERE donation_id = {$donation->id}");

        return $revenue->amount;
    }
}

