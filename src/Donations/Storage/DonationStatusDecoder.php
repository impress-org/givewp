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

        if (empty($meta[DonationMetaKeys::SUBSCRIPTION_ID])) {
            return [$postStatus, DonationType::SINGLE];
        }

        $isInitialDonation = !empty($meta[DonationMetaKeys::SUBSCRIPTION_INITIAL_DONATION]) || $postParent === 0;

        return [$postStatus, $isInitialDonation ? DonationType::SUBSCRIPTION : DonationType::RENEWAL];
    }
}
