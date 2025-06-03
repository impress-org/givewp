<?php

namespace Give\API\REST\V3\Routes\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonorRoute NAMESPACE()
 * @method static DonorRoute BASE()
 * @method bool isNamespace()
 * @method bool isBase()
 */
class DonorRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const BASE = 'donors';
}
