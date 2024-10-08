<?php

namespace Give\Campaigns\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static CampaignGoalType AMOUNT()
 * @method static CampaignGoalType DONATIONS()
 * @method static CampaignGoalType DONORS()
 * @method static CampaignGoalType AMOUNT_FROM_SUBSCRIPTIONS()
 * @method static CampaignGoalType SUBSCRIPTIONS()
 * @method static CampaignGoalType DONORS_FROM_SUBSCRIPTIONS()
 * @method bool isAmount()
 * @method bool isDonations()
 * @method bool isDonors()
 * @method bool isAmountFromSubscriptions()
 * @method bool isSubscriptions()
 * @method bool isDonorsFromSubscriptions()
 */
class CampaignGoalType extends Enum
{
    const AMOUNT = 'amount';
    const DONATIONS = 'donations';
    const DONORS = 'donors';
    const AMOUNT_FROM_SUBSCRIPTIONS = 'amountFromSubscriptions';
    const SUBSCRIPTIONS = 'subscriptions';
    const DONORS_FROM_SUBSCRIPTIONS = 'donorsFromSubscriptions';
}
