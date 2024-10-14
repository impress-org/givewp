<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static CampaignType AMOUNT()
 * @method static CampaignType DONATIONS()
 * @method static CampaignType DONORS()
 * @method bool isAmount()
 * @method bool isDonations()
 * @method bool isDonors()
 */
class CampaignGoalType extends Enum
{
    const AMOUNT = 'amount';
    const DONATIONS = 'donations';
    const DONORS = 'donors';
}
