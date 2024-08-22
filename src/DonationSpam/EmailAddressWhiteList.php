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
     * @unreleased Add array type to enforce type.
     * @since 3.15.0
     */
    public function __construct(array $whitelistEmails = [])
    {
        $this->whitelistEmails = $whitelistEmails;
    }

    /**
     * @since 3.15.0
     */
    public function validate($email): bool
    {
        return in_array($email, $this->whitelistEmails, true);
    }
}
