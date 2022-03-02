<?php

namespace unit\tests\Subscriptions\Repositories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationMode;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
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
    use InteractsWithTime;

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
        $subscription = $this->createSubscription();
        $repository = new SubscriptionRepository();

        $subscriptionById = $repository->queryById($subscription->id);

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
        $subscriptionInstance = $this->createSubscriptionInstance();
        $repository = new SubscriptionRepository();

        /** @var Subscription $insertedSubscription */
        $insertedSubscription = $repository->insert($subscriptionInstance);

        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $insertedSubscription->id)
            ->get();

        $this->assertInstanceOf(Subscription::class, $insertedSubscription);
        $this->assertEquals($this->toDateTime($subscriptionQuery->created), $insertedSubscription->createdAt);
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
        $subscription = $this->createSubscription();
        $repository = new SubscriptionRepository();

        $subscription->amount = 200;
        $subscription->period = SubscriptionPeriod::YEAR();

        $repository->update($subscription);

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
        $subscription = $this->createSubscription();
        $repository = new SubscriptionRepository();

        $repository->delete($subscription);

        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        $this->assertNull($subscriptionQuery);
    }

    /**
     * @unreleased
     *
     * @return Subscription
     */
    private function createSubscriptionInstance()
    {
        return new Subscription([
            'id' => 1,
            'createdAt' => $this->getCurrentDateTime(),
            'amount' => 50,
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'donorId' => 1,
            'installments' => 0,
            'transactionId' => 'transaction-id',
            'feeAmount' => 0,
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
            'donationFormId' => 1
        ]);
    }

    /**
     * @unreleased
     *
     * @return Subscription
     * @throws Exception
     */
    private function createSubscription()
    {
        return Subscription::create([
            'createdAt' => $this->getCurrentDateTime(),
            'amount' => 50,
            'period' => SubscriptionPeriod::MONTH(),
            'frequency' => 1,
            'donorId' => 1,
            'installments' => 0,
            'transactionId' => 'transaction-id',
            'feeAmount' => 0,
            'status' => SubscriptionStatus::PENDING(),
            'gatewaySubscriptionId' => 'gateway-subscription-id',
            'donationFormId' => 1
        ]);
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
                'mode' => DonationMode::TEST(),
                'amount' => 50,
                'currency' => 'USD',
                'donorId' => 1,
                'firstName' => 'Bill',
                'lastName' => 'Murray',
                'email' => 'billMurray@givewp.com',
                'formId' => 1,
                'formTitle' => 'Form Title',
            ], $attributes)
        );
    }

    /**
     * @unreleased
     *
     * @return Subscription
     * @throws Exception
     */
    private function createSubscriptionAndInitialDonation()
    {
        $subscription = $this->createSubscription();

        $donation = $this->createDonation();

        $repository = new SubscriptionRepository();

        $repository->updateLegacyColumns($subscription->id, ['parent_payment_id' => $donation->id]);

        return $subscription;
    }

    /**
     * @unreleased
     *
     * @return Donation
     * @throws Exception
     */
    private function createRenewal(Subscription $subscription)
    {
        return $this->createDonation([
            'status' => DonationStatus::RENEWAL(),
            'subscriptionId' => $subscription->id,
            // initial donation ID 'parent_payment_id'
            'parentId' => give()->subscriptions->getInitialDonationId($subscription->id),
        ]);
    }
}
