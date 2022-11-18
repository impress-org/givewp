<?php

namespace Give\DonationForms\ValueObjects;

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
}
