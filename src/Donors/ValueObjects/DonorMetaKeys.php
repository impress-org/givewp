<?php

namespace Give\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;
use Give\Framework\Support\ValueObjects\InteractsWithQueryBuilder;

/**
 * @unreleased
 *
 * @method static FIRST_NAME()
 * @method static LAST_NAME()
 */
class DonorMetaKeys extends Enum
{
    use InteractsWithQueryBuilder;

    const FIRST_NAME = '_give_donor_first_name';
    const LAST_NAME = '_give_donor_last_name';
    const ADDITIONAL_EMAIL = 'additional_email';
    const PREFIX = '_give_donor_title_prefix';
}
