<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @unreleased 
 *
 * @method static PENDING()
 * @method static COMPLETE()
 * @method static REFUNDED()
 * @method static FAILED()
 * @method static CANCELED()
 * @method static ABANDONED()
 * @method static PREAPPROVAL()
 * @method static PROCESSING()
 * @method static REVOKED()
 */
class DonationStatus extends Enum {
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const COMPLETE = 'publish';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';
    const CANCELED = 'cancelled';
    const ABANDONED = 'abandoned';
    const PREAPPROVAL = 'preapproval';
    const REVOKED = 'revoked';
}
