<?php

namespace unit\tests\Donations\Models;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;

/**
 * @coversDefaultClass \Give\Donations\Models\Donation
 */
class TestDonation extends \Give_Unit_Test_Case
{
    use InteractsWithTime;

    public function testDonationShouldAssignProperties()
    {
        $donation = new Donation(
            [
                'amount' => 50,
                'currency' => 'USD',
                'donorId' => 1,
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'email' => 'billMurray@givewp.com',
                'status' => DonationStatus::PENDING(),
                'createdAt' => $this->getCurrentDateTime()
            ]
        );
        
        $this->assertEquals(50, $donation->amount);
        $this->assertEquals('USD', $donation->currency);
        $this->assertEquals(1, $donation->donorId);
        $this->assertEquals('Bill', $donation->firstName);
        $this->assertEquals('Murray', $donation->lastName);
        $this->assertEquals('billMurray@givewp.com', $donation->email);
        $this->assertEquals(DonationStatus::PENDING(), $donation->status);
        $this->assertEquals(
            $this->getCurrentDateTime()->format('Y-m-d H:i'),
            $donation->createdAt->format('Y-m-d H:i')
        );
    }

    public function testDonationShouldThrowExceptionWhenAssigningTheWrongValueType()
    {
        $this->expectException(InvalidArgumentException::class);

        new Donation(['status' => 'workingOnIt']);
    }

}
