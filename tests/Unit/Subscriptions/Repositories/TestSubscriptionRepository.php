<?php

namespace Give\Tests\Unit\Subscriptions\Repositories;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Actions\GenerateNextRenewalForSubscription;
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

    /**
     * @unreleased
     * @throws Exception
     */
    public function testCreateRenewalShouldCreateNewRenewal(): void
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionRepository();

        $renewalCreatedAt = Temporal::getCurrentDateTime();
        $gatewayTransactionId = 'transaction-id';

        $renewal = $repository->createRenewal($subscription, [
            'gatewayTransactionId' => $gatewayTransactionId,
            'createdAt' => $renewalCreatedAt,
        ]);

        $nextRenewalDate = (new GenerateNextRenewalForSubscription())(
            $subscription->period,
            $subscription->frequency,
            $subscription->renewsAt
        );

        $initialDonation = $subscription->initialDonation();

        $this->assertCount(2, $subscription->donations);
        $this->assertTrue($renewal->status->isComplete());
        $this->assertTrue($renewal->type->isRenewal());
        $this->assertSame($subscription->id, $renewal->subscriptionId);
        $this->assertSame($subscription->gatewayId, $renewal->gatewayId);
        $this->assertSame($subscription->donorId, $renewal->donorId);
        $this->assertSame($subscription->donationFormId, $renewal->formId);
        $this->assertTrue($renewal->type->isRenewal());
        $this->assertTrue($renewal->status->isComplete());
        $this->assertSame($gatewayTransactionId, $renewal->gatewayTransactionId);
        $this->assertSame($initialDonation->honorific, $renewal->honorific);
        $this->assertSame($initialDonation->firstName, $renewal->firstName);
        $this->assertSame($initialDonation->lastName, $renewal->lastName);
        $this->assertSame($initialDonation->email, $renewal->email);
        $this->assertSame($initialDonation->phone, $renewal->phone);
        $this->assertSame($initialDonation->anonymous, $renewal->anonymous);
        $this->assertSame($initialDonation->levelId, $renewal->levelId);
        $this->assertSame($initialDonation->company, $renewal->company);
        $this->assertSame($subscription->feeAmountRecovered, $renewal->feeAmountRecovered);
        $this->assertSame($initialDonation->exchangeRate, $renewal->exchangeRate);
        $this->assertSame($initialDonation->formTitle, $renewal->formTitle);
        $this->assertSame($subscription->mode->getValue(), $renewal->mode->getValue());
        $this->assertSame($initialDonation->donorIp, $renewal->donorIp);
        $this->assertSame($initialDonation->billingAddress->toArray(), $renewal->billingAddress->toArray());
        $this->assertSame($renewalCreatedAt->getTimestamp(), $renewal->createdAt->getTimestamp());
        $this->assertSame($subscription->renewsAt->getTimestamp(), $nextRenewalDate->getTimestamp());
    }
}
