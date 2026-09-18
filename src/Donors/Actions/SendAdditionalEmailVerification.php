<?php

namespace Give\Donors\Actions;

use Give\Donors\Repositories\DonorPendingEmailRepository;

/**
 * Sends the ownership-verification email for a new additional email address.
 *
 * @since TBD
 */
class SendAdditionalEmailVerification
{
    /**
     * Queue the email for verification and send the verification email. No-op when
     * the pending repository rejects the email (invalid, already owned by a donor,
     * already pending, or pending limit reached).
     *
     * @since TBD
     */
    public function __invoke(int $donorId, string $email): void
    {
        $token = give(DonorPendingEmailRepository::class)->add($donorId, $email);

        if ($token === null) {
            return;
        }

        $verificationUrl = add_query_arg(
            'givewp_verify_email',
            $token,
            give_get_history_page_uri()
        );

        /* translators: 1: email address, 2: site name, 3: verification URL */
        $message = sprintf(
            __(
                'Someone requested to add %1$s as an additional email address to their donor profile on %2$s. If this was you, confirm the request by visiting this link: %3$s. If you did not make this request, you can safely ignore this email.',
                'give'
            ),
            $email,
            get_bloginfo('name'),
            '<a href="' . esc_url($verificationUrl) . '">' . esc_html($verificationUrl) . '</a>'
        );

        give()->emails->send($email, __('Please verify your email address', 'give'), $message);
    }
}
