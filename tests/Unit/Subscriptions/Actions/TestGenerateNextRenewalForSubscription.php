<?php

declare(strict_types=1);

namespace Give\Tests\Unit\Subscriptions\Actions;

use DateInterval;
use DateTime;
use Give\Subscriptions\Actions\GenerateNextRenewalForSubscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Tests\TestCase;

/**
 * @since 2.19.6
 *
 * @coversDefaultClass GenerateNextRenewalForSubscription
 */
class TestGenerateNextRenewalForSubscription extends TestCase
{
    /**
     * @since 2.19.6
     */
    public function testShouldGenerateDateWithoutBaseDate()
    {
        $generateNextRenewalForSubscription = new GenerateNextRenewalForSubscription();

        $nextRenewalDate = $generateNextRenewalForSubscription(
            SubscriptionPeriod::MONTH(),
            2
        );

        $this->assertEquals(
            (new DateTime('now', wp_timezone()))->add(new DateInterval('P2M'))->format('Y-m-d'),
            $nextRenewalDate->format('Y-m-d')
        );
    }

    /**
     * @since 2.19.6
     */
    public function testShouldGenerateDateWithBaseDate()
    {
        $generateNextRenewalForSubscription = new GenerateNextRenewalForSubscription();

        $nextRenewalDate = $generateNextRenewalForSubscription(
            SubscriptionPeriod::MONTH(),
            1,
            new DateTime('2020-01-01', wp_timezone())
        );

        $this->assertEquals(
            (new DateTime('2020-01-01', wp_timezone()))->add(new DateInterval('P1M'))->format('Y-m-d'),
            $nextRenewalDate->format('Y-m-d')
        );
    }

    /**
     * @since 2.19.6
     */
    public function testShouldGenerateDateWithQuarterPeriod()
    {
        $generateNextRenewalForSubscription = new GenerateNextRenewalForSubscription();

        $nextRenewalDate = $generateNextRenewalForSubscription(
            SubscriptionPeriod::QUARTER(),
            1
        );

        $this->assertEquals(
            (new DateTime('now', wp_timezone()))->add(new DateInterval('P3M'))->format('Y-m-d'),
            $nextRenewalDate->format('Y-m-d')
        );
    }
}
