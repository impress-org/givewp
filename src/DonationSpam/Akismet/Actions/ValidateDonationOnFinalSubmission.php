<?php

namespace Give\DonationSpam\Akismet\Actions;

use Give\DonationSpam\Exceptions\SpamDonationException;

/**
 * Handles the givewp_donation_form_fields_validated action by running the Akismet spam check, but
 * only for the final submission so Akismet isn't called on every step (which looks like a spam flood).
 *
 * @since 4.16.0
 */
class ValidateDonationOnFinalSubmission
{
    /**
     * @var ValidateDonation
     */
    protected $validateDonation;

    /**
     * @since 4.16.0
     */
    public function __construct(ValidateDonation $validateDonation)
    {
        $this->validateDonation = $validateDonation;
    }

    /**
     * The Akismet enabled/configured check lives here rather than on boot so it only runs when a
     * donation is actually validated, not on every request.
     *
     * @since 4.16.0
     *
     * @throws SpamDonationException
     */
    public function __invoke(array $data, bool $isFinalSubmission = true): void
    {
        if (!$isFinalSubmission || !$this->isAkismetEnabledAndConfigured()) {
            return;
        }

        ($this->validateDonation)(
            $data['email'] ?? '',
            $data['comment'] ?? '',
            $data['firstName'] ?? '',
            $data['lastName'] ?? ''
        );
    }

    /**
     * @since 4.16.0
     */
    protected function isAkismetEnabledAndConfigured(): bool
    {
        return
            give_check_akismet_key()
            && give_is_setting_enabled(
                give_get_option('akismet_spam_protection', 'enabled')
            );
    }
}
