<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static CampaignType AMOUNT()
 * @method static CampaignType DONATION()
 * @method static CampaignType DONORS()
 * @method bool isAmount()
 * @method bool isDonation()
 * @method bool isDonors()
 */
class CampaignGoalType extends Enum
{
    const AMOUNT = 'amount';
    const DONATION = 'donation';
    const DONORS = 'donors';
}
