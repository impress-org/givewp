<?php

namespace unit\tests\Subscriptions\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give_Subscriptions_DB;
use Give_Unit_Test_Case;

/**
 * @unreleased
 *
 * @coversDefaultClass SubscriptionRepository
 */
class TestSubscriptionRepository extends Give_Unit_Test_Case
{

    public function setUp()
    {
        parent::setUp();

        /** @var Give_Subscriptions_DB $legacySubscriptionDb */
        $legacySubscriptionDb = give(Give_Subscriptions_DB::class);

        $legacySubscriptionDb->create_table();
    }

    /**
     * @unreleased - truncate donationMetaTable to avoid duplicate records
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationMetaTable = DB::prefix('give_donationmeta');
        $subscriptionsTable = DB::prefix('give_subscriptions');

        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $subscriptionsTable");
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnSubscription()
    {
        $donor = Donor::factory()->create();
        $subscription = Subscription::factory()->create(['donorId' => $donor->id]);
        $repository = new SubscriptionRepository();

        $subscriptionById = $repository->getById($subscription->id);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertEquals($subscriptionById, $subscription);
    }

    /**
     * @unreleased
     *
     * @return void
     * @throws Exception
     */
    public function testInsertShouldAddSubscriptionToDatabase()
    {
        $subscriptionInstance = new Subscription(Subscription::factory()->definition());
        $repository = new SubscriptionRepository();

        $insertedSubscription = $repository->insert($subscriptionInstance);

        /** @var object $subscriptionQuery */
        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $insertedSubscription->id)
            ->get();

        $this->assertInstanceOf(Subscription::class, $insertedSubscription);
        $this->assertEquals(Temporal::toDateTime($subscriptionQuery->created), $insertedSubscription->createdAt);
        $this->assertEquals($subscriptionQuery->customer_id, $insertedSubscription->donorId);
        $this->assertEquals($subscriptionQuery->profile_id, $insertedSubscription->gatewaySubscriptionId);
        $this->assertEquals($subscriptionQuery->product_id, $insertedSubscription->donationFormId);
        $this->assertEquals($subscriptionQuery->period, $insertedSubscription->period->getValue());
        $this->assertEquals($subscriptionQuery->frequency, $insertedSubscription->frequency);
        $this->assertEquals($subscriptionQuery->initial_amount, $insertedSubscription->amount);
        $this->assertEquals($subscriptionQuery->recurring_amount, $insertedSubscription->amount);
        $this->assertEquals($subscriptionQuery->recurring_fee_amount, $insertedSubscription->feeAmount);
        $this->assertEquals($subscriptionQuery->bill_times, $insertedSubscription->installments);
        $this->assertEquals($subscriptionQuery->transaction_id, $insertedSubscription->transactionId);
        $this->assertEquals($subscriptionQuery->status, $insertedSubscription->status->getValue());
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

        $subscriptionMissingAmount = new Subscription([
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'donorId' => 1,
            'transactionId' => 'transaction-id',
            'status' => SubscriptionStatus::PENDING(),
            'donationFormId' => 1
        ]);

        $repository = new SubscriptionRepository();

        $repository->insert($subscriptionMissingAmount);
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

        $subscriptionMissingAmount = new Subscription([
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'donorId' => 1,
            'transactionId' => 'transaction-id',
            'status' => SubscriptionStatus::PENDING(),
            'donationFormId' => 1
        ]);

        $repository = new SubscriptionRepository();

        $repository->update($subscriptionMissingAmount);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateValuesInTheDatabase()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();
        $repository = new SubscriptionRepository();

        $subscription->amount = 200;
        $subscription->period = SubscriptionPeriod::YEAR();

        $repository->update($subscription);

        /** @var object $subscriptionQuery */
        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        $this->assertEquals(200, $subscriptionQuery->recurring_amount);
        $this->assertEquals(SubscriptionPeriod::YEAR, $subscriptionQuery->period);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveSubscriptionFromTheDatabase()
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create();
        $repository = new SubscriptionRepository();

        $repository->delete($subscription);

        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        $this->assertNull($subscriptionQuery);
    }
}
