<?php

namespace Give\Tests\Unit\Donations\LegacyListeners;

use Give\Subscriptions\Models\Subscription;
use Give_Payment;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use InvalidArgumentException;

/**
 * @since 2.23.2
 */
class DispatchGiveRecurringAddSubscriptionPaymentAndRecordPaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.23.2
     * @return void
     */
    public function testShouldNotTriggerActionHookForSubscriptionInitialDonation()
    {
        Subscription::factory()->createWithDonation();

        $this->assertFalse((bool)did_action('give_recurring_add_subscription_payment'));
        $this->assertFalse((bool)did_action('give_recurring_record_payment'));
    }

    /**
     * @since 2.23.2
     * @return void
     */
    public function testShouldTriggerActionHookForSubscriptionRenewalDonation()
    {
        $subscription = Subscription::factory()->createWithDonation();
        Subscription::factory()->createRenewal($subscription);

        $this->assertTrue((bool)did_action('give_recurring_add_subscription_payment'));
        $this->assertTrue((bool)did_action('give_recurring_record_payment'));
    }

    /**
     * @since 2.23.2
     * @return void
     */
    public function testActionHookShouldHaveDonationAmountWithFloatType()
    {
        add_action(
            'give_recurring_add_subscription_payment',
            function (Give_Payment $payment) {
                $tempPayment = new Give_Payment($payment->ID);

                if ($tempPayment->total !== $payment->total) {
                    throw new InvalidArgumentException();
                }
            },
            10,
            2
        );

        add_action(
            'give_recurring_record_payment',
            function (
                Give_Payment $payment,
                $parentDonationId,
                $donationAmount
            ) {
                $tempPayment = new Give_Payment($payment->ID);

                if ($tempPayment->total !== $donationAmount) {
                    throw new InvalidArgumentException();
                }
            },
            10,
            3
        );

        $subscription = Subscription::factory()->createWithDonation();
        Subscription::factory()->createRenewal($subscription);
    }
}
