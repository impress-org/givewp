<?php

namespace Give\NextGen\DonationForm\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static RegistrationOptions AMOUNT_RAISED()
 * @method static RegistrationOptions PERCENTAGE_RAISED()
 * @method static RegistrationOptions NUMBER_DONATIONS()
 * @method static RegistrationOptions NUMBER_DONORS()
 * @method bool isNumberRaised()
 * @method bool isPercentageRaised()
 * @method bool isNumberDonations()
 * @method bool NumberDonors()
 */
class GoalFormatOptions extends Enum
{
    const AMOUNT_RAISED = 'amount-raised';
    const PERCENTAGE_RAISED = 'percentage-raised';
    const NUMBER_DONATIONS = 'number-donations';
    const NUMBER_DONORS = 'number-donors';
}
