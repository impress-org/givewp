<?php

namespace Give\Donations\ValueObjects;

use Give\Framework\Support\ValueObjects\Enum;

/**
 * @since 2.19.6
 *
 * @method static DonationStatus PENDING()
 * @method static DonationStatus COMPLETE()
 * @method static DonationStatus REFUNDED()
 * @method static DonationStatus FAILED()
 * @method static DonationStatus CANCELED()
 * @method static DonationStatus ABANDONED()
 * @method static DonationStatus PREAPPROVAL()
 * @method static DonationStatus PROCESSING()
 * @method static DonationStatus REVOKED()
 * @method static DonationStatus RENEWAL()
 * @method bool isPending()
 * @method bool isComplete()
 * @method bool isRefunded()
 * @method bool isFailed()
 * @method bool isCanceled()
 * @method bool isAbandoned()
 * @method bool isPreapproval()
 * @method bool isProcessing()
 * @method bool isRevoked()
 * @method bool isRenewal()
 */
class DonationStatus extends Enum
{
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
