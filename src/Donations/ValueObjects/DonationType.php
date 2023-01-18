<?php

declare(strict_types=1);

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.23.0
 *
 * @method static DonationType SINGLE()
 * @method static DonationType SUBSCRIPTION()
 * @method static DonationType RENEWAL()
 * @method bool isSingle()
 * @method bool isSubscription()
 * @method bool isRenewal()
 */
class DonationType extends Enum
{
    // A single donation with no recurrence
    const SINGLE = 'single';

    // The first donation for a new subscription
    const SUBSCRIPTION = 'subscription';

    // A subsequent donation for an existing subscription
    const RENEWAL = 'renewal';

    /**
     * Whether this donation is recurring or not
     * @since 2.24.0
     */
    public function isRecurring(): bool
    {
        return ! $this->isSingle();
    }
}
