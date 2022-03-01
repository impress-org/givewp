<?php

namespace unit\tests\Donors\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestDonor extends \Give_Unit_Test_Case
{
    use InteractsWithTime;


    /**
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldGetDonations()
    {
        $donor = $this->createDonor();

        $donation1 = $this->createDonation(['donorId' => $donor->id, 'amount' => 100]);
        $donation2 = $this->createDonation(['donorId' => $donor->id, 'amount' => 200]);

        $this->assertEquals($donor->donations()->getAll(), [$donation1, $donation2]);
    }

    /**
     * @return void
     */
    public function testShouldGetSubscriptions()
    {
        $this->markTestIncomplete();
    }

    /**
     * @unreleased
     *
     * @param  array  $attributes
     *
     * @return Donation
     *
     * @throws Exception
     */
    private function createDonation(array $attributes = [])
    {
        return Donation::create(
            array_merge([
                'status' => DonationStatus::PENDING(),
                'gateway' => TestGateway::id(),
                'amount' => 50,
                'currency' => 'USD',
                'donorId' => 1,
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'email' => 'billMurray@givewp.com',
                'formId' => 1
            ], $attributes)
        );
    }

    /**
     * @unreleased
     *
     * @param  array  $attributes
     *
     * @return Donor
     *
     * @throws Exception
     */
    private function createDonor(array $attributes = [])
    {
        return Donor::create(
            array_merge([
                'createdAt' => $this->getCurrentDateTime(),
                'name' => 'Bill Murray',
                'firstName' => 'Bill',
                'lastName' => 'Bill Murray',
                'email' => 'billMurray@givewp.com'
            ], $attributes)
        );
    }
}
