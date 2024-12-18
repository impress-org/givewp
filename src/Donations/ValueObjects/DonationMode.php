<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.19.6
 *
 * @method static DonationMode TEST()
 * @method static DonationMode LIVE()
 * @method bool isTest()
 * @method bool isLive()
 */
class DonationMode extends Enum {
    const TEST = 'test';
    const LIVE = 'live';
}
