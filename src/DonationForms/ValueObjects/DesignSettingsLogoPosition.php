<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DesignSettingsLogoPosition LEFT()
 * @method static DesignSettingsLogoPosition CENTER()
 * @method static DesignSettingsLogoPosition RIGHT()
 * @method bool isLeft()
 * @method bool isCenter()
 * @method bool isRight()
 */
class DesignSettingsLogoPosition extends Enum
{
    const LEFT = 'left';
    const CENTER = 'center';
    const RIGHT = 'right';
}
