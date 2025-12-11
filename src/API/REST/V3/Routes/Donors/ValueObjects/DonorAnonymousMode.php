<?php

namespace Give\API\REST\V3\Routes\Donors\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 4.0.0
 *
 * @method static DonorAnonymousMode EXCLUDE()
 * @method static DonorAnonymousMode INCLUDED()
 * @method static DonorAnonymousMode REDACTED()
 * @method bool isExcluded()
 * @method bool isIncluded()
 * @method bool isRedacted()
 */
class DonorAnonymousMode extends Enum
{
    const EXCLUDED = 'exclude';
    const INCLUDED = 'include';
    const REDACTED = 'redact';
}
