<?php

namespace Give\Tests\Unit\DonorDashboards\Repositories;

use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\DonorDashboards\Repositories\Donations;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use RuntimeException;

/**
 * @since 4.20.0
 */
final class TestDonations extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.20.0
     */
    public function testGetDonationsKeepsOtherDonationsWhenOneReceiptFails(): void
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $donations = Donation::factory()->count(2)->create(['donorId' => $donor->id]);
        $failingDonationId = $donations[0]->id;

        $throwForFailingDonation = static function ($receipt) use ($failingDonationId) {
            if ($receipt->donationId === $failingDonationId) {
                throw new RuntimeException('Receipt failure');
            }
        };
        add_action('give_new_receipt', $throwForFailingDonation);

        $result = (new Donations())->getDonations($donor->id);

        remove_action('give_new_receipt', $throwForFailingDonation);

        $receiptsByDonationId = array_column($result, 'receipt', 'id');

        $this->assertCount(count($donations), $result);
        $this->assertSame([], $receiptsByDonationId[$failingDonationId]);
        $this->assertNotEmpty($receiptsByDonationId[$donations[1]->id]);
    }
}
