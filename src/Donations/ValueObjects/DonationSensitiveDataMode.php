<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @unreleased
 *
 * @method static DonationAnonymousMode EXCLUDE()
 * @method static DonationAnonymousMode INCLUDED()
 * @method bool isExcluded()
 * @method bool isIncluded()
 */
class DonationSensitiveDataMode extends Enum
{
    const EXCLUDED = 'exclude';
    const INCLUDED = 'include';
    const REDACTED = 'redact';
}
