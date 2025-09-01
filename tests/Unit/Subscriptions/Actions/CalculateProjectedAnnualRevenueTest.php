<?php

namespace Give\Tests\Unit\Subscriptions\Actions;

use DateTime;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Actions\CalculateProjectedAnnualRevenue;
use Give\Subscriptions\Actions\GenerateNextRenewalForSubscription;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionMode;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class CalculateProjectedAnnualRevenueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @dataProvider subscriptionStartDateVariationsProvider
     */
    public function testSubscriptionStartDateVariations(string $startDate, SubscriptionPeriod $period, int $frequency, string $currentDate, int $paidInstallments, int $futureInstallments): void
    {
        $installmentDate = new DateTime($startDate);
        $currentDate = new DateTime($currentDate);

        $subscription = Subscription::factory()->createWithDonation([
            'amount' => new Money(10000, 'USD'), // $100.00
            'period' => $period,
            'frequency' => $frequency,
            'status' => SubscriptionStatus::ACTIVE(),
            'installments' => 0, // unlimited
            'mode' => SubscriptionMode::TEST(),
            'createdAt' => $installmentDate,
            'renewsAt' => give(GenerateNextRenewalForSubscription::class)($period, $frequency, $installmentDate),
        ]);

        $paidInstallmentsCount = 1;

        while ($subscription->renewsAt <= $currentDate) {
            $subscription->createRenewal([
                'createdAt' => $subscription->renewsAt,
            ]);

            $paidInstallmentsCount++;
        }

        $action = new CalculateProjectedAnnualRevenue();
        $projectedRevenue = $action($subscription);

        $this->assertEquals($paidInstallments, $paidInstallmentsCount);
        $this->assertEquals($subscription->amount->multiply($paidInstallments + $futureInstallments)->formatToMinorAmount(), $projectedRevenue->formatToMinorAmount());
    }

    /**
     * @unreleased
     */
    public function subscriptionStartDateVariationsProvider(): array
    {
        $currentYear = date('Y');

        /**
         * Array[
         *  'description' => [
         *      'startDate' => '2025-01-01',
         *      'period' => SubscriptionPeriod::MONTH(),
         *      'frequency' => 1,
         *      'currentDate' => '2025-08-31',
         *      'paidInstallments' => 8,
         *      'futureInstallments' => 4
         *  ]
         * ]
         */
        $data = [
            // --- Start at Jan 1 ---
            'jan 1 monthly every 1 month'   => ["{$currentYear}-01-01", SubscriptionPeriod::MONTH(), 1, "{$currentYear}-08-31", 8, 4],
            'jan 1 weekly every 1 week'    => ["{$currentYear}-01-01", SubscriptionPeriod::WEEK(), 1, "{$currentYear}-08-31", 35, 18],
            'jan 1 yearly every 1 year'    => ["{$currentYear}-01-01", SubscriptionPeriod::YEAR(), 1, "{$currentYear}-08-31", 1, 0],
            'jan 1 monthly every 2 months'   => ["{$currentYear}-01-01", SubscriptionPeriod::MONTH(), 2, "{$currentYear}-08-31", 4, 2],
            'jan 1 weekly every 2 weeks'    => ["{$currentYear}-01-01", SubscriptionPeriod::WEEK(), 2, "{$currentYear}-08-31", 18, 9],
            'jan 1 yearly every 2 years'    => ["{$currentYear}-01-01", SubscriptionPeriod::YEAR(), 2, "{$currentYear}-08-31", 1, 0],
            'jan 1 monthly every 3 months'   => ["{$currentYear}-01-01", SubscriptionPeriod::MONTH(), 3, "{$currentYear}-08-31", 3, 1],
            'jan 1 weekly every 3 weeks'    => ["{$currentYear}-01-01", SubscriptionPeriod::WEEK(), 3, "{$currentYear}-08-31", 12, 6],
            'jan 1 yearly every 3 years'    => ["{$currentYear}-01-01", SubscriptionPeriod::YEAR(), 3, "{$currentYear}-08-31", 1, 0],

            // --- Start mid-year ---
            'july 15 monthly every 1 month' => ["{$currentYear}-07-15", SubscriptionPeriod::MONTH(), 1, "{$currentYear}-08-31", 2, 4],
            'july 15 weekly every 1 week'  => ["{$currentYear}-07-15", SubscriptionPeriod::WEEK(), 1, "{$currentYear}-08-31", 7, 18],
            'july 15 yearly every 1 year'  => ["{$currentYear}-07-15", SubscriptionPeriod::YEAR(), 1, "{$currentYear}-08-31", 1, 0],
            'july 15 monthly every 2 months' => ["{$currentYear}-07-15", SubscriptionPeriod::MONTH(), 2, "{$currentYear}-08-31", 1, 2],
            'july 15 weekly every 2 weeks'  => ["{$currentYear}-07-15", SubscriptionPeriod::WEEK(), 2, "{$currentYear}-08-31", 4, 9],
            'july 15 yearly every 2 years'  => ["{$currentYear}-07-15", SubscriptionPeriod::YEAR(), 2, "{$currentYear}-08-31", 1, 0],
            'july 15 monthly every 3 months' => ["{$currentYear}-07-15", SubscriptionPeriod::MONTH(), 3, "{$currentYear}-08-31", 1, 1],
            'july 15 weekly every 3 weeks'  => ["{$currentYear}-07-15", SubscriptionPeriod::WEEK(), 3, "{$currentYear}-08-31", 3, 6],
            'july 15 yearly every 3 years'  => ["{$currentYear}-07-15", SubscriptionPeriod::YEAR(), 3, "{$currentYear}-08-31", 1, 0],

            // --- Start late-year ---
            'dec 20 monthly every 1 month'  => ["{$currentYear}-12-20", SubscriptionPeriod::MONTH(), 1, "{$currentYear}-12-31", 1, 0],
            'dec 20 weekly every 1 week'   => ["{$currentYear}-12-20", SubscriptionPeriod::WEEK(), 1, "{$currentYear}-12-31", 2, 0],
            'dec 20 yearly every 1 year'   => ["{$currentYear}-12-20", SubscriptionPeriod::YEAR(), 1, "{$currentYear}-12-31", 1, 0],
            'dec 20 monthly every 2 months'  => ["{$currentYear}-12-20", SubscriptionPeriod::MONTH(), 2, "{$currentYear}-12-31", 1, 0],
            'dec 20 weekly every 2 weeks'   => ["{$currentYear}-12-20", SubscriptionPeriod::WEEK(), 2, "{$currentYear}-12-31", 1, 0],
            'dec 20 yearly every 2 years'   => ["{$currentYear}-12-20", SubscriptionPeriod::YEAR(), 2, "{$currentYear}-12-31", 1, 0],
            'dec 20 monthly every 3 months'  => ["{$currentYear}-12-20", SubscriptionPeriod::MONTH(), 3, "{$currentYear}-12-31", 1, 0],
            'dec 20 weekly every 3 weeks'   => ["{$currentYear}-12-20", SubscriptionPeriod::WEEK(), 3, "{$currentYear}-12-31", 1, 0],
            'dec 20 yearly every 3 years'   => ["{$currentYear}-12-20", SubscriptionPeriod::YEAR(), 3, "{$currentYear}-12-31", 1, 0],
        ];

        return $data;
    }
}
