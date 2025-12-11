<?php

namespace Give\API\REST\V3\Routes\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonationRoute NAMESPACE()
 * @method static DonationRoute BASE()
 * @method bool isNamespace()
 * @method bool isBase()
 */
class DonationRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const BASE = 'donations';
}
