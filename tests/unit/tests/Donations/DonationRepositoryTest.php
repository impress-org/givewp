<?php

namespace unit\tests\Donations;

use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Framework\Database\Exceptions\DatabaseQueryException;
use PHPUnit\Framework\TestCase;

final class DonationRepositoryTest extends TestCase
{
    /**
     * @throws DatabaseQueryException
     */
    public function testInsertShouldAddDonationToDatabase()
    {
        $donation = new Donation(50, 'USD', 1, 'Ante', 'Laća', 'ante@givewp.com');
        $repository = new DonationRepository();
        $donation->gateway = 'manual';

        $newDonation = $repository->insert($donation);

        $this->assertInstanceOf(Donation::class, $newDonation);
        $this->assertEquals('pending', $newDonation->status->getValue());
        $this->assertEquals(50, $newDonation->amount);
        $this->assertEquals('USD', $newDonation->currency);
        $this->assertEquals('manual', $newDonation->gateway);
        $this->assertEquals(1, $newDonation->donorId);
        $this->assertEquals('Ante', $newDonation->firstName);
        $this->assertEquals('Laća', $newDonation->lastName);
        $this->assertEquals('ante@givewp.com', $newDonation->email);
    }
}
