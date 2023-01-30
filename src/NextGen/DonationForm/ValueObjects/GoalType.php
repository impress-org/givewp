<?php

namespace Give\NextGen\DonationForm\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 0.1.0
 *
 * @method static GoalType AMOUNT()
 * @method static GoalType DONATIONS()
 * @method static GoalType DONORS()
 * @method bool isAmount()
 * @method bool isDonations()
 * @method bool isDonors()
 */
class GoalType extends Enum
{
    const AMOUNT = 'amount';
    const DONATIONS = 'donations';
    const DONORS = 'donors';
}
