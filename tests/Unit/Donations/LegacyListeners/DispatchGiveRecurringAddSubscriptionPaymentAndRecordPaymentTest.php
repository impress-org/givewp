<?php

namespace GiveTests\Unit\Donations\LegacyListeners;

use Give\Subscriptions\Models\Subscription;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class DispatchGiveRecurringAddSubscriptionPaymentAndRecordPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     * @return void
     */
    public function testShouldNotTriggerActionHookForSubscriptionInitialDonation()
    {

        Subscription::factory()->createWithDonation();

        $this->assertFalse((bool)did_action('give_recurring_add_subscription_payment'));
        $this->assertFalse((bool)did_action('give_recurring_record_payment'));
    }

    /**
     * @unreleased
     * @return void
     */
    public function testShouldTriggerActionHookForSubscriptionRenewalDonation()
    {
        $subscription = Subscription::factory()->createWithDonation();
        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue((bool)did_action('give_recurring_add_subscription_payment'));
        $this->assertTrue((bool)did_action('give_recurring_record_payment'));
    }
}
