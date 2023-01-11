<?php

namespace Give\NextGen\DonationForm\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @unreleased
 *
 * @method static DonationFormMetaKeys SETTINGS()
 * @method static DonationFormMetaKeys FIELDS()
 * @method bool isSettings()
 * @method bool isFields()
 */
class DonationFormMetaKeys extends Enum
{
    use EnumInteractsWithQueryBuilder;

    const SETTINGS = 'formBuilderSettings';

    const FIELDS = 'formBuilderFields';
}
