<?php

namespace unit\tests\Donors\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give_Subscriptions_DB;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestDonor extends \Give_Unit_Test_Case
{

    public function setUp()
    {
        parent::setUp();

        /** @var Give_Subscriptions_DB $legacySubscriptionDb */
        $legacySubscriptionDb = give(Give_Subscriptions_DB::class);

        $legacySubscriptionDb->create_table();
    }

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
        $subscriptionsTable = DB::prefix('give_subscriptions');
        $sequentialOrderingTable = DB::prefix('give_sequential_ordering');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $subscriptionsTable");
        DB::query("TRUNCATE TABLE $sequentialOrderingTable");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldGetDonations()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $donation1 = Donation::factory()->create(['donorId' => $donor->id, 'amount' => 100]);
        $donation2 = Donation::factory()->create(['donorId' => $donor->id, 'amount' => 200]);

        $this->assertEquals($donor->donations, [$donation1, $donation2]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldGetTotalDonations()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $donation1 = Donation::factory()->create(['donorId' => $donor->id, 'amount' => 100]);
        $donation2 = Donation::factory()->create(['donorId' => $donor->id, 'amount' => 200]);

        $this->assertEquals(2, $donor->totalDonations());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testShouldGetSubscriptions()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $subscription1 = Subscription::factory()->create(['donorId' => $donor->id]);
        $subscription2 = Subscription::factory()->create(['donorId' => $donor->id]);

        $this->assertEquals($donor->subscriptions, [$subscription1, $subscription2]);
    }
}
