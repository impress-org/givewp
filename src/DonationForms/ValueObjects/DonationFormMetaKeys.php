<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * Donation Form Meta keys
 *
 * @since 2.21.0
 *
 * @method static DonationFormMetaKeys FORM_EARNINGS()
 * @method static DonationFormMetaKeys DONATION_LEVELS()
 * @method static DonationFormMetaKeys SET_PRICE()
 * @method static DonationFormMetaKeys GOAL_OPTION()
 */
class DonationFormMetaKeys extends Enum
{
    const FORM_EARNINGS = '_give_form_earnings';
    const DONATION_LEVELS = '_give_donation_levels';
    const SET_PRICE = '_give_set_price';
    const GOAL_OPTION = '_give_goal_option';
}
