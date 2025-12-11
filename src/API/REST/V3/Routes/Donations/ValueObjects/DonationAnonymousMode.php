<?php

namespace Give\API\REST\V3\Routes\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonationAnonymousMode EXCLUDE()
 * @method static DonationAnonymousMode INCLUDED()
 * @method static DonationAnonymousMode REDACTED()
 * @method bool isExcluded()
 * @method bool isIncluded()
 * @method bool isRedacted()
 */
class DonationAnonymousMode extends Enum
{
    const EXCLUDED = 'exclude';
    const INCLUDED = 'include';
    const REDACTED = 'redact';
}
