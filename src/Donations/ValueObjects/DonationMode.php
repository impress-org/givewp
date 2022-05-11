<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @since 2.19.6
 *
 * @method static DonationMode TEST()
 * @method static DonationMode LIVE()
 */
class DonationMode extends Enum {
    const TEST = 'test';
    const LIVE = 'live';
}
