<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.4.0
 *
 * @method static DonationFormErrorTypes BACKGROUND()
 * @method static DonationFormErrorTypes COVER()
 * @method static DonationFormErrorTypes ABOVE()
 * @method static DonationFormErrorTypes CENTER()
 * @method bool isBackground()
 * @method bool isCover()
 * @method bool isAbove()
 * @method bool isCenter()
 */
class DesignSettingsImageStyle extends Enum
{
    const BACKGROUND = 'background';
    const COVER = 'cover';
    const ABOVE = 'above';
    const CENTER = 'center';
}
