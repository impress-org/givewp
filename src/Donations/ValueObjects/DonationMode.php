<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @unreleased
 *
 * @method static TEST()
 * @method static LIVE()
 */
class DonationMode extends Enum {
    const TEST = 'test';
    const LIVE = 'live';
}
