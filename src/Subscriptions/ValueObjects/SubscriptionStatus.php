<?php

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.17.0 Added a new "paused" status
 * @since 2.19.6
 *
 * @method static SubscriptionStatus PENDING()
 * @method static SubscriptionStatus ACTIVE()
 * @method static SubscriptionStatus EXPIRED()
 * @method static SubscriptionStatus COMPLETED()
 * @method static SubscriptionStatus REFUNDED() @deprecated Do not use this. Use SubscriptionStatus::CANCELLED() or SubscriptionStatus::SUSPENDED() instead.
 * @method static SubscriptionStatus ABANDONED() @deprecated Do not use this. Use SubscriptionStatus::CANCELLED() instead.
 * @method static SubscriptionStatus FAILING()
 * @method static SubscriptionStatus CANCELLED()
 * @method static SubscriptionStatus SUSPENDED()
 * @method static SubscriptionStatus PAUSED()
 * @method bool isPending()
 * @method bool isActive()
 * @method bool isExpired()
 * @method bool isCompleted()
 * @method bool isRefunded() @deprecated Do not use this. Instead, use the CANCELLED or SUSPENDED statuses.
 * @method bool isAbandoned() @deprecated Do not use this. Instead, use the CANCELLED status.
 * @method bool isFailing()
 * @method bool isCancelled()
 * @method bool isSuspended()
 * @method bool isPaused()
 */
class SubscriptionStatus extends Enum {
    const PENDING = 'pending';
    const ACTIVE = 'active';
    const EXPIRED = 'expired';
    const COMPLETED = 'completed';
    const FAILING = 'failing';
    const CANCELLED = 'cancelled';
    const SUSPENDED = 'suspended';
    const PAUSED = 'paused';

    /**
     * @deprecated Do not use this. Use SubscriptionStatus::CANCELLED or SubscriptionStatus::SUSPENDED instead.
     */
    const REFUNDED = 'refunded';

    /**
     * @deprecated Do not use this. Use SubscriptionStatus::CANCELLED instead.
     */
    const ABANDONED = 'abandoned';

    /**
     * @since 3.17.0 Added a new "paused" status
     * @since 2.24.0
     *
     * @return array
     */
    public static function labels(): array
    {
        return [
            self::PENDING => __( 'Pending', 'give' ),
            self::ACTIVE => __( 'Active', 'give' ),
            self::EXPIRED => __( 'Expired', 'give' ),
            self::COMPLETED => __( 'Completed', 'give' ),
            self::REFUNDED => __( 'Refunded', 'give' ),
            self::FAILING => __( 'Failed', 'give' ),
            self::CANCELLED => __( 'Cancelled', 'give' ),
            self::ABANDONED => __( 'Abandoned', 'give' ),
            self::SUSPENDED => __( 'Suspended', 'give' ),
            self::PAUSED => __('Paused', 'give'),
        ];
    }

    /**
     * @since 2.24.0
     *
     * @return string
     */
    public function label(): string
    {
        return self::labels()[ $this->getValue() ];
    }
}
