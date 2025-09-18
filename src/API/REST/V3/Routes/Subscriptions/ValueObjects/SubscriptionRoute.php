<?php

namespace Give\API\REST\V3\Routes\Subscriptions\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.8.0
 *
 * @method static SubscriptionRoute NAMESPACE()
 * @method static SubscriptionRoute BASE()
 * @method bool isNamespace()
 * @method bool isBase()
 */
class SubscriptionRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const BASE = 'subscriptions';
}
