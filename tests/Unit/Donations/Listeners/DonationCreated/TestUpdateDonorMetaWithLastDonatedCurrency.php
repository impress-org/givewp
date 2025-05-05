<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Donations\Listeners\DonationCreated;

use Give\Donations\Listeners\DonationCreated\UpdateDonorMetaWithLastDonatedCurrency;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @coversDefaultClass UpdateDonorMetaWithLastDonatedCurrency
 */
class TestUpdateDonorMetaWithLastDonatedCurrency extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.2.0
     */
    public function testShouldUpdateDonorMetaWithLastDonatedCurrency()
    {
        $donor = Donor::factory()->create();

        $donation = Donation::factory()->create([
            'donorId' => $donor->id,
            'amount' => new Money(100, 'EUR'),
        ]);

        $action = new UpdateDonorMetaWithLastDonatedCurrency();
        $action($donation);

        $this->assertEquals('EUR', give()->donor_meta->get_meta($donor->id, '_give_cs_currency', true));
    }
}
