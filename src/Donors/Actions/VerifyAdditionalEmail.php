<?php

namespace Give\Donors\Actions;

use Give\Donors\Models\Donor;
use Give\Donors\Repositories\DonorPendingEmailRepository;

/**
 * Completes additional-email verification when a donor opens the link from their
 * verification email: givewp_verify_email={token}.
 *
 * The token is the only credential, as with WordPress account-activation links, so the
 * verification works whether or not the recipient is logged in.
 *
 * @since TBD
 */
class VerifyAdditionalEmail
{
    const STATUS_VERIFIED = 'verified';
    const STATUS_INVALID = 'invalid';
    const STATUS_CLAIMED = 'claimed';

    /**
     * Redirect any givewp_verify_email link to the donor history page with the outcome.
     *
     * @since TBD
     */
    public function __invoke()
    {
        if (empty($_GET['givewp_verify_email'])) {
            return;
        }

        $token = sanitize_text_field(wp_unslash($_GET['givewp_verify_email']));

        wp_safe_redirect(
            add_query_arg(
                'givewp_verify_email_status',
                $this->verify($token),
                give_get_history_page_uri()
            )
        );

        exit;
    }

    /**
     * Verify the token and append the pending email to its donor when the email is still
     * unclaimed. Returns the outcome for the redirect.
     *
     * @since TBD
     */
    public function verify(string $token): string
    {
        $pendingEmails = give(DonorPendingEmailRepository::class);
        $match = $pendingEmails->findByToken($token);

        if ($match === null) {
            return self::STATUS_INVALID;
        }

        $donorId = $match['donorId'];
        $entry = $match['entry'];

        // The email may have been claimed by another donor since the request was made.
        $claimant = Donor::whereEmail($entry['email']);

        if ($claimant !== null && (int)$claimant->id !== $donorId) {
            $pendingEmails->delete($donorId, $entry);

            return self::STATUS_CLAIMED;
        }

        $donor = Donor::find($donorId);

        if ($donor === null || $donor->hasEmail($entry['email'])) {
            $pendingEmails->delete($donorId, $entry);

            return self::STATUS_VERIFIED;
        }

        if (give()->donors->addVerifiedAdditionalEmail($donor, $entry['email'])) {
            $pendingEmails->delete($donorId, $entry);

            return self::STATUS_VERIFIED;
        }

        return self::STATUS_INVALID;
    }
}
