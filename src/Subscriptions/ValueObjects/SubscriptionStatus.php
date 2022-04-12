<?php

namespace Give\Subscriptions\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @since 2.19.6
 *
 * @method static PENDING()
 * @method static ACTIVE()
 * @method static EXPIRED()
 * @method static COMPLETED()
 * @method static REFUNDED()
 * @method static ABANDONED()
 * @method static FAILING()
 * @method static CANCELED()
 * @method static SUSPENDED()
 */
class SubscriptionStatus extends Enum {
    const PENDING = 'pending';
    const ACTIVE = 'active';
    const EXPIRED = 'expired';
    const COMPLETED = 'completed';
    const REFUNDED = 'refunded';
    const FAILING = 'failing';
    const CANCELLED = 'cancelled';
    const ABANDONED = 'abandoned';
    const SUSPENDED = 'suspended';
}
