<?php

namespace unit\tests\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

class TestDonation extends \Give_Unit_Test_Case
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
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
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
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldThrowExceptionIfAttributesAreNotComplete()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationMissingAmount = Donation::create([
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldThrowExceptionIfAttributeIsInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationWithWrongAmountType = Donation::create([
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'amount' => '50',
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetDonor()
    {
        $donor = $this->createDonor();
        $donation = $this->createDonation(['donorId' => $donor->id]);

        $this->assertInstanceOf(Donor::class, $donation->donor());
        $this->assertEquals($donor, $donation->donor());
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
