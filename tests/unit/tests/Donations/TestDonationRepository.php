<?php

namespace unit\tests\Donations;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Repositories\DonationRepository;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;

/**
 * @coversDefaultClass DonationRepository
 */
final class TestDonationRepository extends \Give_Unit_Test_Case
{

    /**
     * @unreleased - truncate donationMetaTable to avoid duplicate records
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donationsTable = DB::prefix('posts');
        $donorsTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');

        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $donorsTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
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
        $donor = Donor::factory()->create();
        $donationFactory = Donation::factory()->create();
        $repository = new DonationRepository();

        $donation = $repository->getById($donationFactory->id);

        $this->assertInstanceOf(Donation::class, $donation);
        $this->assertEquals($donationFactory->id, $donation->id);
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
        $donor = Donor::factory()->create();
        $donation = new Donation(Donation::factory()->definition());

        $repository = new DonationRepository();

        /** @var Donation $newDonation */
        $newDonation = $repository->insert($donation);

        /** @var object $query */
        $query = $repository->prepareQuery()
            ->where('ID', $newDonation->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(Donation::class, $newDonation);
        $this->assertEquals($query->id, $newDonation->id);
        $this->assertEquals($query->status, $newDonation->status->getValue());
        $this->assertEquals($query->amount, $newDonation->amount);
        $this->assertEquals($query->currency, $newDonation->currency);
        $this->assertEquals($query->gateway, $newDonation->gateway);
        $this->assertEquals($query->donorId, $newDonation->donorId);
        $this->assertEquals($query->firstName, $newDonation->firstName);
        $this->assertEquals($query->lastName, $newDonation->lastName);
        $this->assertEquals($query->email, $newDonation->email);
        $this->assertEquals($query->createdAt, $newDonation->createdAt);
        $this->assertEquals($query->parentId, $newDonation->parentId);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationMissingAmount = new Donation([
            'createdAt' => Temporal::getCurrentDateTime(),
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
    public function testInsertShouldFailValidationWhenDonorDoesNotExistAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donationWithInvalidDonor = new Donation([
            'createdAt' => Temporal::getCurrentDateTime(),
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'currency' => 'USD',
            'amount' => 50,
            'formId' => 1,
            'donorId' => 2,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonationRepository();

        $repository->insert($donationWithInvalidDonor);
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
            'createdAt' => Temporal::getCurrentDateTime(),
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
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $repository = new DonationRepository();

        // update donation values
        $donation->amount = 100;
        $donation->firstName = "Ron";
        $donation->lastName = "Swanson";
        $donation->email = "ron@swanson.com";

        // call update method
        $repository->update($donation);

        /** @var object $query */
        $query = $repository->prepareQuery()
            ->where('ID', $donation->id)
            ->get();

        // assert updated values from the database
        $this->assertNotEquals(50, $query->amount);
        $this->assertEquals(100, $query->amount);
        $this->assertEquals("Ron", $query->firstName);
        $this->assertEquals("Swanson", $query->lastName);
        $this->assertEquals("ron@swanson.com", $query->email);
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
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $repository = new DonationRepository();

        $repository->delete($donation);

        $donationQuery = $repository->prepareQuery()
            ->where('ID', $donation->id)
            ->get();

        $donationCoreMetaQuery =
            DB::table('give_donationmeta')
                ->where('donation_id', $donation->id)
                ->getAll();

        $this->assertNull($donationQuery);
        $this->assertEmpty($donationCoreMetaQuery);
    }
}
