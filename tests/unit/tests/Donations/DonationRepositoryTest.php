<?php

namespace unit\tests\Donations;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

/**
 * @coversDefaultClass DonationRepository
 */
final class DonationRepositoryTest extends \Give_Unit_Test_Case
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
    public function testGetByIdShouldReturnDonation()
    {
        $donationFactory = $this->createDonationInstance();
        $repository = new DonationRepository();

        $insertedDonation = $repository->insert($donationFactory);

        $donation = $repository->getById($insertedDonation->id);

        $this->assertInstanceOf(Donation::class, $donation);
        $this->assertEquals($insertedDonation->id, $donation->id);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonationToDatabase()
    {
        $donation = $this->createDonationInstance();
        $repository = new DonationRepository();

        $newDonation = $repository->insert($donation);

        $query = DB::table('posts')
            ->select('ID', 'post_date', 'post_modified', 'post_status', 'post_parent')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                '_give_payment_total',
                '_give_payment_currency',
                '_give_payment_gateway',
                '_give_payment_donor_id',
                '_give_donor_billing_first_name',
                '_give_donor_billing_last_name',
                '_give_payment_donor_email'
            )
            ->where('ID', $newDonation->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(Donation::class, $newDonation);
        $this->assertEquals($query->ID, $newDonation->id);
        $this->assertEquals($query->post_status, $newDonation->status->getValue());
        $this->assertEquals($query->_give_payment_total, $newDonation->amount);
        $this->assertEquals($query->_give_payment_currency, $newDonation->currency);
        $this->assertEquals($query->_give_payment_gateway, $newDonation->gateway);
        $this->assertEquals($query->_give_payment_donor_id, $newDonation->donorId);
        $this->assertEquals($query->_give_donor_billing_first_name, $newDonation->firstName);
        $this->assertEquals($query->_give_donor_billing_last_name, $newDonation->lastName);
        $this->assertEquals($query->_give_payment_donor_email, $newDonation->email);
        $this->assertEquals($this->toDateTime($query->post_date), $newDonation->createdAt);
        $this->assertEquals($this->toDateTime($query->post_modified), $newDonation->updatedAt);
        $this->assertEquals($query->post_parent, $newDonation->parentId);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationMissingAmount = new Donation([
            'createdAt' => $this->getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->insert($donationMissingAmount);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationMissingAmount = new Donation([
            'createdAt' => $this->getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->update($donationMissingAmount);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonationValuesInTheDatabase()
    {
        $donation = $this->createDonationInstance();
        $repository = new DonationRepository();

        $newDonation = $repository->insert($donation);

        // update donation values
        $newDonation->amount = 100;
        $newDonation->firstName = "Ron";
        $newDonation->lastName = "Swanson";
        $newDonation->email = "ron@swanson.com";

        // call update method
        $repository->update($newDonation);

        $query = DB::table('posts')
            ->select('ID')
            ->attachMeta(
                'give_donationmeta',
                'ID',
                'donation_id',
                '_give_payment_total',
                '_give_donor_billing_first_name',
                '_give_donor_billing_last_name',
                '_give_payment_donor_email'
            )
            ->where('ID', $newDonation->id)
            ->get();

        // assert updated values from the database
        $this->assertNotEquals(50, $query->_give_payment_total);
        $this->assertEquals(100, $query->_give_payment_total);
        $this->assertEquals("Ron", $query->_give_donor_billing_first_name);
        $this->assertEquals("Swanson", $query->_give_donor_billing_last_name);
        $this->assertEquals("ron@swanson.com", $query->_give_payment_donor_email);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonationFromTheDatabase()
    {
        $donation = $this->createDonationInstance();
        $repository = new DonationRepository();

        $newDonation = $repository->insert($donation);

        $repository->delete($newDonation);

        $donationQuery = DB::table('posts')
            ->where('ID', $newDonation->id)
            ->get();

        $donationCoreMetaQuery =
            DB::table('give_donationmeta')
                ->where('donation_id', $newDonation->id)
                ->getAll();

        $this->assertNull($donationQuery);
        $this->assertEmpty($donationCoreMetaQuery);
    }

    /**
     * Local donation factory
     *
     * @unreleased
     *
     * @return Donation
     */
    private function createDonationInstance()
    {
        return new Donation([
            'createdAt' => $this->getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'donorId' => 1,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
            'parentId' => 0,
        ]);
    }
}
