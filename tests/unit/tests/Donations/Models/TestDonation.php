<?php

namespace unit\tests\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

class TestDonation extends \Give_Unit_Test_Case
{
    use InteractsWithTime;

    /**
     * @unreleased - truncate donationMetaTable to avoid duplicate records
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationMetaTable = DB::prefix('give_donationmeta');

        DB::query("TRUNCATE TABLE $donationMetaTable");
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonation()
    {
        // TODO: have this mock repository and expect insert method
        $donation = Donation::create([
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        /** @var Donation $donationFromDatabase */
        $donationFromDatabase = $repository->getById($donation->id);

        $this->assertEquals($donation->getAttributes(), $donationFromDatabase->getAttributes());
    }

    /**
     * @return void
     */
    public function testDonationShouldGetDonor()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testDonationShouldGetSubscriptions()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testDonationShouldGetSequentialId()
    {
        $this->markTestIncomplete();
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    private function createDonation()
    {
        return Donation::create([
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);
    }
}
