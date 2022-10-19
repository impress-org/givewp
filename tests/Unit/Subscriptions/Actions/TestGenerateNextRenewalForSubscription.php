<?php

declare(strict_types=1);

namespace GiveTests\Unit\Subscriptions\Actions;

use DateInterval;
use DateTime;
use Give\Subscriptions\Actions\GenerateNextRenewalForSubscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use GiveTests\TestCase;

/**
 * @unreleased
 *
 * @coversDefaultClass GenerateNextRenewalForSubscription
 */
class TestGenerateNextRenewalForSubscription extends TestCase
{
    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
