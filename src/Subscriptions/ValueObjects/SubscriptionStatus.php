<?php

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.19.6
 *
 * @method static SubscriptionStatus PENDING()
 * @method static SubscriptionStatus ACTIVE()
 * @method static SubscriptionStatus EXPIRED()
 * @method static SubscriptionStatus COMPLETED()
 * @method static SubscriptionStatus REFUNDED()
 * @method static SubscriptionStatus ABANDONED()
 * @method static SubscriptionStatus FAILING()
 * @method static SubscriptionStatus CANCELLED()
 * @method static SubscriptionStatus SUSPENDED()
 * @method bool isPending()
 * @method bool isActive()
 * @method bool isExpired()
 * @method bool isCompleted()
 * @method bool isRefunded()
 * @method bool isAbandoned()
 * @method bool isFailing()
 * @method bool isCancelled()
 * @method bool isSuspended()
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

    /**
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
