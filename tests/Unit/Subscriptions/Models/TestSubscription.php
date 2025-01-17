<?php

namespace Give\Tests\Unit\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 2.19.6
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestSubscription extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertSubscription()
    {
        $subscription = Subscription::factory()->createWithDonation();

        /** @var Subscription $subscriptionFromDatabase */
        $subscriptionFromDatabase = Subscription::find($subscription->id);

        $this->assertEquals($subscription->toArray(), $subscriptionFromDatabase->toArray());
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function testCreateRenewalShouldCreateRenewalWithAttributes(): void
    {
        $subscription = Subscription::factory()->createWithDonation();

        /** @var Subscription $subscriptionFromDatabase */
        $subscriptionFromDatabase = Subscription::find($subscription->id);
        $renewal = $subscriptionFromDatabase->createRenewal(['gatewayTransactionId' => 'transaction-id-1234']);

        $this->assertEquals('transaction-id-1234', $renewal->gatewayTransactionId);
        $this->assertEquals($subscriptionFromDatabase->id, $renewal->subscriptionId);
    }

    /**
     * @unreleased
     */
    public function testShouldCreateRenewalShouldReturnTrueWhenInstallmentsIsZero()
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0,
        ]);

        $this->assertTrue($subscription->shouldCreateRenewal());


    }

    /**
     * @unreleased
     */
    public function testShouldCreateRenewalShouldReturnTrueWhenInstallmentsIsLessThanDonations(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 3,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue($subscription->shouldCreateRenewal());
    }

    /**
     * @unreleased
     */
    public function testShouldCreateRenewalShouldReturnFalseWhenInstallmentsAreReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 2,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldCreateRenewal());
    }

    /**
     * @unreleased
     */
    public function testShouldCreateRenewalShouldReturnFalseWhenStatusIsNotActive(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'status' => SubscriptionStatus::PENDING(),
            'installments' => 0,
        ]);

        $this->assertFalse($subscription->shouldCreateRenewal());
    }

    /**
     * @unreleased
     */
    public function testShouldEndSubscriptionShouldReturnTrueWhenInstallmentsAreReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 2,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue($subscription->shouldEndSubscription());
    }

    /**
     * @unreleased
     */
    public function testShouldEndSubscriptionShouldReturnFalseWhenInstallmentsAreNotReached(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 4,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldEndSubscription());
    }

    /**
     * @unreleased
     */
    public function testShouldEndSubscriptionShouldReturnFalseWhenInstallmentsAreZero(): void
    {
        $subscription = Subscription::factory()->createWithDonation([
            'installments' => 0,
        ]);

        Subscription::factory()->createRenewal($subscription);

        $this->assertFalse($subscription->shouldEndSubscription());
    }

    /**
     * @throws Exception
     */
    public function testGetTotalDonationsShouldReturnTotalDonations(): void
    {
        $subscription = Subscription::factory()->createWithDonation();
        Subscription::factory()->createRenewal($subscription, 2);

        $this->assertEquals(3, $subscription->totalDonations());
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldGetDonations()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);

        Subscription::factory()->createRenewal($subscription, 2, ['donorId' => $donor->id]);

        // include the initial donation with renewals
        $this->assertCount(3, $subscription->donations);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldGetDonor()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->createWithDonation(['donorId' => $donor->id]);

        $this->assertSame($donor->id, $subscription->donor->id);
    }

    /**
     * @since 2.19.6
     */
    public function testIntendedAmount()
    {
        // No amount recovered yields same amount
        $subscription = Subscription::factory()->createWithDonation([
            'amount' => new Money(10000, 'USD'),
            'feeAmountRecovered' => new Money(0, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(10000, 'USD'), $subscription->intendedAmount());

        // Intended amount is amount minus fee recovered
        $subscription = Subscription::factory()->create([
            'amount' => new Money(10000, 'USD'),
            'feeAmountRecovered' => new Money(500, 'USD'),
        ]);

        self::assertMoneyEquals(new Money(9500, 'USD'), $subscription->intendedAmount());
    }

    /**
     * @since 2.19.6
     */
    public function testShouldBumpRenewalDate()
    {
        $subscription = Subscription::factory()->create([
            'frequency' => 1,
            'period' => SubscriptionPeriod::MONTH(),
            'renewsAt' => new DateTime('2020-01-01 00:00:00'),
        ]);

        $subscription->bumpRenewalDate();

        $this->assertEquals('2020-02-01 00:00:00', $subscription->renewsAt->format('Y-m-d H:i:s'));
    }

    /**
     * @since 2.19.6
     */
    public function testShouldRetrieveInitialDonationForSubscription()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $this->assertEquals($subscription->donations[0]->id, $subscription->initialDonation()->id);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldCancel()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSubscriptionShouldGetNotes()
    {
        $this->markTestIncomplete();
    }
}
