<?php

namespace Give\Tests\Unit\Revenue\LegacyListeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Revenue\LegacyListeners\UpdateRevenueWhenDonationAmountUpdated;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.3.0
 */
class UpdateRevenueWhenDonationAmountUpdatedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.3.0
     */
    public function testRevenueIsUpdatedWhenDonationIsUpdated(): void
    {
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'amount' => Money::fromDecimal(250.00, 'USD'),
        ]);

        $donation->amount = Money::fromDecimal(25.00, 'USD');
        $donation->save();

        $listener = new UpdateRevenueWhenDonationAmountUpdated();
        $listener($donation->id);

        $this->assertEquals(
            Money::fromDecimal(25.00, 'USD')->formatToMinorAmount(),
            $this->getRevenueAmountForDonation($donation)
        );
    }

    /**
     * @since 3.3.0
     */
    private function getRevenueAmountForDonation(Donation $donation)
    {
        global $wpdb;
        $revenue = DB::get_row("SELECT * FROM {$wpdb->give_revenue} WHERE donation_id = {$donation->id}");

        return $revenue->amount;
    }
}

