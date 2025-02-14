<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
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
    const NAMESPACE = 'give-api/v2';
    const DONATION = 'donations/(?P<id>[0-9]+)';
    const DONATIONS = 'donations';
}
