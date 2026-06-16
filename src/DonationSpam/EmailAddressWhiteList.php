<?php

namespace Give\DonationSpam;

/**
 * @since 3.15.0
 */
class EmailAddressWhiteList
{
    /**
     * @var array
     */
    protected $whitelistEmails;

    /**
     * @since TBD Normalize whitelisted emails so comparisons are case- and whitespace-insensitive.
     * @since 3.15.1 Add array type to enforce type.
     * @since 3.15.0
     */
    public function __construct(array $whitelistEmails = [])
    {
        $this->whitelistEmails = array_map([$this, 'normalize'], $whitelistEmails);
    }

    /**
     * @since TBD Compare against the normalized whitelist so casing/whitespace don't cause a miss.
     * @since 3.15.0
     */
    public function validate($email): bool
    {
        return in_array($this->normalize($email), $this->whitelistEmails, true);
    }

    /**
     * @since TBD
     */
    private function normalize($email): string
    {
        return strtolower(trim((string)$email));
    }
}
