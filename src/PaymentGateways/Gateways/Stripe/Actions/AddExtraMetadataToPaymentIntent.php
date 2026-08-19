<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;

/**
 * Adds extra metadata to the transaction sent to Stripe.
 *
 * @since 4.16.7
 */
class AddExtraMetadataToPaymentIntent
{
    /**
     * Stripe rejects metadata values longer than 500 characters.
     */
    const MAX_LENGTH = 500;

    /**
     * @since 4.16.7
     */
    public function __invoke(array $metadata, int $donationId): array
    {
        $donation = Donation::find($donationId);
        $campaign = $donation ? $donation->campaign : null;

        if (!$campaign || empty($campaign->title)) {
            return $metadata;
        }

        $metadata['Campaign Name'] = mb_strlen($campaign->title) > self::MAX_LENGTH
            ? mb_substr($campaign->title, 0, self::MAX_LENGTH - 3) . '...'
            : $campaign->title;

        return $metadata;
    }
}
