<?php

namespace GiveTests\Unit\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Revenue\Listeners\UpdateRevenueWhenDonationAmountUpdated;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class UpdateRevenueWhenDonationAmountUpdatedTest extends TestCase
{
    use RefreshDatabase;

    public function testRevenueIsUpdatedWhenDonationIsUpdated()
    {
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'amount' => Money::fromDecimal(250.00, 'USD'),
        ]);

        $donation->amount = Money::fromDecimal(25.00, 'USD');
        $donation->save();

        $listener = new UpdateRevenueWhenDonationAmountUpdated();
        $listener->__invoke($donation->id);

        $this->assertEquals(
            Money::fromDecimal(25.00, 'USD')->formatToMinorAmount(),
            $this->getRevenueAmountForDonation($donation)
        );
    }

    /**
     * @param Donation $donation
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

