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
        $donorMetaTable = DB::prefix('give_donormeta');

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
        $donorInstance = $this->createDonorInstance();
        $repository = new DonorRepository();

        DB::table('give_donors')
            ->insert([
                'id' => $donorInstance->id,
                'user_id' => $donorInstance->id,
                'email' => $donorInstance->email,
                'name' => $donorInstance->email,
                'date_created' => $this->getFormattedDateTime($donorInstance->createdAt)
            ]);

        $donor = $repository->getById($donorInstance->id);

        $donorQuery = DB::table('give_donors')
            ->where('id', $donorInstance->id)
            ->get();

        $this->assertEquals($donor->id, $donorQuery->id);
    }

    /**
     * @unreleased
     *
     * @return Donor
     */
    private function createDonorInstance()
    {
        return new Donor([
            'id' => 2,
            'userId' => 2,
            'createdAt' => $this->getCurrentDateTime(),
            'name' => 'Bill Murray',
            'email' => 'billMurray@givewp.com'
        ]);
    }
}
