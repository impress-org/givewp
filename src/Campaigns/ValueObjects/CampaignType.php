<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static CampaignStatus CORE()
 * @method static CampaignStatus P2P()
 * @method bool isCore()
 * @method bool isP2p()
 */
class CampaignType extends Enum
{
    const CORE = 'core';
    const P2P = 'p2p';
}
