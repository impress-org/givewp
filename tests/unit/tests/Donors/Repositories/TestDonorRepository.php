<?php

namespace unit\tests\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorRepository;
use Give\Framework\Database\DB;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give_Unit_Test_Case;

/**
 * @unreleased
 *
 * @coversDefaultClass SubscriptionRepository
 */
class TestDonorRepository extends Give_Unit_Test_Case
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
        $donor = $this->createDonor();

        $repository = new DonorRepository();

        $donorFromRepository = $repository->getById($donor->id);

        $donorQuery = DB::table('give_donors')
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
        $donor = $this->createDonorInstance();
        $repository = new DonorRepository();

        $newDonor = $repository->insert($donor);

        $query = DB::table('give_donors')
            ->select('*')
            ->attachMeta('give_donormeta',
                'ID',
                'donor_id',
                ['_give_donor_first_name', 'firstName'],
                ['_give_donor_last_name', 'lastName']
            )
            ->where('id', $newDonor->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(Donor::class, $newDonor);
        $this->assertEquals($this->toDateTime($query->date_created), $newDonor->createdAt);
        $this->assertEquals($query->id, $newDonor->id);
        $this->assertEquals($query->name, $newDonor->name);
        $this->assertEquals($query->firstName, $newDonor->firstName);
        $this->assertEquals($query->lastName, $newDonor->lastName);
        $this->assertEquals($query->email, $newDonor->email);
    }

    /**
     * @unreleased
     *
     * @return Donor
     */
    private function createDonorInstance()
    {
        return new Donor([
            'createdAt' => $this->getCurrentDateTime(),
            'name' => 'Bill Murray',
            'firstName' => 'Bill',
            'lastName' => 'Bill Murray',
            'email' => 'billMurray@givewp.com'
        ]);
    }

    /**
     * @unreleased
     *
     * @return Donor
     *
     * @throws Exception
     */
    private function createDonor()
    {
        return Donor::create([
            'createdAt' => $this->getCurrentDateTime(),
            'name' => 'Bill Murray',
            'firstName' => 'Bill',
            'lastName' => 'Bill Murray',
            'email' => 'billMurray@givewp.com'
        ]);
    }
}
