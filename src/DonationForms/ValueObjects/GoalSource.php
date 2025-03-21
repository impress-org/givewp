<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
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
