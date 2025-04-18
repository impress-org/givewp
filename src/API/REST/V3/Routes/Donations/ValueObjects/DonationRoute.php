<?php

namespace Give\API\REST\V3\Routes\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonationRoute NAMESPACE()
 * @method static DonationRoute DONATION()
 * @method static DonationRoute DONATIONS()
 * @method bool isNamespace()
 * @method bool isDonation()
 * @method bool isDonations()
 */
class DonationRoute extends Enum
{
    const NAMESPACE = 'givewp/v3';
    const DONATION = 'donations/(?P<id>[0-9]+)';
    const DONATIONS = 'donations';
}
