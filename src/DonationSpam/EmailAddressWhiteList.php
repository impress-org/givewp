<?php

namespace Give\DonationSpam;

/**
 * @since 3.15.0
 */
class EmailAddressWhiteList
{
    /**
     * @var string[]
     */
    protected array $whitelistEmails;

    /**
     * @since 4.16.0 Normalize whitelisted emails so comparisons are case- and whitespace-insensitive.
     * @since 3.15.1 Add array type to enforce type.
     * @since 3.15.0
     */
    public function __construct(array $whitelistEmails = [])
    {
        $this->whitelistEmails = array_map([$this, 'normalize'], $whitelistEmails);
    }

    /**
     * @since 4.16.0 Compare against the normalized whitelist so casing/whitespace don't cause a miss.
     * @since 3.15.0
     */
    public function validate(string $email): bool
    {
        return in_array($this->normalize($email), $this->whitelistEmails, true);
    }

    /**
     * Whitelist entries originate from the give_akismet_whitelist_emails filter, so they aren't
     * guaranteed to be strings — cast defensively before normalizing.
     *
     * @since 4.16.0
     *
     * @param mixed $email
     */
    private function normalize($email): string
    {
        return strtolower(trim((string)$email));
    }
}
