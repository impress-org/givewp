<?php

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use PHPUnit\Framework\TestCase;

final class DonationTest extends TestCase
{
    public function testDonationInsert()
    {
        $donation   = new Donation();
        $repository = new DonationRepository();

        $donation->status    = 'give_payment';
        $donation->amount    = 1000000;
        $donation->currency  = 'USD';
        $donation->gateway   = 'manual';
        $donation->donorId   = 1;
        $donation->firstName = 'Ante';
        $donation->lastName  = 'Laća';
        $donation->email     = 'ante@givewp.com';

        $newDonation = $repository->insert($donation);

        $this->assertInstanceOf(Donation::class, $newDonation);
        $this->assertEquals('give_payment', $newDonation->status);
        $this->assertEquals(1000000, $newDonation->amount);
        $this->assertEquals('USD', $newDonation->currency);
        $this->assertEquals('manual', $newDonation->gateway);
        $this->assertEquals(1, $newDonation->donorId);
        $this->assertEquals('Ante', $newDonation->firstName);
        $this->assertEquals('Laća', $newDonation->lastName);
        $this->assertEquals('ante@givewp.com', $newDonation->email);
    }
}
