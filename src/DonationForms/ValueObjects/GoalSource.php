<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.1.0
 *
 * @method static GoalSource CAMPAIGN()
 * @method static GoalSource FORM()
 * @method bool isCampaign()
 * @method bool isForm()
 */
class GoalSource extends Enum
{
    const CAMPAIGN = 'campaign';
    const FORM = 'form';
}
