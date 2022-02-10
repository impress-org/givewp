<?php

namespace unit\tests\Subscriptions\Models;

use DateTime;
use Give\Framework\Models\Traits\InteractsWithTime;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use PHPUnit\Framework\TestCase;

/**
 * @unreleased
 *
 * @coversDefaultClass \Give\Subscriptions\Models\Subscription
 */
class TestSubscription extends TestCase
{
    use InteractsWithTime;

    /**
     * @unreleased
     *
     * @return void
     */
    public function testSubscriptionShouldHaveDefaultProperties()
    {
        $subscription = $this->createSubscriptionInstance();

        $this->assertEquals(null, $subscription->id);
        $this->assertEquals($this->getCurrentDateTime()->format( 'Y-m-d H:i' ), $subscription->createdAt->format( 'Y-m-d H:i' ));
        $this->assertEquals(null, $subscription->expiresAt);
        $this->assertEquals(50, $subscription->amount);
        $this->assertEquals(SubscriptionPeriod::MONTH(), $subscription->period);
        $this->assertEquals(1, $subscription->frequency);
        $this->assertEquals(1, $subscription->donorId);
        $this->assertEquals(null, $subscription->transactionId);
        $this->assertEquals(0, $subscription->feeAmount);
        $this->assertEquals(SubscriptionStatus::PENDING(), $subscription->status);
        $this->assertEquals(null, $subscription->notes);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testDefaultSubscriptionPropertiesShouldHaveCorrectTypes()
    {
        $subscription = $this->createSubscriptionInstance();

        $this->assertInstanceOf(SubscriptionStatus::class, $subscription->status);
        $this->assertInstanceOf(SubscriptionPeriod::class, $subscription->period);
        $this->assertInstanceOf(DateTime::class, $subscription->createdAt);
    }

    /**
     * @unreleased
     *
     * @return Subscription
     */
    private function createSubscriptionInstance()
    {
        return new Subscription(50, SubscriptionPeriod::MONTH(), 1, 1);
    }
}
