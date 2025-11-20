<?php

namespace Give\Tests\Unit\Revenue\Listeners;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DeleteRevenueWhenDonationDeletedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testRevenueIsDeletedWhenDonationIsDeleted(): void
    {
        // Create a donation - this should automatically create a revenue entry via give_insert_payment hook
        $donation = Donation::factory()->create([
            'status' => DonationStatus::COMPLETE(),
            'amount' => Money::fromDecimal(100.00, 'USD'),
        ]);

        $donationId = $donation->id;

        // Verify revenue entry exists after donation creation
        $revenueBeforeDelete = $this->getRevenueForDonation($donationId);
        $this->assertNotNull($revenueBeforeDelete, 'Revenue entry should exist after donation creation');
        $this->assertEquals(
            Money::fromDecimal(100.00, 'USD')->formatToMinorAmount(),
            $revenueBeforeDelete->amount
        );
        $this->assertEquals($donationId, $revenueBeforeDelete->donation_id);

        // Delete the donation - this should trigger givewp_donation_deleted hook and delete revenue
        $donation->delete();

        // Verify revenue entry is deleted
        $revenueAfterDelete = $this->getRevenueForDonation($donationId);
        $this->assertNull($revenueAfterDelete, 'Revenue entry should be deleted after donation deletion');
    }

    /**
     * @unreleased
     */
    private function getRevenueForDonation(int $donationId)
    {
        global $wpdb;
        return DB::get_row(
            DB::prepare(
                "SELECT * FROM {$wpdb->give_revenue} WHERE donation_id = %d",
                $donationId
            )
        );
    }
}
