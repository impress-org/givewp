<?php

namespace Give\DonationForms\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\EnumInteractsWithQueryBuilder;

/**
 * @since 3.0.0
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
