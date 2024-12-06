<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.12.0
 *
 * @method static GoalProgressType ALL_TIME()
 * @method static GoalProgressType CUSTOM()
 * @method bool isAllTime()
 * @method bool isCustom()
 */
class GoalProgressType extends Enum
{
    const ALL_TIME = 'all_time';
    const CUSTOM = 'custom';
}
