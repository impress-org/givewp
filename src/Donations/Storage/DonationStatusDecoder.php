<?php

namespace Give\Donations\Storage;

use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;

/**
 * Turns the legacy post record into the separate status and type columns of give_donations.
 *
 * The posts storage overloads post_status: a completed renewal is stored as give_subscription, and
 * the type is only implied by subscription meta and post_parent. This is the single place that
 * decoding happens, shared by the sync listeners and the backfill so the two can never disagree.
 *
 * @since TBD
 */
class DonationStatusDecoder
{
    /**
     * @since TBD
     *
     * @param array $meta meta_key => meta_value for the donation
     *
     * @return array{0: string, 1: string} [status, type]
     */
    public static function decode(string $postStatus, int $postParent, array $meta): array
    {
        if ($postStatus === DonationStatus::RENEWAL) {
            return [DonationStatus::COMPLETE, DonationType::RENEWAL];
        }

        // The initial-donation flag wins on its own, as it does when the model is hydrated from
        // posts, so a flagged donation that lost its subscription_id meta stays a subscription.
        if (!empty($meta[DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION])) {
            return [$postStatus, DonationType::SUBSCRIPTION];
        }

        if (empty($meta[DonationMetaKeys::SUBSCRIPTION_ID])) {
            return [$postStatus, DonationType::SINGLE];
        }

        return [$postStatus, $postParent === 0 ? DonationType::SUBSCRIPTION : DonationType::RENEWAL];
    }
}
