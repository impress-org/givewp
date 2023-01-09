<?php

namespace Give\Tests\Unit\Subscriptions\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Repositories\SubscriptionRepository;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 2.19.6
 *
 * @coversDefaultClass SubscriptionRepository
 */
class TestSubscriptionRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnSubscription()
    {
        $donor = Donor::factory()->create();
        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);
        $repository = new SubscriptionRepository();

        $subscriptionById = $repository->getById($subscription->id);

        $this->assertInstanceOf(Subscription::class, $subscription);
        $this->assertSame($subscription->id, $subscriptionById->id);
    }

    /**
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function testInsertShouldAddSubscriptionToDatabase()
    {
        $subscriptionInstance = new Subscription(Subscription::factory()->definition());
        $repository = new SubscriptionRepository();

        $repository->insert($subscriptionInstance);

        /** @var object $subscriptionQuery */
        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $subscriptionInstance->id)
            ->get();

        $this->assertInstanceOf(Subscription::class, $subscriptionInstance);
        $this->assertEquals(
            Temporal::toDateTime($subscriptionQuery->created)->format('Y-m-d H:i:s'),
            $subscriptionInstance->createdAt->format('Y-m-d H:i:s')
        );
        $this->assertEquals(
            Temporal::toDateTime($subscriptionQuery->expiration)->format('Y-m-d H:i:s'),
            $subscriptionInstance->renewsAt->format('Y-m-d H:i:s')
        );
        $this->assertEquals($subscriptionQuery->customer_id, $subscriptionInstance->donorId);
        $this->assertEquals($subscriptionQuery->profile_id, $subscriptionInstance->gatewaySubscriptionId);
        $this->assertEquals($subscriptionQuery->product_id, $subscriptionInstance->donationFormId);
        $this->assertEquals($subscriptionQuery->period, $subscriptionInstance->period->getValue());
        $this->assertEquals($subscriptionQuery->frequency, $subscriptionInstance->frequency);
        $this->assertEquals($subscriptionQuery->initial_amount, $subscriptionInstance->amount->formatToDecimal());
        $this->assertEquals($subscriptionQuery->recurring_amount, $subscriptionInstance->amount->formatToDecimal());
        $this->assertEquals(
            $subscriptionQuery->recurring_fee_amount,
            $subscriptionInstance->feeAmountRecovered->formatToDecimal()
        );
        $this->assertEquals($subscriptionQuery->bill_times, $subscriptionInstance->installments);
        $this->assertEquals($subscriptionQuery->transaction_id, $subscriptionInstance->transactionId);
        $this->assertEquals($subscriptionQuery->status, $subscriptionInstance->status->getValue());
        $this->assertEquals($subscriptionQuery->payment_mode, $subscriptionInstance->mode->getValue());
    }

    /**
     * @since 2.19.6
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
            'donationFormId' => 1,
        ]);

        $repository = new SubscriptionRepository();

        $repository->insert($subscriptionMissingAmount);
    }

    /**
     * @since 2.19.6
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
            'donationFormId' => 1,
        ]);

        $repository = new SubscriptionRepository();

        $repository->update($subscriptionMissingAmount);
    }

    /**
     * @since 2.19.6
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

        $subscription->amount = new Money(2000, 'USD');
        $subscription->period = SubscriptionPeriod::YEAR();

        $repository->update($subscription);

        /** @var object $subscriptionQuery */
        $subscriptionQuery = DB::table('give_subscriptions')
            ->where('id', $subscription->id)
            ->get();

        $this->assertEquals(20, $subscriptionQuery->recurring_amount);
        $this->assertSame(SubscriptionPeriod::YEAR, $subscriptionQuery->period);
    }

    /**
     * @since 2.19.6
     */
    public function testShouldRetrieveInitialDonationIdForSubscription()
    {
        $donor = Donor::factory()->create();
        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);
        $repository = new SubscriptionRepository();

        $donationId = $repository->getInitialDonationId($subscription->id);

        $this->assertSame($subscription->donations[0]->id, $donationId);
    }

    /**
     * @since 2.19.6
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
