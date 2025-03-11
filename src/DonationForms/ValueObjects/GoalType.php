<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.0.0
 *
 * @method static GoalType AMOUNT()
 * @method static GoalType DONATIONS()
 * @method static GoalType DONORS()
 * @method static GoalType SUBSCRIPTIONS()
 * @method static GoalType AMOUNT_FROM_SUBSCRIPTIONS()
 * @method static GoalType DONORS_FROM_SUBSCRIPTIONS()
 * @method bool isAmount()
 * @method bool isDonations()
 * @method bool isDonors()
 * @method bool isSubscriptions()
 * @method bool isAmountFromSubscriptions()
 * @method bool isDonorsFromSubscriptions()
 */
class GoalType extends Enum
{
    const AMOUNT = 'amount';
    const DONATIONS = 'donations';
    const DONORS = 'donors';
    const SUBSCRIPTIONS = 'subscriptions';
    const AMOUNT_FROM_SUBSCRIPTIONS = 'amountFromSubscriptions';
    const DONORS_FROM_SUBSCRIPTIONS = 'donorsFromSubscriptions';
}
