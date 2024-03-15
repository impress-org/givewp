<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.4.0
 *
 * @method static DesignSettingsSectionStyle DEFAULT()
 * @method static DesignSettingsSectionStyle BORDER()
 * @method static DesignSettingsSectionStyle SOLID()
 * @method static DesignSettingsSectionStyle CARD()
 * @method bool isDefault()
 * @method bool isBorder()
 * @method bool isSolid()
 * @method bool isCard()
 */
class DesignSettingsSectionStyle extends Enum
{
    const DEFAULT = 'default';
    const BORDER = 'border';
    const SOLID = 'solid';
    const CARD = 'card';
}
