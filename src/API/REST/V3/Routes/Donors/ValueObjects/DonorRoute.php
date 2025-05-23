<?php

namespace Give\API\REST\V3\Routes\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonorRoute NAMESPACE()
 * @method static DonorRoute BASE()
 * @method static DonorRoute DONOR()
 * @method static DonorRoute DONORS()
 * @method bool isNamespace()
 * @method bool isBase()
 * @method bool isDonor()
 * @method bool isDonors()
 */
class DonorRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const BASE = 'donors';
    const DONOR = 'donors/(?P<id>[0-9]+)';
    const DONORS = 'donors';
}
