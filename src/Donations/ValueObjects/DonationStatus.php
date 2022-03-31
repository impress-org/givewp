<?php

namespace Give\Donations\ValueObjects;

use MyCLabs\Enum\Enum;

/**
 * @since 2.19.6
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
 * @method static RENEWAL()
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
    const RENEWAL = 'give_subscription';
}
