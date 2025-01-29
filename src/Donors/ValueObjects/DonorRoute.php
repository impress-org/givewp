<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonorRoute NAMESPACE()
 * @method static DonorRoute DONOR()
 * @method static DonorRoute DONORS()
 * @method bool isNamespace()
 * @method bool isDonor()
 * @method bool isDonors()
 */
class DonorRoute extends Enum
{
    const NAMESPACE = 'give-api/v2';
    const DONOR = 'donors/(?P<id>[0-9]+)';
    const DONORS = 'donors';
}
