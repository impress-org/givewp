<?php

namespace unit\tests\Donations\Models;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Models\Traits\InteractsWithTime;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Give\Donations\Models\Donation
 */
class TestDonation extends TestCase
{
    use InteractsWithTime;

    public function testDonationShouldHaveDefaultProperties()
    {
        $donation = new Donation(50, 'USD', 1, 'Bill', 'Murray', 'billMurray@givewp.com');

        $this->assertEquals([
            null,
            $this->getCurrentDateTime()->format( 'Y-m-d H:i' ),
            null,
            DonationStatus::PENDING(),
            50,
            "USD",
            null,
            1,
            "Bill",
            "Murray",
            "billMurray@givewp.com",
            0,
            null
        ],
        [
            $donation->id,
            $donation->createdAt->format( 'Y-m-d H:i' ),
            $donation->updatedAt,
            $donation->status,
            $donation->amount,
            $donation->currency,
            $donation->gateway,
            $donation->donorId,
            $donation->firstName,
            $donation->lastName,
            $donation->email,
            $donation->parentId,
            $donation->subscriptionId
            ]
        );
    }

    public function testDefaultDonationPropertiesShouldHaveCorrectTypes()
    {
        $donation = new Donation(50, 'USD', 1, 'Bill', 'Murray', 'billMurray@givewp.com');

        $this->assertInstanceOf(DonationStatus::class, $donation->status);
        $this->assertInstanceOf(\DateTime::class, $donation->createdAt);
    }

}
