<?php

declare(strict_types=1);

namespace Give\Subscriptions\Actions;

use DateTime;
use Exception;
use Give\DonationForms\DonationQuery;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\QueryBuilder\QueryBuilder;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * Calculates the projected annual revenue for a subscription based on completed donations
 * from the current year and estimated remaining donations until the end of the year.
 *
 * @since 4.8.0
 */
class CalculateProjectedAnnualRevenue
{
    /**
     * Calculate the projected annual revenue for a subscription.
     *
     * @param Subscription $subscription
     * @return Money
     */
    public function __invoke(Subscription $subscription): Money
    {
        try {
            $currentYear = (int) date('Y');
            $yearStart = new DateTime("January 1st, {$currentYear}");
            $yearEnd = new DateTime("December 31st, {$currentYear} 23:59:59");

            // Get completed donations from January 1st of current year to now
            $completedAmount = (new DonationQuery())
            ->whereIn("post_status", [
                DonationStatus::COMPLETE,
                DonationStatus::RENEWAL
            ])
            ->whereIn("ID", function (QueryBuilder $builder) use ($subscription) {
                $builder
                    ->select("donation_id")
                    ->from("give_donationmeta")
                    ->where("meta_key", DonationMetaKeys::SUBSCRIPTION_ID)
                    ->where("meta_value", $subscription->id);
            })
            ->between(
                $yearStart->format("Y-m-d H:i:s"),
                $yearEnd->format("Y-m-d H:i:s")
            )
            ->sumIntendedAmount();

            $completedAmount = Money::fromDecimal($completedAmount, $subscription->amount->getCurrency()->getCode());

            // Calculate projected amount based on remaining donations this year
            $remainingInstallments = $this->calculateRemainingInstallmentsUntilEndOfYear($subscription);

            $projectedAmount = $subscription->intendedAmount()->multiply($remainingInstallments);

            return $completedAmount->add($projectedAmount);
        } catch (Exception $e) {
            // Return zero Money object on error
            return new Money(0, $subscription->amount->getCurrency()->getCode());
        }
    }

    /**
     * Calculate the remaining installments until the end of the current year.
     *
     * @since 4.8.0
     */
    private function calculateRemainingInstallmentsUntilEndOfYear(Subscription $subscription): int
    {
        $nextRenewal = clone $subscription->renewsAt;
        $currentYear = (new DateTime())->format('Y');
        $yearEnd = new DateTime('last day of December ' . $currentYear . ' 23:59:59');
        $modifier = $subscription->period->isQuarter()
            ? ($subscription->frequency * 3) . ' ' . SubscriptionPeriod::MONTH
            : $subscription->frequency . ' ' . $subscription->period->getValue();

        $installments = 0;

        while ($nextRenewal <= $yearEnd) {
            if ($nextRenewal->format('Y') === $currentYear) {
                $installments++;
            }

            $nextRenewal->modify($modifier);
        }

        if ($subscription->installments > 0) {
            $paidInstallments = $subscription->donations()->count();
            $remainingInstallments = max(0, $subscription->installments - $paidInstallments);
            $installments = min($installments, $remainingInstallments);
        }

        return $installments;
    }
}
