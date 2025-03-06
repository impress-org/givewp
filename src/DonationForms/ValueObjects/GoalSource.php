<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static GoalSource CAMPAIGN()
 * @method static GoalSource CUSTOM()
 * @method bool isCampaign()
 * @method bool isCustom()
 */
class GoalSource extends Enum
{
    const CAMPAIGN = 'campaign';
    const CUSTOM = 'custom';
}
