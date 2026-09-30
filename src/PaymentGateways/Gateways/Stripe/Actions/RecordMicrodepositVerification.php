<?php

namespace Give\PaymentGateways\Gateways\Stripe\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Email\Notifications\DonationMicrodepositVerificationEmail;

/**
 * Records that a Stripe ACH donation requires microdeposit verification, notes it on the donation,
 * and emails the donor the hosted verification link.
 *
 * Called from the gateway status handler, the legacy Stripe helper, and the
 * `payment_intent.requires_action` webhook listener, so the work is intentionally idempotent: Stripe
 * can deliver the same state more than once, and each caller can reach it independently.
 *
 * @since TBD
 */
class RecordMicrodepositVerification
{
    /**
     * The donation meta key holding the hosted Stripe verification URL.
     */
    const META_KEY = '_give_stripe_microdeposit_verification_url';

    /**
     * @since TBD
     *
     * @return bool Whether the verification was recorded for the first time.
     */
    public function __invoke(Donation $donation, string $hostedVerificationUrl): bool
    {
        if (empty($hostedVerificationUrl)) {
            return false;
        }

        if (give_get_meta($donation->id, self::META_KEY, true) === $hostedVerificationUrl) {
            return false;
        }

        give_update_meta($donation->id, self::META_KEY, $hostedVerificationUrl);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
                __(
                    'Stripe ACH microdeposit verification required. The donor was emailed a hosted verification link: %s',
                    'give'
                ),
                $hostedVerificationUrl
            ),
        ]);

        DonationMicrodepositVerificationEmail::get_instance()->sendEmailNotificationToDonor($donation);

        return true;
    }
}
