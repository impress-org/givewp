<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @since 2.19.6
 *
 * @method static TEST()
 * @method static LIVE()
 */
class DonationMode extends Enum {
    const TEST = 'test';
    const LIVE = 'live';
}
