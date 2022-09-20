<?php

namespace GiveTests\Unit\Subscriptions\Models;

use DateTime;
use Exception;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestSubscription extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
        $subscription = Subscription::factory()->createWithDonation();

        $subscription->cancel();

        $this->assertTrue($subscription->status->isCancelled());
    }

    /**
     * @return void
     */
    public function testSubscriptionShouldGetNotes()
    {
        $this->markTestIncomplete();
    }
}
