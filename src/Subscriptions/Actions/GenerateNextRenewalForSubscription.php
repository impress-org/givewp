<?php

declare(strict_types=1);

namespace Give\Subscriptions\Actions;

use DateInterval;
use DateTime;
use DateTimeInterface;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;

/**
 * Generates the next renewal date for a subscription. A base date may be provided, otherwise it will default to the
 * current date.
 *
 * @since 2.23.0
 */
class GenerateNextRenewalForSubscription
{
    public function __invoke(
        SubscriptionPeriod $period,
        int $frequency,
        DateTimeInterface $baseDate = null
    ): DateTimeInterface {
        if ( $frequency < 1 ) {
            throw new InvalidArgumentException('Frequency must be greater than 0');
        }

        // Calculate the quarter as times 3 months
        if ($period->equals(SubscriptionPeriod::QUARTER())) {
            $frequency *= 3;
            $period = SubscriptionPeriod::MONTH();
        }

        $baseDate = $baseDate ?? new DateTime('now', wp_timezone());

        return Temporal::withoutMicroseconds($baseDate->modify("$frequency {$period->getValue()}"));
    }
}
