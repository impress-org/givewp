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
 * can deliver the same state more than once, and each caller can reach it independently. The emailed
 * URL is tracked apart from the recorded URL so a failed send is retried on the next call.
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
     * The donation meta key holding the verification URL the donor was successfully emailed.
     */
    const EMAILED_META_KEY = '_give_stripe_microdeposit_verification_emailed_url';

    /**
     * @since TBD
     *
     * @return bool Whether the donor was emailed the verification link during this call.
     */
    public function __invoke(Donation $donation, string $hostedVerificationUrl): bool
    {
        if (!$hostedVerificationUrl) {
            return false;
        }

        if (give_get_meta($donation->id, self::META_KEY, true) !== $hostedVerificationUrl) {
            give_update_meta($donation->id, self::META_KEY, $hostedVerificationUrl);

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => sprintf(
                    __('Stripe ACH microdeposit verification required. Hosted verification link: %s', 'give'),
                    $hostedVerificationUrl
                ),
            ]);
        }

        if (give_get_meta($donation->id, self::EMAILED_META_KEY, true) === $hostedVerificationUrl) {
            return false;
        }

        if (!DonationMicrodepositVerificationEmail::get_instance()->sendEmailNotificationToDonor($donation)) {
            return false;
        }

        give_update_meta($donation->id, self::EMAILED_META_KEY, $hostedVerificationUrl);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => __('The donor was emailed the Stripe ACH microdeposit verification link.', 'give'),
        ]);

        return true;
    }
}
