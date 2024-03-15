<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 3.4.0
 * @method static DesignSettingsTextFieldStyle DEFAULT()
 * @method static DesignSettingsTextFieldStyle BOX()
 * @method static DesignSettingsTextFieldStyle LINE()
 * @method bool isDefault()
 * @method bool isBox()
 * @method bool isLine()
 */
class DesignSettingsTextFieldStyle extends Enum
{
    const DEFAULT = 'default';
    const BOX = 'box';
    const LINE = 'line';

}
