<?php

declare(strict_types=1);

namespace Give\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.8.0
 *
 * @method static SubscriptionNoteType ADMIN()
 * @method static SubscriptionNoteType DONOR()
 * @method bool isAdmin()
 * @method bool isDonor()
 */
class SubscriptionNoteType extends Enum
{
    const ADMIN = 'admin';
    const DONOR = 'donor';
}
