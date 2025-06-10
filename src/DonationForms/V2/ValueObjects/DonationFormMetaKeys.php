<?php

namespace Give\DonationForms\V2\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * Donation Form Meta keys
 *
 * @since 2.21.0
 *
 * @method static DonationFormMetaKeys FORM_EARNINGS()
 * @method static DonationFormMetaKeys FORM_SALES()
 * @method static DonationFormMetaKeys DONATION_LEVELS()
 * @method static DonationFormMetaKeys SET_PRICE()
 * @method static DonationFormMetaKeys PRICE_OPTION()
 * @method static DonationFormMetaKeys GOAL_OPTION()
 * @since 4.3.0
 * @method static DonationFormMetaKeys GOAL_FORMAT()
 * @method static DonationFormMetaKeys RECURRING_GOAL_FORMAT()
 * @method static DonationFormMetaKeys GOAL_AMOUNT()
 */
class DonationFormMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const FORM_EARNINGS = '_give_form_earnings';
    const FORM_SALES = '_give_form_sales';
    const DONATION_LEVELS = '_give_donation_levels';
    const SET_PRICE = '_give_set_price';
    const PRICE_OPTION = '_give_price_option';
    const GOAL_OPTION = '_give_goal_option';
    const GOAL_FORMAT = '_give_goal_format';
    const RECURRING_GOAL_FORMAT = '_give_recurring_goal_format';
    const GOAL_AMOUNT = '_give_set_goal';
}
