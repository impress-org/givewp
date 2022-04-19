<?php

namespace unit\tests\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give_Unit_Test_Case;

/**
 * @unreleased
 *
 * @coversDefaultClass SubscriptionRepository
 */
class TestDonorRepository extends Give_Unit_Test_Case
{

    /**
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnDonor()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $repository = new DonorRepository();

        /** @var Donor $donorFromRepository */
        $donorFromRepository = $repository->queryById($donor->id)->get();

        /** @var Donor $donorQuery */
        $donorQuery = $repository->prepareQuery()
            ->where('id', $donorFromRepository->id)
            ->get();

        $this->assertEquals($donor->id, $donorQuery->id);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonorToDatabase()
    {
        $donor = new Donor(Donor::factory()->definition());
        $donor->additionalEmails = ["chrisFarley2@givewp.com", "chrisFarley3@givewp.com"];

        $repository = new DonorRepository();

        $repository->insert($donor);

        /** @var Donor $query */
        $query = $repository->prepareQuery()
            ->where('id', $donor->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(Donor::class, $donor);
        $this->assertEquals($query->createdAt->format('Y-m-d H:i:s'), $donor->createdAt->format('Y-m-d H:i:s'));
        $this->assertEquals($query->id, $donor->id);
        $this->assertEquals($query->name, $donor->name);
        $this->assertEquals($query->firstName, $donor->firstName);
        $this->assertEquals($query->lastName, $donor->lastName);
        $this->assertEquals($query->email, $donor->email);
        $this->assertEquals($query->additionalEmails, $donor->additionalEmails);
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

        $donorMissingFirstName = new Donor([
            'name' => 'Bill Murray',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonorRepository();

        $repository->insert($donorMissingFirstName);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonorValuesInTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();
        $repository = new DonorRepository();

        $donor->firstName = "Chris";
        $donor->lastName = "Farley";
        $donor->additionalEmails = ["chrisFarley2@givewp.com", "chrisFarley3@givewp.com"];

        $repository->update($donor);

        /** @var object $query */
        $query = DB::table('give_donors')
            ->select('*')
            ->attachMeta(
                'give_donormeta',
                'ID',
                'donor_id',
                ['_give_donor_first_name', 'firstName'],
                ['_give_donor_last_name', 'lastName'],
                ['additional_email', 'additionalEmails', true]
            )
            ->where('id', $donor->id)
            ->get();

        // assert updated values from the database
        $this->assertEquals("Chris", $query->firstName);
        $this->assertEquals("Farley", $query->lastName);
        $this->assertEquals($donor->additionalEmails, json_decode($query->additionalEmails, true));
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

        $donorMissingFirstName = new Donor([
            'name' => 'Bill Murray',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
        ]);

        $repository = new DonorRepository();

        $repository->update($donorMissingFirstName);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonorFromTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $repository = new DonorRepository();

        $repository->delete($donor);

        $donorQuery = $repository->prepareQuery()
            ->where('id', $donor->id)
            ->get();

        $donorMetaQuery =
            DB::table('give_donormeta')
                ->where('donor_id', $donor->id)
                ->getAll();

        $this->assertNull($donorQuery);
        $this->assertEmpty($donorMetaQuery);
    }
}
