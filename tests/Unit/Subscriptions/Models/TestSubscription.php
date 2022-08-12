<?php

namespace GiveTests\Unit\Subscriptions\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donors\Models\Donor;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
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
        $subscription = Subscription::factory()->create();

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

        /** @var Subscription $subscription */
        $subscription = Subscription::factory()->create(['donorId' => $donor->id]);

        /** @var Donation $donation */
        $renewal1 = Subscription::factory()->createRenewal($subscription->id, ['donorId' => $donor->id]);
        $renewal2 = Subscription::factory()->createRenewal($subscription->id, ['donorId' => $donor->id]);

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
        $subscription = Subscription::factory()->create(['donorId' => $donor->id]);

        $this->assertSame($donor->id, $subscription->donor->id);
    }

    /**
     * @unreleased
     */
    public function testIntendedAmount()
    {
        // No amount recovered yields same amount
        $subscription = Subscription::factory()->create([
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
     * @return void
     */
    public function testSubscriptionShouldGetNotes()
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSubscriptionShouldCancel()
    {
        $this->markTestIncomplete();
    }
}
