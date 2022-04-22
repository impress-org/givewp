<?php

namespace unit\tests\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Subscriptions\Models\Subscription;
use Give_Subscriptions_DB;

class TestDonation extends \Give_Unit_Test_Case
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
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonation()
    {
        $donor = Donor::factory()->create();

        $donation = Donation::create([
            'status' => DonationStatus::PENDING(),
            'gateway' => TestGateway::id(),
            'amount' => 50,
            'currency' => 'USD',
            'donorId' => $donor->id,
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billMurray@givewp.com',
            'formId' => 1,
        ]);

        $donationFromDatabase = Donation::find($donation->id);

        $this->assertEquals($donation->getAttributes(), $donationFromDatabase->getAttributes());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetDonor()
    {
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $this->assertInstanceOf(Donor::class, $donation->donor);
        $this->assertEquals($donor->id, $donation->donor->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetSubscription()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['donorId' => $donor->id]);

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id, 'subscriptionId' => $subscription->id]);

        $this->assertEquals($donation->subscription->id, $subscription->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDonationShouldGetSequentialId()
    {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        give()->seq_donation_number->__save_donation_title($donation->id, get_post($donation->id), false);

        $this->assertEquals(1, $donation->getSequentialId());
    }
}
