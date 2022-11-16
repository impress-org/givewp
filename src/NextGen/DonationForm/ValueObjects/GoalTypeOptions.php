<?php

namespace Give\NextGen\DonationForm\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static GoalTypeOptions AMOUNT()
 * @method static GoalTypeOptions DONATIONS()
 * @method static GoalTypeOptions DONORS()
 * @method bool isAmount()
 * @method bool isDonations()
 * @method bool isDonors()
 */
class GoalTypeOptions extends Enum
{
    const AMOUNT = 'amount';
    const DONATIONS = 'donations';
    const DONORS = 'donors';
}
