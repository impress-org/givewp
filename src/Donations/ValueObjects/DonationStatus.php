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
 * @method static DonationStatus CANCELLED()
 * @method static DonationStatus ABANDONED()
 * @method static DonationStatus PREAPPROVAL()
 * @method static DonationStatus PROCESSING()
 * @method static DonationStatus REVOKED()
 * @method static DonationStatus RENEWAL() @deprecated
 * @method bool isPending()
 * @method bool isComplete()
 * @method bool isRefunded()
 * @method bool isFailed()
 * @method bool isCancelled()
 * @method bool isAbandoned()
 * @method bool isPreapproval()
 * @method bool isProcessing()
 * @method bool isRevoked()
 * @method bool isRenewal() @deprecated Do not use this. Instead, set the donation type to "renewal" and use COMPLETE status.
 */
class DonationStatus extends Enum
{
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const COMPLETE = 'publish';
    const REFUNDED = 'refunded';
    const FAILED = 'failed';
    const CANCELLED = 'cancelled';
    const ABANDONED = 'abandoned';
    const PREAPPROVAL = 'preapproval';
    const REVOKED = 'revoked';

    /**
     * @deprecated 2.23.0 Use DonationStatus::COMPLETE
     */
    const RENEWAL = 'give_subscription';
}
